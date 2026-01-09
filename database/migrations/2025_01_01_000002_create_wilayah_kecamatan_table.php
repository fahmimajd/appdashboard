<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilayah_kecamatan', function (Blueprint $table) {
            $table->char('kode_kecamatan', 6)->primary();
            $table->char('kode_kabupaten', 4);
            $table->string('nama_kecamatan', 100);

            $table->foreign('kode_kabupaten')
                  ->references('kode_kabupaten')
                  ->on('wilayah_kabupaten')
                  ->name('fk_kecamatan_kabupaten');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah_kecamatan');
    }
};
