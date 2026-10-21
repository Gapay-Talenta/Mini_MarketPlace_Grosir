<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model Percobaan Pembayaran (QR Payload Attempt).
 *
 * @property int $id
 * @property int $payment_id
 * @property int $attempt_no
 * @property string $provider_reference
 * @property string $qr_payload
 * @property string $status
 * @property Carbon $expires_at
 */
class PaymentAttempt extends Model
{
    protected $fillable = [
        'payment_id',
        'attempt_no',
        'provider_reference',
        'qr_payload',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'attempt_no' => 'integer',
            'expires_at' => 'datetime',
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
