<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sasaran extends Model
{
    protected $table = 'sasaran';
    
    protected $fillable = [
        'kode_kecamatan',
        'kode_desa',
        'nik',
        'nama',
        'status',
    ];

    public function desa()
    {
        return $this->belongsTo(WilayahDesa::class, 'kode_desa', 'kode_desa');
    }

    public function kecamatan()
    {
        return $this->belongsTo(WilayahKecamatan::class, 'kode_kecamatan', 'kode_kecamatan');
    }
}
