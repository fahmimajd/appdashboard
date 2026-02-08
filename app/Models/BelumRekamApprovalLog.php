<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BelumRekamApprovalLog extends Model
{
    protected $table = 'belum_rekam_approval_logs';

    protected $fillable = [
        'belum_rekam_nik',
        'field_name',
        'old_value',
        'proposed_value',
        'final_value',
        'action',
        'proposed_by',
        'action_by',
        'rejection_reason',
    ];

    /**
     * Field name labels in Indonesian
     */
    public static array $fieldLabels = [
        'keterangan' => 'Keterangan',
    ];

    /**
     * Get the belum rekam record that this log belongs to
     */
    public function belumRekam(): BelongsTo
    {
        return $this->belongsTo(BelumRekam::class, 'belum_rekam_nik', 'nik');
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
}
