<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeaderPelayanan extends Model
{
    protected $table = 'header_pelayanan';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'nomor_pelayanan',
        'nomor_pengaduan',
        'tanggal_dibuat',
    ];

    protected $casts = [
        'tanggal_dibuat' => 'datetime',
    ];

    /**
     * Get formatted tanggal
     */
    public function getTanggalFormatted(): string
    {
        return $this->tanggal_dibuat?->format('d/m/Y H:i') ?? '';
    }

    /**
     * Scope for recent records
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('tanggal_dibuat', 'desc')->limit($limit);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_dibuat', [$startDate, $endDate]);
    }
}
