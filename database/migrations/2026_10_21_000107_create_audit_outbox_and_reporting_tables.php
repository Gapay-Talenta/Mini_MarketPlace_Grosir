<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Domain G — Jejak audit admin, event outbox realtime (Reverb), Web Push opt-in,
 * dan metadata antrean ekspor laporan.
 * Sumber: docs/schema.dbml & docs/draft_rincian_project_laravel.md §6
 */
return new class extends Migration
{
    public function up(): void
    {
        // 23. Audit Logs (Log append-only jejak keputusan dan aksi admin)
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('action', 60); // approve_refund|reject_refund|verify_bank_account|upsert_commission_rate|...
            $table->string('subject_type', 60); // polymorphic class name
            $table->unsignedBigInteger('subject_id');
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('note', 255)->nullable();
            $table->timestampTz('created_at', 6);

            $table->index(['subject_type', 'subject_id']);
            $table->index(['user_id', 'created_at']);
        });

        // 24. Outbox Events (Transactional outbox untuk realtime broadcast Reverb)
        Schema::create('outbox_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event_type', 80);
            $table->json('payload');
            $table->string('status', 20)->default('pending'); // pending|published|failed
            $table->integer('attempts')->default(0);
            $table->timestampTz('published_at', 6)->nullable();
            $table->timestampTz('created_at', 6);

            $table->index(['status', 'created_at']);
        });

        // 25. Push Subscriptions (Web Push browser endpoint terikat user_id)
        Schema::create('push_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('endpoint', 500);
            $table->string('p256dh', 120);
            $table->string('auth', 60);
            $table->timestampTz('created_at', 6);

            $table->unique(['user_id', 'endpoint']);
        });

        // 26. Report Exports (Job antrean ekspor CSV/XLSX/PDF + signed URL)
        Schema::create('report_exports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('report_type', 40); // sales|ledger|reconciliation
            $table->string('format', 10); // csv|xlsx|pdf
            $table->string('status', 20)->default('pending'); // pending|done|failed
            $table->string('file_path', 255)->nullable();
            $table->string('error_message', 255)->nullable();
            $table->timestampTz('expires_at', 6);
            $table->timestamps(6);

            $table->index(['store_id', 'created_at']);
            $table->index(['requested_by', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('outbox_events');
        Schema::dropIfExists('audit_logs');
    }
};
