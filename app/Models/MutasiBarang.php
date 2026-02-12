<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutasiBarang extends Model
{
    protected $table = 'mutasi_barang';
    
    protected $fillable = [
        'barang_id',
        'tanggal',
        'tipe_mutasi',
        'jumlah',
        'lokasi_asal_tipe',
        'lokasi_asal_kecamatan',
        'lokasi_tujuan_tipe',
        'lokasi_tujuan_kecamatan',
        'keterangan',
        'referensi_id',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asalKecamatan()
    {
        return $this->belongsTo(WilayahKecamatan::class, 'lokasi_asal_kecamatan', 'kode_kecamatan');
    }

    public function tujuanKecamatan()
    {
        return $this->belongsTo(WilayahKecamatan::class, 'lokasi_tujuan_kecamatan', 'kode_kecamatan');
    }
}
