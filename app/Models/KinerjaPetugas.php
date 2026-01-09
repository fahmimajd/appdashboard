<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KinerjaPetugas extends Model
{
    protected $table = 'kinerja_petugas';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

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
        return $this->aktivasi_ikd +
               $this->ikd_desa +
               $this->akta_kelahiran +
               $this->akta_kematian +
               $this->pengajuan_kk +
               $this->pengajuan_pindah +
               $this->pengajuan_kia;
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
}
