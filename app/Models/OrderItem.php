<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model Item Pesanan (Snapshot).
 *
 * @property int $id
 * @property int $store_id
 * @property int $sub_order_id
 * @property int $product_id
 * @property string $product_name
 * @property int $price_amount
 * @property string $price_type
 * @property int|null $min_qty
 * @property int $qty
 * @property string $sales_unit
 * @property int $weight_gram
 * @property int $line_total
 * @property Carbon $created_at
 */
class OrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'store_id',
        'sub_order_id',
        'product_id',
        'product_name',
        'price_amount',
        'price_type',
        'min_qty',
        'qty',
        'sales_unit',
        'weight_gram',
        'line_total',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'price_amount' => 'integer',
            'min_qty' => 'integer',
            'qty' => 'integer',
            'weight_gram' => 'integer',
            'line_total' => 'integer',
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
     * @return BelongsTo<SubOrder, $this>
     */
    public function subOrder(): BelongsTo
    {
        return $this->belongsTo(SubOrder::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
