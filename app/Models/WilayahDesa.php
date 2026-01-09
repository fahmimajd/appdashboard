<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WilayahDesa extends Model
{
    protected $table = 'wilayah_desa';
    protected $primaryKey = 'kode_desa';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kode_desa',
        'kode_kecamatan',
        'nama_desa',
        'nama_kepala_desa',
        'titik_koordinat',
        'kontur_wilayah',
        'luas_wilayah',
        'jarak_disdukcapil',
        'jumlah_rt',
        'jumlah_rw',
        'jumlah_dusun',
    ];

    protected $casts = [
        'luas_wilayah' => 'decimal:2',
        'jarak_disdukcapil' => 'decimal:2',
        'jumlah_rt' => 'integer',
        'jumlah_rw' => 'integer',
        'jumlah_dusun' => 'integer',
    ];

    /**
     * Get the kecamatan that owns this desa
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(WilayahKecamatan::class, 'kode_kecamatan', 'kode_kecamatan');
    }

    /**
     * Get the pendamping for this desa
     */
    public function pendamping(): HasOne
    {
        return $this->hasOne(Pendamping::class, 'kode_desa', 'kode_desa');
    }

    /**
     * Get the sarpras for this desa
     */
    public function sarpras(): HasOne
    {
        return $this->hasOne(SarprasDesa::class, 'kode_desa', 'kode_desa');
    }

    /**
     * Get the VPN configuration for this desa
     */
    public function vpn(): HasOne
    {
        return $this->hasOne(VpnDesa::class, 'kode_desa', 'kode_desa');
    }

    /**
     * Get all petugas at desa level
     */
    public function petugas(): HasMany
    {
        return $this->hasMany(Petugas::class, 'kode_desa', 'kode_desa');
    }

    /**
     * Get all kinerja records for this desa
     */
    public function kinerja(): HasMany
    {
        return $this->hasMany(KinerjaPetugas::class, 'kode_desa', 'kode_desa');
    }

    /**
     * Get all kependudukan data for this desa
     */
    public function kependudukan(): HasMany
    {
        return $this->hasMany(KependudukanSemester::class, 'kode_desa', 'kode_desa');
    }
}
