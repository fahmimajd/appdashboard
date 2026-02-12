<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('kinerja_kecamatan');
        Schema::create('kinerja_kecamatan', function (Blueprint $table) {
            $table->id();
            $table->char('kode_kecamatan', 6)->index()->nullable();
            $table->char('petugas_id', 16)->nullable(); // NIK of the petugas
            $table->date('tanggal');
            
            // Stats
            $table->integer('rekam_ktp_el')->default(0);
            $table->integer('cetak_ktp_el')->default(0);
            $table->integer('kartu_keluarga')->default(0);
            $table->integer('kia')->default(0);
            $table->integer('pindah')->default(0);
            $table->integer('kedatangan')->default(0);
            $table->integer('akta_kelahiran')->default(0);
            $table->integer('akta_kematian')->default(0);
            $table->integer('stok_blangko_ktp')->default(0);
            $table->integer('stok_blangko_kia')->default(0);
            $table->decimal('persentase_ribbon', 5, 2)->default(0);
            $table->decimal('persentase_film', 5, 2)->default(0);
            $table->integer('ikd_hari_ini')->default(0);

            $table->timestamps();

            // Foreign keys with explicit short names for Oracle compatibility
            $table->foreign('kode_kecamatan', 'fk_kk_wil_kec')->references('kode_kecamatan')->on('wilayah_kecamatan')->nullOnDelete();
            $table->foreign('petugas_id', 'fk_kk_pet_kec')->references('nik')->on('petugas_kecamatan')->nullOnDelete();

            // Composite unique index with short name
            $table->unique(['petugas_id', 'tanggal'], 'unq_kk_pet_tgl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kinerja_kecamatan');
    }
};
