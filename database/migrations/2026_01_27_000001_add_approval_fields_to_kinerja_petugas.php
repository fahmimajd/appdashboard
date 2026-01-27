<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kinerja_petugas', function (Blueprint $table) {
            // Proposed values for each field (pending approval)
            $table->integer('aktivasi_ikd_proposed')->nullable();
            $table->integer('ikd_desa_proposed')->nullable();
            $table->integer('akta_kelahiran_proposed')->nullable();
            $table->integer('akta_kematian_proposed')->nullable();
            $table->integer('pengajuan_kk_proposed')->nullable();
            $table->integer('pengajuan_pindah_proposed')->nullable();
            $table->integer('pengajuan_kia_proposed')->nullable();
            $table->integer('jumlah_login_proposed')->nullable();
            $table->integer('total_aktivasi_ikd_proposed')->nullable();

            // Metadata for pending approval
            $table->boolean('has_pending_approval')->default(false);
            $table->timestamp('last_proposed_at')->nullable();
            $table->char('last_proposed_by', 16)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('kinerja_petugas', function (Blueprint $table) {
            $table->dropColumn([
                'aktivasi_ikd_proposed',
                'ikd_desa_proposed',
                'akta_kelahiran_proposed',
                'akta_kematian_proposed',
                'pengajuan_kk_proposed',
                'pengajuan_pindah_proposed',
                'pengajuan_kia_proposed',
                'jumlah_login_proposed',
                'total_aktivasi_ikd_proposed',
                'has_pending_approval',
                'last_proposed_at',
                'last_proposed_by',
            ]);
        });
    }
};
