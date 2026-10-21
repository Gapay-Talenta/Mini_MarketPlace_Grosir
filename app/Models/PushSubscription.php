<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model Langganan Web Push Browser.
 *
 * @property int $id
 * @property int $user_id
 * @property string $endpoint
 * @property string $p256dh
 * @property string $auth
 * @property Carbon $created_at
 */
class PushSubscription extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'endpoint',
        'p256dh',
        'auth',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
