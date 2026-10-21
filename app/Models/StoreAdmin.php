<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Membership Admin Toko.
 *
 * @property int $id
 * @property int $store_id
 * @property int $user_id
 * @property string $role
 */
class StoreAdmin extends Model
{
    protected $fillable = [
        'store_id',
        'user_id',
        'role',
    ];

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
