<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model Penarikan Saldo Toko.
 *
 * @property int $id
 * @property int $store_id
 * @property int $bank_account_id
 * @property int $amount
 * @property string $status
 * @property Carbon $requested_at
 * @property Carbon|null $decided_at
 * @property Carbon|null $disbursed_at
 */
class Withdrawal extends Model
{
    protected $fillable = [
        'store_id',
        'bank_account_id',
        'amount',
        'status',
        'requested_at',
        'decided_at',
        'disbursed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'requested_at' => 'datetime',
            'decided_at' => 'datetime',
            'disbursed_at' => 'datetime',
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
     * @return BelongsTo<StoreBankAccount, $this>
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(StoreBankAccount::class, 'bank_account_id');
    }
}
