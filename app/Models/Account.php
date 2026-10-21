<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Chart of Accounts (COA 5 Akun).
 *
 * @property string $code (Primary Key: 1000|2000|2100|2110|3000)
 * @property string $name
 * @property string $account_type (aset|liabilitas|pendapatan)
 */
class Account extends Model
{
    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'account_type',
    ];

    /**
     * @return HasMany<LedgerEntry, $this>
     */
    public function entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'account_code', 'code');
    }

    /**
     * @return HasMany<BalanceProjection, $this>
     */
    public function projections(): HasMany
    {
        return $this->hasMany(BalanceProjection::class, 'account_code', 'code');
    }
}
