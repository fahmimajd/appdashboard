<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petugas', function (Blueprint $table) {
            $table->char('nik', 16)->primary();
            $table->string('nama', 100);
            $table->string('nomor_ponsel', 20)->nullable();
            $table->char('jenis_kelamin', 1)->check("jenis_kelamin IN ('L','P','-')")->nullable();
            $table->string('level_akses', 20)->nullable();
            $table->string('status_aktif', 10)->default('Aktif');
            $table->text('keterangan_nonaktif')->nullable(); // CLOB in Oracle -> text in Laravel
            $table->date('tanggal_mulai_akses')->nullable();
            
            $table->char('kode_desa', 10);
            $table->char('kode_kecamatan', 6);
            $table->char('kode_kabupaten', 4);

            $table->foreign('kode_desa')->references('kode_desa')->on('wilayah_desa')->name('fk_petugas_desa');
            $table->foreign('kode_kecamatan')->references('kode_kecamatan')->on('wilayah_kecamatan')->name('fk_petugas_kecamatan_desa');
            $table->foreign('kode_kabupaten')->references('kode_kabupaten')->on('wilayah_kabupaten')->name('fk_petugas_kabupaten_desa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petugas');
    }
};
