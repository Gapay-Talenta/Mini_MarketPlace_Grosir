<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model Tarif Komisi Toko (Effective-Dated).
 *
 * @property int $id
 * @property int $store_id
 * @property string $rate_percent
 * @property Carbon $valid_from
 * @property Carbon|null $valid_to
 */
class CommissionRate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'store_id',
        'rate_percent',
        'valid_from',
        'valid_to',
    ];

    protected function casts(): array
    {
        return [
            'rate_percent' => 'decimal:2',
            'valid_from' => 'datetime',
            'valid_to' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Scope untuk mengambil tarif komisi yang berlaku pada instan waktu tertentu.
     * Menggunakan interval setengah-terbuka [valid_from, valid_to).
     *
     * @param  Builder<self>  $query
     */
    public function scopeEffectiveAt(Builder $query, CarbonInterface $instant): void
    {
        $query->where('valid_from', '<=', $instant)
            ->where(function (Builder $query) use ($instant): void {
                $query->whereNull('valid_to')->orWhere('valid_to', '>', $instant);
            });
    }
}
