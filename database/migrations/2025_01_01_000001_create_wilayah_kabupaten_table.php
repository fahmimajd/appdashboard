<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilayah_kabupaten', function (Blueprint $table) {
            $table->char('kode_kabupaten', 4)->primary();
            $table->string('nama_kabupaten', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah_kabupaten');
    }
};
