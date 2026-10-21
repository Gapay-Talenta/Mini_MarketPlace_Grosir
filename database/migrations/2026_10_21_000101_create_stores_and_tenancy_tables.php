<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Domain A — Toko, tenancy, hak akses, komisi, tarif ongkir, dan rekening bank.
 * Sumber: docs/schema.dbml & docs/draft_rincian_project_laravel.md §6
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Lengkapi tabel users dengan kolom phone
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->after('email');
            }
        });

        // 2. Stores (tenant toko grosir)
        Schema::create('stores', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('aktif'); // aktif|nonaktif
            $table->timestamps(6);
        });

        // 3. Store Admins (membership admin toko: owner|admin|operator)
        Schema::create('store_admins', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 20); // owner|admin|operator
            $table->timestamps(6);

            $table->unique(['store_id', 'user_id']);
        });

        // 4. Commission Rates (effective-dated commission rate per store)
        Schema::create('commission_rates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->decimal('rate_percent', 5, 2); // e.g. 10.00
            $table->timestampTz('valid_from', 6);
            $table->timestampTz('valid_to', 6)->nullable(); // null = masih berlaku
            $table->timestamps(6);

            $table->index(['store_id', 'valid_from']);
        });

        // 5. Store Shipping Rates (tarif ongkir flat per kg per toko)
        Schema::create('store_shipping_rates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->unique()->constrained('stores')->cascadeOnDelete();
            $table->unsignedBigInteger('rate_per_kg'); // Rupiah per kg
            $table->timestamps(6);
        });

        // 6. Store Bank Accounts (rekening bank toko terenkripsi, last4, target composite FK withdrawal)
        Schema::create('store_bank_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('bank_name', 60);
            $table->text('account_number_encrypted');
            $table->char('account_last4', 4);
            $table->string('account_holder_name', 120);
            $table->string('status', 20)->default('pending'); // pending|verified|rejected
            $table->timestamps(6);

            // Composite UNIQUE menopang composite FK anti-lintas-toko (misal di tabel withdrawals)
            $table->unique(['store_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_bank_accounts');
        Schema::dropIfExists('store_shipping_rates');
        Schema::dropIfExists('commission_rates');
        Schema::dropIfExists('store_admins');
        Schema::dropIfExists('stores');

        if (Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('phone');
            });
        }
    }
};
