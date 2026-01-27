<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pendamping extends Model
{
    protected $table = 'pendamping';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'nik',
        'kode_desa',
        'kode_kecamatan',
        'nama',
        'nomor_ponsel',
        'jenis_kelamin',
        // Note: password, akses, status_aktif are now in users table
        // but kept here for backward compatibility during transition
        'password',
        'akses',
        'status_aktif',
    ];

    protected $hidden = [
        'password',
    ];

    public function getKodeDesaAttribute($value)
    {
        return trim($value);
    }

    public function getKodeKecamatanAttribute($value)
    {
        return trim($value);
    }

    /**
     * Get the desa relation.
     */
    public function desa(): BelongsTo
    {
        return $this->belongsTo(WilayahDesa::class, 'kode_desa', 'kode_desa');
    }

    /**
     * Get the user account associated with this pendamping.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'nik', 'nik');
    }

    /**
     * Scope for active pendamping.
     */
    public function scopeActive($query)
    {
        return $query->where('status_aktif', 'Aktif');
    }

    /**
     * Scope by akses/role.
     */
    public function scopeByAkses($query, $akses)
    {
        return $query->where('akses', $akses);
    }
}
