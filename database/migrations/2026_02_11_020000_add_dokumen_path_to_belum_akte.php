<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('belum_akte', function (Blueprint $table) {
            $table->string('dokumen_path', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('belum_akte', function (Blueprint $table) {
            $table->dropColumn('dokumen_path');
        });
    }
};
