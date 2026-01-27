<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$migrations = [
    '2025_01_01_000001_create_wilayah_kabupaten_table',
    '2025_01_01_000002_create_wilayah_kecamatan_table',
    '2025_01_01_000003_create_wilayah_desa_table',
    '2025_01_01_000004_create_header_pelayanan_table',
    '2025_01_01_000005_create_pendamping_table',
    '2025_01_01_000006_create_petugas_table',
    '2025_01_01_000007_create_petugas_kecamatan_table',
    '2025_01_01_000008_create_petugas_dinas_table',
    '2025_01_01_000009_create_sarpras_desa_table',
    '2025_01_01_000010_create_vpn_desa_table',
    '2025_01_01_000011_create_kinerja_petugas_table',
    '2025_01_01_000012_create_kependudukan_semester_table',
    '2026_01_13_035330_create_sasaran_table',
];

foreach ($migrations as $migration) {
    $exists = DB::table('migrations')->where('migration', $migration)->exists();
    if (!$exists) {
        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => 1
        ]);
        echo "Marked as run: $migration\n";
    } else {
        echo "Already ran: $migration\n";
    }
}
echo "Done.\n";
