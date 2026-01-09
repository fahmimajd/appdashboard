<?php

use App\Models\Petugas;
use App\Models\WilayahDesa;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debug Petugas -> Desa Relation\n\n";

// Get first 5 Petugas with level 'Desa'
$petugas = Petugas::where('level_akses', 'Desa')->take(5)->get();

if ($petugas->isEmpty()) {
    echo "No Petugas with level_akses = 'Desa' found.\n";
    // Check if there are any petugas at all and what their levels are
    $all = Petugas::take(5)->get();
    echo "Found " . $all->count() . " total petugas. Levels: " . $all->pluck('level_akses')->implode(', ') . "\n";
} else {
    foreach ($petugas as $p) {
        echo "NIK: {$p->nik}\n";
        echo "Nama: {$p->nama}\n";
        echo "Kode Desa (Raw): '{$p->kode_desa}'\n";
        echo "Level Akses: '{$p->level_akses}'\n";
        
        $desa = $p->desa;
        if ($desa) {
            echo "Desa Found: {$desa->nama_desa} (Kode: '{$desa->kode_desa}')\n";
        } else {
            echo "Desa Relation is NULL.\n";
            // Try to find manual
            $manualDesa = WilayahDesa::where('kode_desa', $p->kode_desa)->first();
            if ($manualDesa) {
                echo " [matches manual query] Found via manual query: {$manualDesa->nama_desa}\n";
            } else {
                 echo " [matches manual query] NOT FOUND via manual query either.\n";
                 // Check if trimming helps
                 $trimmed = trim($p->kode_desa);
                 $manualTrimmed = WilayahDesa::where('kode_desa', $trimmed)->first();
                 if ($manualTrimmed) {
                     echo " [matches trimmed] Found via trimmed query ('{$trimmed}'): {$manualTrimmed->nama_desa}\n";
                 }
            }
        }
        echo "-------------------\n";
    }
}
