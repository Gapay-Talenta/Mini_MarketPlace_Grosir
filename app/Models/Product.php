<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Produk Grosir.
 *
 * @property int $id
 * @property int $store_id
 * @property string $name
 * @property string $category
 * @property string $sales_unit
 * @property string|null $description
 * @property string|null $photo_path
 * @property int $weight_gram
 * @property int $stock
 * @property string $status
 */
class Product extends Model
{
    protected $fillable = [
        'store_id',
        'name',
        'category',
        'sales_unit',
        'description',
        'photo_path',
        'weight_gram',
        'stock',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'weight_gram' => 'integer',
            'stock' => 'integer',
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
     * @return HasMany<ProductPrice, $this>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
