<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model Antrean Job Ekspor Laporan.
 *
 * @property int $id
 * @property int $store_id
 * @property int $requested_by
 * @property string $report_type
 * @property string $format
 * @property string $status
 * @property string|null $file_path
 * @property string|null $error_message
 * @property Carbon $expires_at
 */
class ReportExport extends Model
{
    protected $fillable = [
        'store_id',
        'requested_by',
        'report_type',
        'format',
        'status',
        'file_path',
        'error_message',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
