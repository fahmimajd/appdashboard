<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sarpras_desa', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->char('kode_desa', 10);
            $table->integer('komputer')->default(0);
            $table->integer('printer')->default(0);
            $table->integer('internet')->default(0);
            $table->string('ruang_pelayanan', 10)->default('Tidak')
                  ->check("ruang_pelayanan IN ('Ada','Tidak')");
            $table->string('provider', 100)->nullable();

            $table->foreign('kode_desa')
                  ->references('kode_desa')
                  ->on('wilayah_desa')
                  ->onDelete('cascade')
                  ->name('fk_sarpras_desa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sarpras_desa');
    }
};
