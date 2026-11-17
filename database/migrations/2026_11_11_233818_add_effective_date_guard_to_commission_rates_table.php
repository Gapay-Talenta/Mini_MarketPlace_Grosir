<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pengaman database lapis terakhir untuk komisi effective-dated:
 * Hanya boleh ada SATU baris dengan valid_to IS NULL per store_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_rates', function (Blueprint $table): void {
            // Kolom virtual: bernilai store_id jika valid_to IS NULL, selain itu NULL.
            $table->unsignedBigInteger('active_lock')
                ->virtualAs('case when `valid_to` is null then `store_id` else null end')
                ->nullable()
                ->after('valid_to');

            // Unique index pada kolom virtual memastikan hanya ada satu record aktif per store
            $table->unique('active_lock', 'uq_commission_active_per_store');
        });
    }

    public function down(): void
    {
        Schema::table('commission_rates', function (Blueprint $table): void {
            $table->dropUnique('uq_commission_active_per_store');
            $table->dropColumn('active_lock');
        });
    }
};
