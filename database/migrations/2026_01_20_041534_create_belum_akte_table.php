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
        Schema::create('belum_akte', function (Blueprint $table) {
            $table->char('nik', 16)->nullable();
            $table->string('nama_lgkp', 100)->nullable();
            $table->date('tgl_lhr')->nullable();
            $table->char('jenis_klmin', 1)->nullable();
            $table->char('kode_kecamatan', 20)->nullable();
            $table->char('kode_desa', 20)->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('belum_akte');
    }
};
