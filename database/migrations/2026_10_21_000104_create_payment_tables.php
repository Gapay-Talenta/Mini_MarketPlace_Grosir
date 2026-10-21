<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Domain D — Pembayaran, QR payment attempts, dan payment events (append-only webhook log).
 * Sumber: docs/schema.dbml & docs/draft_rincian_project_laravel.md §6
 */
return new class extends Migration
{
    public function up(): void
    {
        // 14. Payments (Satu pembayaran gabungan per order induk)
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->restrictOnDelete();
            $table->string('provider', 20)->default('midtrans');
            $table->unsignedBigInteger('amount'); // setara grand_total order
            $table->string('status', 20)->default('pending'); // pending|settled|expired|failed
            $table->timestampTz('settled_at', 6)->nullable();
            $table->timestamps(6);
        });

        // 15. Payment Attempts (Percobaan pembayaran / regenerasi QR payload)
        Schema::create('payment_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->integer('attempt_no'); // regenerasi QR maks 3 per payment
            $table->string('provider_reference', 80)->unique();
            $table->text('qr_payload');
            $table->string('status', 20)->default('active'); // active|settled|expired|cancelled
            $table->timestampTz('expires_at', 6);
            $table->timestamps(6);

            $table->unique(['payment_id', 'attempt_no']);
        });

        // 16. Payment Events (Log append-only event webhook dari payment gateway)
        Schema::create('payment_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
            $table->string('provider_event_id', 80)->unique(); // dedup webhook guard
            $table->string('event_type', 40);
            $table->json('payload');
            $table->string('status', 20)->default('processed'); // processed|duplicate|failed
            $table->timestampTz('created_at', 6);

            $table->index(['payment_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('payment_attempts');
        Schema::dropIfExists('payments');
    }
};
