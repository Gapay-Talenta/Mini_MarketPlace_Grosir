<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Model Order Induk (Platform-Scoped, TANPA store_id).
 *
 * @property int $id
 * @property int $user_id
 * @property string $status
 * @property int $subtotal
 * @property int $shipping_total
 * @property int $grand_total
 * @property string $checkout_idempotency_key
 * @property Carbon|null $cancelled_at
 */
class Order extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'subtotal',
        'shipping_total',
        'grand_total',
        'checkout_idempotency_key',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'shipping_total' => 'integer',
            'grand_total' => 'integer',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasOne<ShippingAddress, $this>
     */
    public function shippingAddress(): HasOne
    {
        return $this->hasOne(ShippingAddress::class);
    }

    /**
     * @return HasMany<SubOrder, $this>
     */
    public function subOrders(): HasMany
    {
        return $this->hasMany(SubOrder::class);
    }

    /**
     * @return HasOne<Payment, $this>
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
