<?php

namespace App\Models\Concerns;

use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait untuk model store-owned (dimiliki oleh satu toko grosir).
 *
 * 1. Global Scope: Otomatis membatasi query hanya untuk toko yang sedang aktif di TenantContext.
 * 2. Auto-Fill: Otomatis mengisi store_id saat record baru dibuat jika context aktif terisi.
 * 3. Fail-Closed: Scope hanya memfilter jika context terisi (portal internal selalu mengisi context via SetTenantContext).
 *    Penulisan data tanpa context yang sah akan ditolak oleh constraint NOT NULL database.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // 1. Tambahkan Global Scope 'tenant' pada query builder
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $context = app(TenantContext::class);

            if ($context->has()) {
                // qualifyColumn mencegah SQL ambiguous error saat join antar-tabel
                $builder->where($builder->qualifyColumn('store_id'), $context->id());
            }
        });

        // 2. Auto-fill store_id saat model sedang di-instansiasi untuk disimpan (creating)
        static::creating(function (Model $model): void {
            $context = app(TenantContext::class);

            if ($context->has() && empty($model->getAttribute('store_id'))) {
                $model->setAttribute('store_id', $context->id());
            }
        });
    }
}
