<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model Event Webhook Pembayaran (Append-Only).
 *
 * @property int $id
 * @property int $payment_id
 * @property string $provider_event_id
 * @property string $event_type
 * @property array<string, mixed> $payload
 * @property string $status
 * @property Carbon $created_at
 */
class PaymentEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'payment_id',
        'provider_event_id',
        'event_type',
        'payload',
        'status',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
