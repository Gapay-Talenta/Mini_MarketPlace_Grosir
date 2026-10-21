<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Domain B — Katalog produk grosir, tier harga bertingkat, dan audit pergerakan stok.
 * Menegakkan composite FK (store_id, product_id) -> products(store_id, id).
 * Sumber: docs/schema.dbml & docs/draft_rincian_project_laravel.md §6
 */
return new class extends Migration
{
    public function up(): void
    {
        // 7. Products (katalog produk toko grosir)
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('category', 20); // bahan_pangan|minuman|alat_tulis|kebersihan|rumah_tangga|lainnya
            $table->string('sales_unit', 20); // pcs|kardus|karung|pack|rim|kg|liter
            $table->text('description')->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->integer('weight_gram'); // gram per satu satuan jual (dasar ongkir)
            $table->integer('stock')->default(0);
            $table->string('status', 20)->default('aktif'); // aktif|arsip|habis
            $table->timestamps(6);

            // Target composite FK anak (product_prices, stock_movements, order_items)
            $table->unique(['store_id', 'id']);
            $table->index(['store_id', 'status']);
            $table->index(['category', 'status']);
        });

        // 8. Product Prices (1 harga normal + maks 4 tier grosir per produk)
        Schema::create('product_prices', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('product_id');
            $table->string('price_type', 10); // normal|grosir
            $table->unsignedBigInteger('amount'); // Rupiah per satuan jual
            $table->integer('min_qty')->nullable(); // wajib untuk grosir, NULL untuk normal
            $table->timestamps(6);

            $table->unique(
                ['store_id', 'product_id', 'price_type', 'min_qty'],
                'prod_prices_store_prod_type_min_unique'
            );

            // Anti lintas-toko: produk harga harus milik toko yang sama
            $table->foreign(['store_id', 'product_id'])
                ->references(['store_id', 'id'])
                ->on('products')
                ->cascadeOnDelete();
        });

        // 9. Stock Movements (append-only riwayat pergerakan stok)
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('qty_delta'); // negatif = keluar (checkout), positif = masuk (release/adjustment)
            $table->string('movement_type', 20); // checkout|release_expired|release_cancel|adjustment
            $table->string('reference_type', 60);
            $table->unsignedBigInteger('reference_id');
            $table->timestampTz('created_at', 6);

            $table->index(['store_id', 'product_id']);
            $table->index(['reference_type', 'reference_id']);

            // Composite FK ke produk milik toko yang sama
            $table->foreign(['store_id', 'product_id'])
                ->references(['store_id', 'id'])
                ->on('products')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('products');
    }
};
