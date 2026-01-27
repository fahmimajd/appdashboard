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
        Schema::table('pendamping', function (Blueprint $table) {
            $table->index('kode_desa');
            $table->index('nama');
            $table->index('nik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendamping', function (Blueprint $table) {
            $table->dropIndex(['kode_desa']);
            $table->dropIndex(['nama']);
            $table->dropIndex(['nik']);
        });
    }
};
