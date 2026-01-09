<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kinerja_petugas', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->char('nik_petugas', 16);
            $table->char('kode_desa', 10);
            $table->integer('tahun');
            $table->integer('bulan');
            $table->integer('aktivasi_ikd')->default(0);
            $table->integer('ikd_desa')->default(0);
            $table->integer('akta_kelahiran')->default(0);
            $table->integer('akta_kematian')->default(0);
            $table->integer('pengajuan_kk')->default(0);
            $table->integer('pengajuan_pindah')->default(0);
            $table->integer('pengajuan_kia')->default(0);
            $table->integer('jumlah_login')->default(0);

            $table->foreign('nik_petugas')->references('nik')->on('petugas')->onDelete('cascade')->name('fk_kinerja_petugas');
            $table->foreign('kode_desa')->references('kode_desa')->on('wilayah_desa')->onDelete('cascade')->name('fk_kinerja_desa');
            
            $table->unique(['nik_petugas', 'kode_desa', 'tahun', 'bulan'], 'uq_kinerja');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kinerja_petugas');
    }
};
