<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilayah_desa', function (Blueprint $table) {
            $table->char('kode_desa', 10)->primary();
            $table->char('kode_kecamatan', 6);
            $table->string('nama_desa', 100);
            $table->string('nama_kepala_desa', 100)->nullable();
            $table->string('titik_koordinat', 50)->nullable();
            $table->string('kontur_wilayah', 50)->nullable();
            $table->decimal('luas_wilayah', 10, 2)->nullable();
            $table->decimal('jarak_disdukcapil', 10, 2)->nullable();
            $table->integer('jumlah_rt')->nullable();
            $table->integer('jumlah_rw')->nullable();
            $table->integer('jumlah_dusun')->nullable();

            $table->foreign('kode_kecamatan')
                  ->references('kode_kecamatan')
                  ->on('wilayah_kecamatan')
                  ->name('fk_desa_kecamatan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah_desa');
    }
};
