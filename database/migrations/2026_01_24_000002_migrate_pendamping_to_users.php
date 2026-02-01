<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate unique NIK records from pendamping to users
        // Using ROW_NUMBER to get only one record per NIK (in case of duplicates)
        DB::statement("
            INSERT INTO users (nik, nama, password, akses, status_aktif, kode_desa, kode_kecamatan, created_at, updated_at)
            SELECT nik, nama, password, akses, status_aktif, kode_desa, kode_kecamatan, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            FROM (
                SELECT 
                    p.nik,
                    p.nama,
                    p.password,
                    COALESCE(p.akses, 'Pendamping') as akses,
                    COALESCE(p.status_aktif, 'Aktif') as status_aktif,
                    p.kode_desa,
                    NULL as kode_kecamatan,
                    ROW_NUMBER() OVER (PARTITION BY p.nik ORDER BY p.id DESC) as rn
                FROM pendamping p
                WHERE p.nik IS NOT NULL
            ) ranked
            WHERE rn = 1
            AND NOT EXISTS (
                SELECT 1 FROM users u WHERE u.nik = ranked.nik
            )
        ");
    }

    public function down(): void
    {
        // Remove migrated users that came from pendamping
        DB::statement("
            DELETE FROM users 
            WHERE nik IN (SELECT DISTINCT nik FROM pendamping WHERE nik IS NOT NULL)
        ");
    }
};

