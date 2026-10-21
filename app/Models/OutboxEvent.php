<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Model Transactional Outbox Event (Broadcast Reverb).
 *
 * @property int $id
 * @property string $event_type
 * @property array<string, mixed> $payload
 * @property string $status
 * @property int $attempts
 * @property Carbon|null $published_at
 * @property Carbon $created_at
 */
class OutboxEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event_type',
        'payload',
        'status',
        'attempts',
        'published_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'attempts' => 'integer',
            'published_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}
