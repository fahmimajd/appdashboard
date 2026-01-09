<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vpn_desa', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->char('kode_desa', 10)->unique();
            $table->string('username', 150);
            $table->string('password', 255);
            $table->string('jenis_vpn', 20)->nullable()
                  ->check("jenis_vpn IN ('PPTP','L2TP','OpenVPN','WireGuard')");

            $table->foreign('kode_desa')
                  ->references('kode_desa')
                  ->on('wilayah_desa')
                  ->onDelete('cascade')
                  ->name('fk_vpn_desa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vpn_desa');
    }
};
