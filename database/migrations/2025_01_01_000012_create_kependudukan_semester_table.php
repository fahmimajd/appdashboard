<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kependudukan_semester', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->char('kode_desa', 10);
            $table->char('kode_semester', 6);
            $table->integer('jumlah_penduduk')->nullable();
            $table->integer('jumlah_laki')->nullable();
            $table->integer('jumlah_perempuan')->nullable();
            $table->integer('wajib_ktp')->nullable();
            $table->integer('kartu_keluarga')->nullable();
            $table->integer('akta_kelahiran_jml')->nullable();
            $table->decimal('akta_kelahiran_persen', 5, 2)->nullable();
            $table->integer('akta_kematian_jml')->nullable();
            $table->decimal('akta_kematian_persen', 5, 2)->nullable();
            $table->integer('kepemilikan_ktp_jml')->nullable();
            $table->decimal('kepemilikan_ktp_persen', 5, 2)->nullable();
            $table->integer('kepemilikan_kia_jml')->nullable();
            $table->decimal('kepemilikan_kia_persen', 5, 2)->nullable();
            $table->integer('jumlah_kematian')->nullable();
            $table->integer('pindah_keluar')->nullable();
            $table->integer('status_kawin_jml')->nullable();
            $table->decimal('status_kawin_persen', 5, 2)->nullable();

            $table->foreign('kode_desa')
                  ->references('kode_desa')
                  ->on('wilayah_desa')
                  ->onDelete('cascade')
                  ->name('fk_kependudukan_desa');

            $table->unique(['kode_desa', 'kode_semester'], 'uq_kependudukan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kependudukan_semester');
    }
};
