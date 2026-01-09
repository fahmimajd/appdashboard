<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pendamping extends Authenticatable
{
    protected $table = 'pendamping';
    protected $primaryKey = 'nik';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'nik',
        'kode_desa',
        'nama',
        'nomor_ponsel',
        'jenis_kelamin',
        'status_aktif',
        'password',
        'akses',
        // last_password_change removed per db.md
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed', // Laravel 10+
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(WilayahDesa::class, 'kode_desa', 'kode_desa');
    }

    public function isActive(): bool
    {
        return $this->status_aktif === 'Aktif';
    }

    public function isAdmin(): bool
    {
        return $this->akses === 'Admin';
    }

    public function scopeActive($query)
    {
        return $query->where('status_aktif', 'Aktif');
    }

    public function scopeByAkses($query, $akses)
    {
        return $query->where('akses', $akses);
    }
}
