<?php

namespace App\Modules\Admin\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Service untuk mencatat jejak audit tindakan administratif secara append-only.
 * Memastikan data sensitif (nomor rekening mentah, password) disanitasi sebelum disimpan.
 */
final class AuditLogger
{
    /**
     * Daftar atribut sensitif yang wajib disanitasi / dibuang dari diff audit.
     *
     * @var array<int, string>
     */
    private const SENSITIVE_KEYS = [
        'account_number',
        'account_number_encrypted',
        'password',
        'password_hash',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Rekam aktivitas audit baru.
     *
     * @param  class-string<Model>|string  $subjectType
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public function record(
        string $action,
        string $subjectType,
        int|string $subjectId,
        ?array $before = null,
        ?array $after = null,
        ?string $note = null,
        ?int $userId = null,
    ): AuditLog {
        $actorId = $userId ?? Auth::id();

        if ($actorId === null) {
            throw new RuntimeException('AuditLogger membutuhkan aktor yang valid (user_id atau user terautentikasi).');
        }

        return AuditLog::create([
            'user_id' => $actorId,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => (int) $subjectId,
            'before' => $before !== null ? $this->sanitize($before) : null,
            'after' => $after !== null ? $this->sanitize($after) : null,
            'note' => $note,
            'created_at' => now(),
        ]);
    }

    /**
     * Hapus kunci data sensitif dari payload data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function sanitize(array $data): array
    {
        foreach (self::SENSITIVE_KEYS as $key) {
            unset($data[$key]);
        }

        return $data;
    }
}
