<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Model Pengajuan Refund Sub-Order.
 *
 * @property int $id
 * @property int $store_id
 * @property int $sub_order_id
 * @property int $user_id
 * @property int $amount
 * @property string $status
 * @property string $reason
 * @property string|null $reject_reason
 * @property Carbon|null $decided_at
 * @property Carbon|null $refunded_at
 */
class RefundRequest extends Model
{
    protected $fillable = [
        'store_id',
        'sub_order_id',
        'user_id',
        'amount',
        'status',
        'reason',
        'reject_reason',
        'decided_at',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'decided_at' => 'datetime',
            'refunded_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<RefundProof, $this>
     */
    public function proofs(): HasMany
    {
        return $this->hasMany(RefundProof::class);
    }
}
