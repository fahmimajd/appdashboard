<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petugas_kecamatan', function (Blueprint $table) {
            $table->char('nik', 16)->primary();
            $table->string('nama', 100);
            $table->string('nomor_ponsel', 20)->nullable();
            $table->char('jenis_kelamin', 1)->check("jenis_kelamin IN ('L','P','-')")->nullable();
            $table->string('status_aktif', 10)->default('Aktif');
            $table->date('tanggal_mulai_akses')->nullable();
            $table->string('bcard', 50)->nullable();
            $table->string('benroller', 50)->nullable();
            $table->char('kode_kecamatan', 6);

            $table->foreign('kode_kecamatan')
                  ->references('kode_kecamatan')
                  ->on('wilayah_kecamatan')
                  ->name('fk_petugas_kecamatan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petugas_kecamatan');
    }
};
