<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Domain C — Order induk (platform-scoped, TANPA store_id), alamat pengiriman,
 * sub-order per toko, dan snapshot item pesanan.
 * Sumber: docs/schema.dbml & docs/draft_rincian_project_laravel.md §6
 */
return new class extends Migration
{
    public function up(): void
    {
        // 10. Orders (Order induk checkout gabungan lintas toko — TANPA store_id)
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 20)->default('pending_payment'); // pending_payment|paid|expired|cancelled|completed
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('shipping_total'); // jumlah ongkir seluruh sub-order
            $table->unsignedBigInteger('grand_total');
            $table->string('checkout_idempotency_key', 80)->unique();
            $table->timestampTz('cancelled_at', 6)->nullable();
            $table->timestamps(6);

            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        // 11. Shipping Addresses (Alamat pengiriman pesanan — 1 per order induk)
        Schema::create('shipping_addresses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->string('receiver_name', 120);
            $table->string('phone', 20);
            $table->text('address_line');
            $table->string('city', 80);
            $table->string('postal_code', 10);
            $table->text('notes')->nullable();
            $table->timestampTz('created_at', 6);
        });

        // 12. Sub Orders (Pesanan per toko / tenant-scoped)
        Schema::create('sub_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('stores')->restrictOnDelete();
            $table->string('status', 20)->default('pending_payment'); // pending_payment|paid|processing|shipped|completed|refund_requested|refunded|expired|cancelled
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('shipping_fee'); // total_weight_gram (kg) * rate_per_kg
            $table->integer('total_weight_gram'); // snapshot total berat
            $table->unsignedBigInteger('rate_per_kg'); // snapshot tarif per kg toko
            $table->decimal('commission_rate_percent', 5, 2); // snapshot komisi saat checkout
            $table->unsignedBigInteger('commission_amount'); // dari subtotal produk saja
            $table->unsignedBigInteger('total'); // subtotal + shipping_fee
            $table->timestampTz('shipped_at', 6)->nullable();
            $table->integer('estimate_days')->nullable();
            $table->timestampTz('completed_at', 6)->nullable();
            $table->timestamps(6);

            // Target composite FK refund_requests & order_items
            $table->unique(['store_id', 'id']);
            $table->index('order_id');
            $table->index(['store_id', 'status']);
        });

        // 13. Order Items (Snapshot rincian item produk yang dibeli per sub-order)
        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('sub_order_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_name', 150); // snapshot
            $table->unsignedBigInteger('price_amount'); // snapshot harga satuan terpakai
            $table->string('price_type', 10); // snapshot: normal|grosir
            $table->integer('min_qty')->nullable(); // snapshot min_qty tier grosir terpakai
            $table->integer('qty'); // kuantitas dalam satuan jual
            $table->string('sales_unit', 20); // snapshot satuan jual
            $table->integer('weight_gram'); // snapshot berat per satuan
            $table->unsignedBigInteger('line_total');
            $table->timestampTz('created_at', 6);

            $table->index(['store_id', 'sub_order_id']);

            // Composite FK: sub_order dan produk harus milik toko yang sama
            $table->foreign(['store_id', 'sub_order_id'])
                ->references(['store_id', 'id'])
                ->on('sub_orders')
                ->cascadeOnDelete();

            $table->foreign(['store_id', 'product_id'])
                ->references(['store_id', 'id'])
                ->on('products')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('sub_orders');
        Schema::dropIfExists('shipping_addresses');
        Schema::dropIfExists('orders');
    }
};
