<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model Jurnal Buku Besar (Double-Entry Ledger Append-Only).
 *
 * @property int $id
 * @property string $account_code
 * @property int|null $store_id
 * @property int $debit
 * @property int $credit
 * @property string $idempotency_key
 * @property string $reference_type
 * @property int $reference_id
 * @property string|null $note
 * @property Carbon $created_at
 */
class LedgerEntry extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'account_code',
        'store_id',
        'debit',
        'credit',
        'idempotency_key',
        'reference_type',
        'reference_id',
        'note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'debit' => 'integer',
            'credit' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_code', 'code');
    }

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
