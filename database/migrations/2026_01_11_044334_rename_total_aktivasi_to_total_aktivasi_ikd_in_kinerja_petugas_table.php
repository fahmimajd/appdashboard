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
        Schema::table('kinerja_petugas', function (Blueprint $table) {
            $table->renameColumn('total_aktivasi', 'total_aktivasi_ikd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kinerja_petugas', function (Blueprint $table) {
             $table->renameColumn('total_aktivasi_ikd', 'total_aktivasi');
        });
    }
};
