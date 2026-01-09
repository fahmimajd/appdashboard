<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetugasDinas extends Model
{
    protected $table = 'petugas_dinas';
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
        'tanggal_mulai_aktif',
        'bcard',
        'benroller',
        'kode_kabupaten',
    ];

    protected $casts = [
        // 'tanggal_mulai_akses' => 'date',
    ];

    /**
     * Virtual attribute for uniform access
     */
    public function getLevelAksesAttribute()
    {
        return 'Dinas';
    }

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(WilayahKabupaten::class, 'kode_kabupaten', 'kode_kabupaten');
    }

    public function isActive(): bool
    {
        return $this->status_aktif === 'Aktif';
    }
}
