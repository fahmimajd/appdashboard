<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterBarang extends Model
{
    protected $table = 'master_barang';
    
    protected $fillable = [
        'kode',
        'nama',
        'satuan',
        'kategori',
        'auto_kurang',
        'field_kinerja',
        'field_stok_laporan',
        'keterangan',
    ];

    protected $casts = [
        'auto_kurang' => 'boolean',
    ];

    public function stok()
    {
        return $this->hasMany(StokBarang::class, 'barang_id');
    }

    public function mutasi()
    {
        return $this->hasMany(MutasiBarang::class, 'barang_id');
    }
}
