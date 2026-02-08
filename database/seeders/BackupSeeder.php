<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupSeeder extends Seeder
{
    /**
     * Tables to backup/restore.
     * Order matters for foreign key constraints.
     */
    protected array $tables = [
        'wilayah_kabupaten',
        'wilayah_kecamatan',
        'wilayah_desa',
        'pendamping',
        'petugas',
        'petugas_kecamatan',
        'petugas_dinas',
        'users',
        'kinerja_petugas',
        'kependudukan_semester',
        'sarpras_desa',
        'vpn_desa',
        'header_pelayanan',
        'sasaran',
        'belum_rekam',
        'belum_akte',
        'kinerja_approval_logs',
        'export_logs',
    ];

    protected string $backupPath;

    public function __construct()
    {
        $this->backupPath = database_path('backups');
    }

    /**
     * Export all data to JSON files for backup.
     * Run: php artisan db:seed --class=BackupSeeder
     * 
     * To import: php artisan db:seed --class=BackupSeeder -- --import
     */
    public function run(): void
    {
        // Check if --import flag is present
        $args = $_SERVER['argv'] ?? [];
        $isImport = in_array('--import', $args);

        if ($isImport) {
            $this->importBackup();
        } else {
            $this->exportBackup();
        }
    }

    /**
     * Export all tables to JSON files.
     */
    protected function exportBackup(): void
    {
        if (!File::isDirectory($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupDir = "{$this->backupPath}/{$timestamp}";
        File::makeDirectory($backupDir, 0755, true);

        $this->command->info("Exporting database backup to: {$backupDir}");

        foreach ($this->tables as $table) {
            $this->command->info("  Exporting {$table}...");
            
            try {
                $data = DB::table($table)->get()->toArray();
                $count = count($data);
                
                // Convert objects to arrays for JSON encoding
                $data = array_map(function ($item) {
                    return (array) $item;
                }, $data);

                $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                File::put("{$backupDir}/{$table}.json", $json);
                
                $this->command->info("    -> {$count} records exported.");
            } catch (\Exception $e) {
                $this->command->warn("    -> Failed: {$e->getMessage()}");
            }
        }

        // Create a manifest
        $manifest = [
            'created_at' => now()->toIso8601String(),
            'tables' => $this->tables,
        ];
        File::put("{$backupDir}/manifest.json", json_encode($manifest, JSON_PRETTY_PRINT));

        $this->command->info("Backup complete: {$backupDir}");
    }

    /**
     * Import data from the latest backup.
     */
    protected function importBackup(): void
    {
        // Find latest backup directory
        if (!File::isDirectory($this->backupPath)) {
            $this->command->error("No backups found at: {$this->backupPath}");
            return;
        }

        $directories = File::directories($this->backupPath);
        if (empty($directories)) {
            $this->command->error("No backup directories found.");
            return;
        }

        // Sort by name (timestamp) and get latest
        sort($directories);
        $latestBackup = end($directories);

        $this->command->info("Importing from: {$latestBackup}");

        // Disable foreign key checks for Oracle
        // Note: Oracle handles this differently, we'll rely on proper table order
        
        foreach ($this->tables as $table) {
            $jsonFile = "{$latestBackup}/{$table}.json";
            
            if (!File::exists($jsonFile)) {
                $this->command->warn("  Skipping {$table}: backup file not found.");
                continue;
            }

            $this->command->info("  Importing {$table}...");
            
            try {
                $data = json_decode(File::get($jsonFile), true);
                
                if (empty($data)) {
                    $this->command->info("    -> No data to import.");
                    continue;
                }

                // Clear existing data (optional, can be risky)
                // DB::table($table)->truncate();
                
                // Insert in chunks to avoid memory issues
                $chunks = array_chunk($data, 100);
                $totalInserted = 0;
                
                foreach ($chunks as $chunk) {
                    // Use insertOrIgnore to avoid duplicate key errors
                    DB::table($table)->insertOrIgnore($chunk);
                    $totalInserted += count($chunk);
                }
                
                $this->command->info("    -> {$totalInserted} records imported.");
            } catch (\Exception $e) {
                $this->command->error("    -> Failed: {$e->getMessage()}");
            }
        }

        $this->command->info("Import complete.");
    }
}
