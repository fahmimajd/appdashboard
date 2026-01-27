<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BelumAkte extends Model
{
    protected $table = 'belum_akte';
    protected $primaryKey = 'nik';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    
    protected $fillable = [
        'nik',
        'nama_lgkp',
        'tgl_lhr',
        'jenis_klmin',
        'kode_kecamatan',
        'kode_desa',
        'keterangan',
    ];

    public function getKodeDesaAttribute($value)
    {
        return trim($value);
    }

    public function getKodeKecamatanAttribute($value)
    {
        return trim($value);
    }

    public function desa()
    {
        return $this->belongsTo(WilayahDesa::class, 'kode_desa', 'kode_desa');
    }

    public function kecamatan()
    {
        return $this->belongsTo(WilayahKecamatan::class, 'kode_kecamatan', 'kode_kecamatan');
    }
}
