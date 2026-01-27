<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KinerjaPetugas extends Model
{
    protected $table = 'kinerja_petugas';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    /**
     * Fields that support approval workflow
     */
    public static array $approvableFields = [
        'aktivasi_ikd',
        'ikd_desa',
        'akta_kelahiran',
        'akta_kematian',
        'pengajuan_kk',
        'pengajuan_pindah',
        'pengajuan_kia',
        'jumlah_login',
        'total_aktivasi_ikd',
    ];

    /**
     * Get SQL for total pelayanan calculation
     */
    public static function sqlTotalPelayanan(): string
    {
        return 'COALESCE(aktivasi_ikd, 0) + COALESCE(akta_kelahiran, 0) + COALESCE(akta_kematian, 0) + COALESCE(pengajuan_kk, 0) + COALESCE(pengajuan_pindah, 0) + COALESCE(pengajuan_kia, 0)';
    }

    protected $fillable = [
        'nik_petugas',
        'kode_desa',
        'tahun',
        'bulan',
        'aktivasi_ikd',
        'ikd_desa',
        'akta_kelahiran',
        'akta_kematian',
        'pengajuan_kk',
        'pengajuan_pindah',
        'pengajuan_kia',
        'jumlah_login',
        'total_aktivasi_ikd',
        // Proposed values for approval workflow
        'aktivasi_ikd_proposed',
        'ikd_desa_proposed',
        'akta_kelahiran_proposed',
        'akta_kematian_proposed',
        'pengajuan_kk_proposed',
        'pengajuan_pindah_proposed',
        'pengajuan_kia_proposed',
        'jumlah_login_proposed',
        'total_aktivasi_ikd_proposed',
        // Approval metadata
        'has_pending_approval',
        'last_proposed_at',
        'last_proposed_by',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
        'aktivasi_ikd' => 'integer',
        'ikd_desa' => 'integer',
        'akta_kelahiran' => 'integer',
        'akta_kematian' => 'integer',
        'pengajuan_kk' => 'integer',
        'pengajuan_pindah' => 'integer',
        'pengajuan_kia' => 'integer',
        'jumlah_login' => 'integer',
        'total_aktivasi_ikd' => 'integer',
        'total_pelayanan' => 'integer',
        // Proposed values casts
        'aktivasi_ikd_proposed' => 'integer',
        'ikd_desa_proposed' => 'integer',
        'akta_kelahiran_proposed' => 'integer',
        'akta_kematian_proposed' => 'integer',
        'pengajuan_kk_proposed' => 'integer',
        'pengajuan_pindah_proposed' => 'integer',
        'pengajuan_kia_proposed' => 'integer',
        'jumlah_login_proposed' => 'integer',
        'total_aktivasi_ikd_proposed' => 'integer',
        'has_pending_approval' => 'boolean',
        'last_proposed_at' => 'datetime',
    ];

    /**
     * Get the petugas that owns this kinerja
     */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'nik_petugas', 'nik');
    }

    /**
     * Get the desa that owns this kinerja
     */
    public function desa(): BelongsTo
    {
        return $this->belongsTo(WilayahDesa::class, 'kode_desa', 'kode_desa');
    }

    /**
     * Get total pelayanan
     */
    public function getTotalPelayanan(): int
    {
        // Excludes ikd_desa (which is part of total_aktivasi) and jumlah_login
        return $this->aktivasi_ikd +
               $this->akta_kelahiran +
               $this->akta_kematian +
               $this->pengajuan_kk +
               $this->pengajuan_pindah +
               $this->pengajuan_kia;
    }

    /**
     * Get total aktivasi
     */
    public function getTotalAktivasi(): int
    {
        return $this->total_aktivasi_ikd ?? 0;
    }

    /**
     * Get month name in Indonesian
     */
    public function getBulanNama(): string
    {
        $bulanNames = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        return $bulanNames[$this->bulan] ?? '';
    }

    /**
     * Get periode (e.g., "Januari 2024")
     */
    public function getPeriode(): string
    {
        return $this->getBulanNama() . ' ' . $this->tahun;
    }

    /**
     * Scope for specific period
     */
    public function scopeForPeriod($query, $tahun, $bulan = null)
    {
        $query->where('tahun', $tahun);
        if ($bulan) {
            $query->where('bulan', $bulan);
        }
        return $query;
    }

    /**
     * Scope for specific desa
     */
    public function scopeForDesa($query, $kodeDesa)
    {
        return $query->where('kode_desa', $kodeDesa);
    }

    /**
     * Get approval logs for this kinerja
     */
    public function approvalLogs(): HasMany
    {
        return $this->hasMany(KinerjaApprovalLog::class, 'kinerja_id');
    }

    /**
     * Get the user who last proposed changes
     */
    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_proposed_by', 'nik');
    }

    /**
     * Check if this kinerja has any pending approval
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
     * Returns [current, proposed] or [current, null] if no pending
     */
    public function getFieldWithProposed(string $fieldName): array
    {
        $proposedField = $fieldName . '_proposed';
        return [
            $this->$fieldName ?? 0,
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
     * Clear all proposed values and update has_pending_approval flag
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
     * Scope for records with pending approvals
     */
    public function scopeWithPending($query)
    {
        return $query->where('has_pending_approval', true);
    }
}

