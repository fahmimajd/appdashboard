<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';

    protected $fillable = [
        'nik',
        'nama',
        'password',
        'akses',
        'status_aktif',
        'kode_desa',
        'kode_kecamatan',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
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
     * Get the pendamping record associated with this user.
     */
    public function pendamping(): HasOne
    {
        return $this->hasOne(Pendamping::class, 'nik', 'nik');
    }

    /**
     * Get the desa relation.
     */
    public function desa()
    {
        return $this->belongsTo(WilayahDesa::class, 'kode_desa', 'kode_desa');
    }

    /**
     * Get the kecamatan relation.
     */
    public function kecamatan()
    {
        return $this->belongsTo(WilayahKecamatan::class, 'kode_kecamatan', 'kode_kecamatan');
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status_aktif === 'Aktif';
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->akses === 'Admin';
    }

    /**
     * Check if user is pendamping/operator.
     */
    public function isPendamping(): bool
    {
        return $this->akses === 'Pendamping' || $this->akses === 'Operator';
    }

    /**
     * Check if user is supervisor.
     */
    public function isSupervisor(): bool
    {
        return $this->akses === 'Supervisor';
    }

    /**
     * Check if user is desa.
     */
    public function isDesa(): bool
    {
        return $this->akses === 'Desa';
    }

    /**
     * Check if user is petugas.
     */
    public function isPetugas(): bool
    {
        return $this->akses === 'Petugas';
    }

    /**
     * Get the petugas record associated with this user.
     */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'nik', 'nik');
    }

    /**
     * Scope for active users.
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
