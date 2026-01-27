<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BelumRekam extends Model
{
    protected $table = 'BELUM_REKAM';
    protected $primaryKey = 'NIK';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    
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
    }}
