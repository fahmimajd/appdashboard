<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetugasKecamatan extends Model
{
    protected $table = 'petugas_kecamatan';
    protected $primaryKey = 'nik';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'nik',
        'nama',
        'nomor_ponsel',
        'jenis_kelamin',
        'status_aktif',
        'tanggal_mulai_akses',
        'bcard',
        'benroller',
        'kode_kecamatan',
    ];

    protected $casts = [
        // 'tanggal_mulai_akses' => 'date',
    ];

    /**
     * Virtual attribute for uniform access
     */
    public function getLevelAksesAttribute()
    {
        return 'Kecamatan';
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(WilayahKecamatan::class, 'kode_kecamatan', 'kode_kecamatan');
    }

    public function isActive(): bool
    {
        return $this->status_aktif === 'Aktif';
    }
}
