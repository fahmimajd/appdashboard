<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('petugas')) {
            try {
                Schema::table('petugas', function (Blueprint $table) {
                    $table->index('kode_desa');
                    $table->index('kode_kecamatan');
                    $table->index('kode_kabupaten');
                    $table->index('nama');
                });
            } catch (\Exception $e) {
                // Ignore if index exists
            }
        }
        
        if (Schema::hasTable('petugas_kecamatan')) {
            try {
                Schema::table('petugas_kecamatan', function (Blueprint $table) {
                    $table->index('kode_kecamatan');
                    $table->index('nama');
                    // $table->index('nik');
                });
            } catch (\Exception $e) {
                // Ignore
            }
        }
        
        if (Schema::hasTable('petugas_dinas')) {
            try {
                Schema::table('petugas_dinas', function (Blueprint $table) {
                    $table->index('kode_kabupaten');
                    $table->index('nama');
                    // $table->index('nik');
                });
            } catch (\Exception $e) {
                // Ignore
            }
        }
        
        if (Schema::hasTable('kinerja_petugas')) {
            try {
                Schema::table('kinerja_petugas', function (Blueprint $table) {
                    $table->index('nik_petugas');
                    $table->index('kode_desa');
                    $table->index(['tahun', 'bulan']);
                });
            } catch (\Exception $e) {
                // Ignore
            }
        }

        if (Schema::hasTable('kependudukan_semester')) {
            try {
                Schema::table('kependudukan_semester', function (Blueprint $table) {
                    $table->index('kode_desa');
                    $table->index('kode_semester');
                });
            } catch (\Exception $e) {
                // Ignore
            }
        }
        
        if (Schema::hasTable('sasaran')) {
            try {
                Schema::table('sasaran', function (Blueprint $table) {
                   $table->index('kode_desa');
                   $table->index('nama');
                });
            } catch (\Exception $e) {
                // Ignore
            }
        }
        
        if (Schema::hasTable('vpn_desa')) {
            try {
                Schema::table('vpn_desa', function (Blueprint $table) {
                   $table->index('kode_desa');
                   $table->index('username');
                });
            } catch (\Exception $e) {
                // Ignore
            }
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('petugas')) {
            Schema::table('petugas', function (Blueprint $table) {
                $table->dropIndex(['kode_desa']);
                $table->dropIndex(['kode_kecamatan']);
                $table->dropIndex(['kode_kabupaten']);
                $table->dropIndex(['nama']);
            });
        }
        
        if (Schema::hasTable('petugas_kecamatan')) {
            Schema::table('petugas_kecamatan', function (Blueprint $table) {
                $table->dropIndex(['kode_kecamatan']);
                $table->dropIndex(['nama']);
            });
        }
        
        if (Schema::hasTable('petugas_dinas')) {
             Schema::table('petugas_dinas', function (Blueprint $table) {
                $table->dropIndex(['kode_kabupaten']);
                $table->dropIndex(['nama']);
            });
        }

        if (Schema::hasTable('kinerja_petugas')) {
             Schema::table('kinerja_petugas', function (Blueprint $table) {
                $table->dropIndex(['nik_petugas']);
                $table->dropIndex(['kode_desa']);
                $table->dropIndex(['tahun', 'bulan']);
            });
        }

        if (Schema::hasTable('kependudukan_semester')) {
             Schema::table('kependudukan_semester', function (Blueprint $table) {
                $table->dropIndex(['kode_desa']);
                $table->dropIndex(['kode_semester']);
            });
        }

        if (Schema::hasTable('sasaran')) {
            Schema::table('sasaran', function (Blueprint $table) {
               $table->dropIndex(['kode_desa']);
               $table->dropIndex(['nama']);
            });
        }

        if (Schema::hasTable('vpn_desa')) {
             Schema::table('vpn_desa', function (Blueprint $table) {
               $table->dropIndex(['kode_desa']);
               $table->dropIndex(['username']);
            });
        }
    }
};
