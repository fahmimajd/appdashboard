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
        Schema::create('sasaran', function (Blueprint $table) {
            $table->id();
            $table->char('kode_kecamatan', 7)->nullable();
            $table->char('kode_desa', 10)->nullable();
            $table->char('nik', 16)->nullable();
            $table->string('nama')->nullable();
            $table->string('status')->index(); // 'Belum Rekam', 'Belum Akte'
            $table->timestamps();

            $table->index('kode_desa');
            $table->index('kode_kecamatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sasaran');
    }
};
