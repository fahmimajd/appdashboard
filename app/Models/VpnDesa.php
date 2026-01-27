<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VpnDesa extends Model
{
    protected $table = 'vpn_desa';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'kode_desa',
        'username',
        'password', // Renamed from password_hash
        'jenis_vpn',
    ];

    protected $hidden = [
        // 'password', // Password is now plain text for display
    ];

    public function desa(): BelongsTo
    {
        return $this->belongsTo(WilayahDesa::class, 'kode_desa', 'kode_desa');
    }

    public function getVpnTypeBadge(): string
    {
        return match($this->jenis_vpn) {
            'PPTP' => 'bg-blue-100 text-blue-800',
            'L2TP' => 'bg-green-100 text-green-800',
            'OpenVPN' => 'bg-purple-100 text-purple-800',
            'WireGuard' => 'bg-indigo-100 text-indigo-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
}
