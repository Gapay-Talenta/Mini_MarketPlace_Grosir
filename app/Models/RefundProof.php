<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model Bukti Foto Pengajuan Refund.
 *
 * @property int $id
 * @property int $refund_request_id
 * @property string $file_path
 * @property string $mime_type
 * @property int $size_bytes
 * @property Carbon $created_at
 */
class RefundProof extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'refund_request_id',
        'file_path',
        'mime_type',
        'size_bytes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<RefundRequest, $this>
     */
    public function refundRequest(): BelongsTo
    {
        return $this->belongsTo(RefundRequest::class);
    }
}
