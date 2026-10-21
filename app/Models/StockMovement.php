<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model Mutasi Stok Produk (Append-Only).
 *
 * @property int $id
 * @property int $store_id
 * @property int $product_id
 * @property int $qty_delta
 * @property string $movement_type
 * @property string $reference_type
 * @property int $reference_id
 * @property Carbon $created_at
 */
class StockMovement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'store_id',
        'product_id',
        'qty_delta',
        'movement_type',
        'reference_type',
        'reference_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'qty_delta' => 'integer',
            'created_at' => 'datetime',
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
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
