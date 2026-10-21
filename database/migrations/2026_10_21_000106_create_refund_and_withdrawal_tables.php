<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Domain F — Pengajuan refund pembeli, bukti refund, dan penarikan dana toko (withdrawal).
 * Menegakkan composite FK (store_id, sub_order_id) dan (store_id, bank_account_id).
 * Sumber: docs/schema.dbml & docs/draft_rincian_project_laravel.md §6 & §7
 */
return new class extends Migration
{
    public function up(): void
    {
        // 20. Refund Requests (Pengajuan refund per sub-order oleh pembeli terdaftar)
        Schema::create('refund_requests', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('sub_order_id');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('amount'); // total refund penuh sub-order
            $table->string('status', 20)->default('pending'); // pending|approved|rejected|refunded|payout_failed
            $table->text('reason');
            $table->text('reject_reason')->nullable();
            $table->timestampTz('decided_at', 6)->nullable();
            $table->timestampTz('refunded_at', 6)->nullable();
            $table->timestamps(6);

            $table->index(['store_id', 'sub_order_id', 'status']);
            $table->index(['user_id', 'created_at']);

            // Composite FK: sub-order harus milik toko yang sama
            $table->foreign(['store_id', 'sub_order_id'])
                ->references(['store_id', 'id'])
                ->on('sub_orders')
                ->cascadeOnDelete();
        });

        // 21. Refund Proofs (Bukti gambar kerusakan / komplain refund)
        Schema::create('refund_proofs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('refund_request_id')->constrained('refund_requests')->cascadeOnDelete();
            $table->string('file_path', 255);
            $table->string('mime_type', 40);
            $table->integer('size_bytes');
            $table->timestampTz('created_at', 6);

            $table->index('refund_request_id');
        });

        // 22. Withdrawals (Permintaan penarikan saldo available oleh admin toko)
        Schema::create('withdrawals', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('bank_account_id');
            $table->unsignedBigInteger('amount');
            $table->string('status', 20)->default('requested'); // requested|approved|rejected|disbursed
            $table->timestampTz('requested_at', 6);
            $table->timestampTz('decided_at', 6)->nullable();
            $table->timestampTz('disbursed_at', 6)->nullable();
            $table->timestamps(6);

            $table->foreign('store_id')
                ->references('id')
                ->on('stores')
                ->restrictOnDelete();

            // Composite FK: rekening bank tujuan harus milik toko yang menarik dana
            $table->foreign(['store_id', 'bank_account_id'])
                ->references(['store_id', 'id'])
                ->on('store_bank_accounts')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('refund_proofs');
        Schema::dropIfExists('refund_requests');
    }
};
