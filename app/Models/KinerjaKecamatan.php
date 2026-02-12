<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KinerjaKecamatan extends Model
{
    protected $table = 'kinerja_kecamatan';
    
    protected $fillable = [
        'kode_kecamatan',
        'petugas_id',
        'tanggal',
        'rekam_ktp_el',
        'cetak_ktp_el',
        'kartu_keluarga',
        'kia',
        'pindah',
        'kedatangan',
        'akta_kelahiran',
        'akta_kematian',
        'stok_blangko_ktp',
        'stok_blangko_kia',
        'persentase_ribbon',
        'persentase_film',
        'ikd_hari_ini',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'rekam_ktp_el' => 'integer',
        'cetak_ktp_el' => 'integer',
        'kartu_keluarga' => 'integer',
        'kia' => 'integer',
        'pindah' => 'integer',
        'kedatangan' => 'integer',
        'akta_kelahiran' => 'integer',
        'akta_kematian' => 'integer',
        'stok_blangko_ktp' => 'integer',
        'stok_blangko_kia' => 'integer',
        'persentase_ribbon' => 'decimal:2',
        'persentase_film' => 'decimal:2',
        'ikd_hari_ini' => 'integer',
    ];

    /**
     * Get the kecamatan that owns this kinerja
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(WilayahKecamatan::class, 'kode_kecamatan', 'kode_kecamatan');
    }

    /**
     * Get the petugas who created this report
     */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(PetugasKecamatan::class, 'petugas_id', 'nik');
    }

    /**
     * Scope for filtering by period
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }
}
