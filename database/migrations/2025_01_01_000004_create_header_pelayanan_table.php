<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('header_pelayanan', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('nomor_pelayanan', 50)->nullable();
            $table->string('nomor_pengaduan', 50)->nullable();
            $table->timestamp('tanggal_dibuat')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('header_pelayanan');
    }
};
