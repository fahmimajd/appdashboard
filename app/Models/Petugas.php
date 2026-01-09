<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Petugas extends Model
{
    protected $table = 'petugas';
    protected $primaryKey = 'nik';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // No timestamps in db.md

    protected $fillable = [
        'nik',
        'nama',
        'nomor_ponsel',
        'jenis_kelamin',
        'level_akses', // Kept as nullable string per migration/db.md manual note
        'status_aktif',
        'keterangan_nonaktif',
        'tanggal_mulai_aktif',
        'kode_desa',
        'kode_kecamatan',
        'kode_kabupaten',
    ];

    protected $casts = [
        // 'tanggal_mulai_akses' => 'date', // Changed to varchar2(20) per user request
    ];

    public function desa(): BelongsTo
    {
        return $this->belongsTo(WilayahDesa::class, 'kode_desa', 'kode_desa');
    }



    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(WilayahKecamatan::class, 'kode_kecamatan', 'kode_kecamatan');
    }

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(WilayahKabupaten::class, 'kode_kabupaten', 'kode_kabupaten');
    }

    public function kinerja(): HasMany
    {
        return $this->hasMany(KinerjaPetugas::class, 'nik_petugas', 'nik');
    }

    public function isActive(): bool
    {
        return $this->status_aktif === 'Aktif';
    }
}
