<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Model Sub-Order Toko.
 *
 * @property int $id
 * @property int $order_id
 * @property int $store_id
 * @property string $status
 * @property int $subtotal
 * @property int $shipping_fee
 * @property int $total_weight_gram
 * @property int $rate_per_kg
 * @property string $commission_rate_percent
 * @property int $commission_amount
 * @property int $total
 * @property Carbon|null $shipped_at
 * @property int|null $estimate_days
 * @property Carbon|null $completed_at
 */
class SubOrder extends Model
{
    protected $fillable = [
        'order_id',
        'store_id',
        'status',
        'subtotal',
        'shipping_fee',
        'total_weight_gram',
        'rate_per_kg',
        'commission_rate_percent',
        'commission_amount',
        'total',
        'shipped_at',
        'estimate_days',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'shipping_fee' => 'integer',
            'total_weight_gram' => 'integer',
            'rate_per_kg' => 'integer',
            'commission_rate_percent' => 'decimal:2',
            'commission_amount' => 'integer',
            'total' => 'integer',
            'estimate_days' => 'integer',
            'shipped_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasOne<RefundRequest, $this>
     */
    public function refundRequest(): HasOne
    {
        return $this->hasOne(RefundRequest::class);
    }
}
