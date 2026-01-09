<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WilayahKabupaten extends Model
{
    protected $table = 'wilayah_kabupaten';
    protected $primaryKey = 'kode_kabupaten';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kode_kabupaten',
        'nama_kabupaten',
    ];

    /**
     * Get all kecamatan for this kabupaten
     */
    public function kecamatan(): HasMany
    {
        return $this->hasMany(WilayahKecamatan::class, 'kode_kabupaten', 'kode_kabupaten');
    }

    /**
     * Get all petugas at kabupaten level
     */
    public function petugas(): HasMany
    {
        return $this->hasMany(Petugas::class, 'kode_kabupaten', 'kode_kabupaten');
    }
}
