<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Populate users table from pendamping and petugas tables.
     * Safe to run multiple times - uses INSERT ... NOT EXISTS to avoid duplicates.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('12345');

        // 1. Migrate from Pendamping
        // Pendamping has password and akses columns
        $this->command->info('Migrating users from pendamping table...');
        
        DB::statement("
            INSERT INTO users (nik, nama, password, akses, status_aktif, kode_desa, created_at, updated_at)
            SELECT nik, nama, password, akses, status_aktif, kode_desa, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            FROM (
                SELECT 
                    p.nik,
                    p.nama,
                    p.password,
                    COALESCE(p.akses, 'Pendamping') as akses,
                    COALESCE(p.status_aktif, 'Aktif') as status_aktif,
                    p.kode_desa,
                    ROW_NUMBER() OVER (PARTITION BY p.nik ORDER BY p.id DESC) as rn
                FROM pendamping p
                WHERE p.nik IS NOT NULL
            ) ranked
            WHERE rn = 1
            AND NOT EXISTS (
                SELECT 1 FROM users u WHERE u.nik = ranked.nik
            )
        ");

        $pendampingCount = DB::table('users')->whereIn('akses', ['Pendamping', 'Operator', 'Admin', 'Supervisor'])->count();
        $this->command->info("  -> {$pendampingCount} users migrated from pendamping.");

        // 2. Migrate from Petugas
        // Petugas does not have password, use default
        $this->command->info('Migrating users from petugas table...');
        
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

        $petugasCount = DB::table('users')->where('akses', 'Petugas')->count();
        $this->command->info("  -> {$petugasCount} users migrated from petugas.");

        $totalUsers = DB::table('users')->count();
        $this->command->info("Total users in database: {$totalUsers}");
    }
}
