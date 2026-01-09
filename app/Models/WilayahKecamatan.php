<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WilayahKecamatan extends Model
{
    protected $table = 'wilayah_kecamatan';
    protected $primaryKey = 'kode_kecamatan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kode_kecamatan',
        'kode_kabupaten',
        'nama_kecamatan',
    ];

    /**
     * Get the kabupaten that owns this kecamatan
     */
    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(WilayahKabupaten::class, 'kode_kabupaten', 'kode_kabupaten');
    }

    /**
     * Get all desa for this kecamatan
     */
    public function desa(): HasMany
    {
        return $this->hasMany(WilayahDesa::class, 'kode_kecamatan', 'kode_kecamatan');
    }

    /**
     * Get all petugas at kecamatan level
     */
    public function petugas(): HasMany
    {
        return $this->hasMany(Petugas::class, 'kode_kecamatan', 'kode_kecamatan');
    }
}
