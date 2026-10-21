<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model Proyeksi Saldo Akun / Toko.
 *
 * @property int $id
 * @property string $account_code
 * @property int|null $store_id
 * @property int $balance
 * @property Carbon $updated_at
 */
class BalanceProjection extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'account_code',
        'store_id',
        'balance',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
            'updated_at' => 'datetime',
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
