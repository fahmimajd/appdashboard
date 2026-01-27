<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // Default password for petugas users
        $defaultPassword = Hash::make('12345');

        // Migrate unique NIK records from petugas to users
        // Using ROW_NUMBER to get only one record per NIK (in case of duplicates)
        DB::statement("
            INSERT INTO users (nik, nama, password, akses, status_aktif, kode_desa, kode_kecamatan, created_at, updated_at)
            SELECT nik, nama, '{$defaultPassword}', 'Petugas', status_aktif, kode_desa, kode_kecamatan, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            FROM (
                SELECT 
                    p.nik,
                    p.nama,
                    COALESCE(p.status_aktif, 'Aktif') as status_aktif,
                    p.kode_desa,
                    p.kode_kecamatan,
                    ROW_NUMBER() OVER (PARTITION BY p.nik ORDER BY p.nik) as rn
                FROM petugas p
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
        // Remove migrated users with 'Petugas' role that came from petugas table
        DB::statement("
            DELETE FROM users 
            WHERE akses = 'Petugas'
            AND nik IN (SELECT DISTINCT nik FROM petugas WHERE nik IS NOT NULL)
        ");
    }
};
