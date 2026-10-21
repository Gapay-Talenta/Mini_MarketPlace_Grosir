<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model Alamat Pengiriman Pesanan.
 *
 * @property int $id
 * @property int $order_id
 * @property string $receiver_name
 * @property string $phone
 * @property string $address_line
 * @property string $city
 * @property string $postal_code
 * @property string|null $notes
 * @property Carbon $created_at
 */
class ShippingAddress extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'receiver_name',
        'phone',
        'address_line',
        'city',
        'postal_code',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
