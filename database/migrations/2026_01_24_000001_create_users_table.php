<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->char('nik', 16)->unique();
            $table->string('nama', 100);
            $table->string('password', 255);
            $table->string('akses', 15)->default('Pendamping');
            $table->string('status_aktif', 10)->default('Aktif');
            $table->char('kode_desa', 10)->nullable();
            $table->char('kode_kecamatan', 8)->nullable();
            $table->rememberToken();
            $table->timestamps();

            // Index for login lookup
            $table->index('nik');
            $table->index('kode_desa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
