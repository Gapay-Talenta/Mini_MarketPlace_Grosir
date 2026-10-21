<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Domain E — Double-entry ledger 5 akun (append-only) dan balance projections.
 * Akun: 1000 Kas, 2000 Saldo Toko, 2100 Hold Refund, 2110 Hold Withdrawal, 3000 Komisi Platform.
 * Invariant: Setiap reference seimbang (sum debit = sum credit); ledger append-only (reversal entry).
 * Sumber: docs/schema.dbml & docs/draft_rincian_project_laravel.md §5
 */
return new class extends Migration
{
    public function up(): void
    {
        // 17. Accounts (Chart of Accounts - 5 akun baku)
        Schema::create('accounts', function (Blueprint $table): void {
            $table->char('code', 4)->primary(); // 1000|2000|2100|2110|3000
            $table->string('name', 80);
            $table->string('account_type', 20); // aset|liabilitas|pendapatan
        });

        // 18. Ledger Entries (Double-entry journal append-only)
        Schema::create('ledger_entries', function (Blueprint $table): void {
            $table->id();
            $table->char('account_code', 4);
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->unsignedBigInteger('debit')->default(0);
            $table->unsignedBigInteger('credit')->default(0);
            $table->string('idempotency_key', 80)->unique();
            $table->string('reference_type', 60);
            $table->unsignedBigInteger('reference_id');
            $table->string('note', 255)->nullable();
            $table->timestampTz('created_at', 6);

            $table->foreign('account_code')
                ->references('code')
                ->on('accounts')
                ->restrictOnDelete();

            $table->index(['reference_type', 'reference_id']);
            $table->index(['account_code', 'created_at']);
        });

        // 19. Balance Projections (Proyeksi saldo saat ini, dapat direkonsiliasi ulang dari ledger)
        Schema::create('balance_projections', function (Blueprint $table): void {
            $table->id();
            $table->char('account_code', 4);
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->bigInteger('balance')->default(0);
            $table->timestampTz('updated_at', 6);

            $table->foreign('account_code')
                ->references('code')
                ->on('accounts')
                ->restrictOnDelete();

            $table->index(['account_code', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balance_projections');
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('accounts');
    }
};
