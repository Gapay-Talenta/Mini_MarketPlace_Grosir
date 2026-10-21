<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Tarif Ongkos Kirim per Toko (per kg).
 *
 * @property int $id
 * @property int $store_id
 * @property int $rate_per_kg
 */
class StoreShippingRate extends Model
{
    protected $fillable = [
        'store_id',
        'rate_per_kg',
    ];

    protected function casts(): array
    {
        return [
            'rate_per_kg' => 'integer',
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
