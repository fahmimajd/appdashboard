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
        Schema::create('BELUM_REKAM', function (Blueprint $table) {
            $table->char('NIK', 16)->nullable();
            $table->string('NAMA_LGKP', 100)->nullable();
            $table->char('JENIS_KLM', 1)->nullable();
            $table->string('TMPT_LHR', 100)->nullable();
            $table->date('TGL_LHR')->nullable();
            $table->string('WKTP_KET', 50)->nullable();
            $table->char('KODE_KECAMATAN', 20)->nullable();
            $table->char('KODE_DESA', 20)->nullable();
            $table->string('KETERANGAN', 100)->nullable();
            $table->string('KET_DISABILITAS', 100)->nullable();
            $table->string('CURRENT_STATUS_CODE', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('BELUM_REKAM');
    }
};
