<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Harga Produk (Normal & Tier Grosir).
 *
 * @property int $id
 * @property int $store_id
 * @property int $product_id
 * @property string $price_type
 * @property int $amount
 * @property int|null $min_qty
 */
class ProductPrice extends Model
{
    protected $fillable = [
        'store_id',
        'product_id',
        'price_type',
        'amount',
        'min_qty',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'min_qty' => 'integer',
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
