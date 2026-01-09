<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KependudukanSemester extends Model
{
    protected $table = 'kependudukan_semester';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'kode_desa',
        'kode_semester',
        'jumlah_penduduk',
        'jumlah_laki',
        'jumlah_perempuan',
        'wajib_ktp',
        'kartu_keluarga',
        'akta_kelahiran_jml',
        'akta_kelahiran_persen',
        'akta_kematian_jml',
        'akta_kematian_persen',
        'kepemilikan_ktp_jml',
        'kepemilikan_ktp_persen',
        'kepemilikan_kia_jml',
        'kepemilikan_kia_persen',
        'jumlah_kematian',
        'prr',
        'pindah_keluar',
        'status_kawin_jml',
        'status_kawin_persen',
    ];

    protected $casts = [
        'jumlah_penduduk' => 'integer',
        'jumlah_laki' => 'integer',
        'jumlah_perempuan' => 'integer',
        'wajib_ktp' => 'integer',
        'kartu_keluarga' => 'integer',
        'akta_kelahiran_jml' => 'integer',
        'akta_kelahiran_persen' => 'decimal:2',
        'akta_kematian_jml' => 'integer',
        'akta_kematian_persen' => 'decimal:2',
        'kepemilikan_ktp_jml' => 'integer',
        'kepemilikan_ktp_persen' => 'decimal:2',
        'kepemilikan_kia_jml' => 'integer',
        'kepemilikan_kia_persen' => 'decimal:2',
        'jumlah_kematian' => 'integer',
        'prr' => 'integer',
        'pindah_keluar' => 'integer',
        'status_kawin_jml' => 'integer',
        'status_kawin_persen' => 'decimal:2',
    ];

    /**
     * Get the desa that owns this data
     */
    public function desa(): BelongsTo
    {
        return $this->belongsTo(WilayahDesa::class, 'kode_desa', 'kode_desa');
    }

    /**
     * Parse semester code (format: YYYYS, e.g., "20241" for Semester 1 2024)
     */
    public function getTahun(): int
    {
        return intval(substr($this->kode_semester, 0, 4));
    }

    /**
     * Get semester number (1 or 2)
     */
    public function getSemester(): int
    {
        return intval(substr($this->kode_semester, 4, 1));
    }

    /**
     * Get semester name
     */
    public function getSemesterNama(): string
    {
        return 'Semester ' . $this->getSemester() . ' ' . $this->getTahun();
    }

    /**
     * Get percentage of male population
     */
    public function getPersenLaki(): float
    {
        if ($this->jumlah_penduduk == 0) return 0;
        return round(($this->jumlah_laki / $this->jumlah_penduduk) * 100, 2);
    }

    /**
     * Get percentage of female population
     */
    public function getPersenPerempuan(): float
    {
        if ($this->jumlah_penduduk == 0) return 0;
        return round(($this->jumlah_perempuan / $this->jumlah_penduduk) * 100, 2);
    }

    /**
     * Scope for specific semester
     */
    public function scopeForSemester($query, $kodeSemester)
    {
        return $query->where('kode_semester', $kodeSemester);
    }

    /**
     * Scope for specific year
     */
    public function scopeForYear($query, $tahun)
    {
        return $query->where('kode_semester', 'LIKE', $tahun . '%');
    }
}
