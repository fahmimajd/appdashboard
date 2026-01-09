<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SarprasDesa extends Model
{
    protected $table = 'sarpras_desa';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'kode_desa',
        'komputer',
        'printer',
        'internet',
        'ruang_pelayanan',
        'provider',
    ];

    protected $casts = [
        'komputer' => 'integer',
        'printer' => 'integer',
        'internet' => 'integer',
    ];

    /**
     * Get the desa that owns this sarpras
     */
    public function desa(): BelongsTo
    {
        return $this->belongsTo(WilayahDesa::class, 'kode_desa', 'kode_desa');
    }

    /**
     * Check if desa has internet
     */
    public function hasInternet(): bool
    {
        return $this->internet > 0;
    }

    /**
     * Check if desa has ruang pelayanan
     */
    public function hasRuangPelayanan(): bool
    {
        return $this->ruang_pelayanan === 'Ada';
    }

    /**
     * Calculate completeness score (0-100)
     */
    public function getCompletenessScore(): int
    {
        $score = 0;
        if ($this->komputer > 0) $score += 25;
        if ($this->printer > 0) $score += 25;
        if ($this->internet > 0) $score += 25;
        if ($this->ruang_pelayanan === 'Ada') $score += 25;
        return $score;
    }
}
