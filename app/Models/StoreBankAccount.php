<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Rekening Bank Toko.
 *
 * @property int $id
 * @property int $store_id
 * @property string $bank_name
 * @property string $account_number_encrypted
 * @property string $account_last4
 * @property string $account_holder_name
 * @property string $status
 */
class StoreBankAccount extends Model
{
    protected $fillable = [
        'store_id',
        'bank_name',
        'account_number_encrypted',
        'account_last4',
        'account_holder_name',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'account_number_encrypted' => 'encrypted',
        ];
    }

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
