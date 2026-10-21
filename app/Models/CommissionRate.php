<?php

namespace App\Models;

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
}
