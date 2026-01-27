<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KinerjaApprovalLog extends Model
{
    protected $table = 'kinerja_approval_logs';

    protected $fillable = [
        'kinerja_id',
        'field_name',
        'old_value',
        'proposed_value',
        'final_value',
        'action',
        'proposed_by',
        'action_by',
        'rejection_reason',
    ];

    protected $casts = [
        'old_value' => 'integer',
        'proposed_value' => 'integer',
        'final_value' => 'integer',
    ];

    /**
     * Field name labels in Indonesian
     */
    public static array $fieldLabels = [
        'aktivasi_ikd' => 'Aktivasi IKD',
        'ikd_desa' => 'Total IKD Desa',
        'akta_kelahiran' => 'Akta Kelahiran',
        'akta_kematian' => 'Akta Kematian',
        'pengajuan_kk' => 'Pengajuan KK',
        'pengajuan_pindah' => 'Pengajuan Pindah',
        'pengajuan_kia' => 'Pengajuan KIA',
        'jumlah_login' => 'Jumlah Login',
        'total_aktivasi_ikd' => 'Total Aktivasi IKD',
    ];

    /**
     * Get the kinerja that this log belongs to
     */
    public function kinerja(): BelongsTo
    {
        return $this->belongsTo(KinerjaPetugas::class, 'kinerja_id');
    }

    /**
     * Get the user who proposed the change
     */
    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by', 'nik');
    }

    /**
     * Get the user who approved/rejected the change
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by', 'nik');
    }

    /**
     * Get field label in Indonesian
     */
    public function getFieldLabel(): string
    {
        return self::$fieldLabels[$this->field_name] ?? $this->field_name;
    }

    /**
     * Check if this log is an approval
     */
    public function isApproved(): bool
    {
        return $this->action === 'approved';
    }

    /**
     * Check if this log is a rejection
     */
    public function isRejected(): bool
    {
        return $this->action === 'rejected';
    }

    /**
     * Scope for approvals only
     */
    public function scopeApprovals($query)
    {
        return $query->where('action', 'approved');
    }

    /**
     * Scope for rejections only
     */
    public function scopeRejections($query)
    {
        return $query->where('action', 'rejected');
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
