<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BelumRekam extends Model
{
    protected $table = 'BELUM_REKAM';
    protected $primaryKey = 'NIK';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    /**
     * Fields that support approval workflow
     */
    public static array $approvableFields = [
        'keterangan',
    ];
    
    protected $fillable = [
        'NIK',
        'NAMA_LGKP',
        'JENIS_KLM',
        'TMPT_LHR',
        'TGL_LHR',
        'WKTP_KET',
        'KODE_KECAMATAN',
        'KODE_DESA',
        'KETERANGAN',
        'KET_DISABILITAS',
        'CURRENT_STATUS_CODE',
        // Proposed values for approval workflow
        'keterangan_proposed',
        // Approval metadata
        'has_pending_approval',
        'last_proposed_at',
        'last_proposed_by',
    ];

    protected $casts = [
        'has_pending_approval' => 'boolean',
        'last_proposed_at' => 'datetime',
    ];

    public function getKodeDesaAttribute($value)
    {
        return trim($value);
    }

    public function getKodeKecamatanAttribute($value)
    {
        return trim($value);
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(WilayahDesa::class, 'kode_desa', 'kode_desa');
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(WilayahKecamatan::class, 'kode_kecamatan', 'kode_kecamatan');
    }

    /**
     * Get approval logs for this record
     */
    public function approvalLogs(): HasMany
    {
        return $this->hasMany(BelumRekamApprovalLog::class, 'belum_rekam_nik', 'nik');
    }

    /**
     * Get the user who last proposed changes
     */
    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_proposed_by', 'nik');
    }

    /**
     * Check if this record has any pending approval
     */
    public function hasPendingApproval(): bool
    {
        return $this->has_pending_approval ?? false;
    }

    /**
     * Get list of fields that have pending proposed values
     */
    public function getPendingFields(): array
    {
        $pending = [];
        foreach (self::$approvableFields as $field) {
            $proposedField = $field . '_proposed';
            if ($this->$proposedField !== null) {
                $pending[] = $field;
            }
        }
        return $pending;
    }

    /**
     * Get current and proposed value for a field
     */
    public function getFieldWithProposed(string $fieldName): array
    {
        $proposedField = $fieldName . '_proposed';
        return [
            $this->$fieldName ?? '',
            $this->$proposedField,
        ];
    }

    /**
     * Check if a specific field has pending approval
     */
    public function fieldHasPending(string $fieldName): bool
    {
        $proposedField = $fieldName . '_proposed';
        return $this->$proposedField !== null;
    }

    /**
     * Update the has_pending_approval flag based on current proposed values
     */
    public function updatePendingFlag(): void
    {
        $hasPending = false;
        foreach (self::$approvableFields as $field) {
            $proposedField = $field . '_proposed';
            if ($this->$proposedField !== null) {
                $hasPending = true;
                break;
            }
        }
        $this->has_pending_approval = $hasPending;
        $this->save();
    }

    /**
     * Clear all proposed values
     */
    public function clearAllProposed(): void
    {
        foreach (self::$approvableFields as $field) {
            $proposedField = $field . '_proposed';
            $this->$proposedField = null;
        }
        $this->has_pending_approval = false;
        $this->save();
    }

    /**
     * Scope for records with pending approvals
     */
    public function scopeWithPending($query)
    {
        return $query->where('has_pending_approval', true);
    }
}
