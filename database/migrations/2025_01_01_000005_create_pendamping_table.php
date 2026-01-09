<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendamping', function (Blueprint $table) {
            $table->char('nik', 16)->primary();
            $table->char('kode_desa', 10)->unique();
            $table->string('nama', 100);
            $table->string('nomor_ponsel', 20)->nullable();
            $table->char('jenis_kelamin', 1)->check("jenis_kelamin IN ('L','P','-')")->nullable();
            // Using string for enum-like fields
            $table->string('status_aktif', 10)->default('Aktif'); 
            $table->string('password', 255);
            $table->string('akses', 10)->default('Operator');
            
            // Re-adding last_password_change even if not in db.md explicitly, 
            // to support existing app logic seen in Model.
            // However, strict adherence requested. I will add it but nullable.
            // Actually, wait, db.md is the source of truth for the UPDATE.
            // If I add it, I deviate. But if I don't, existing code might break.
            // I will stick to db.md strictly as requested "perbaiki yang berubah".
            // If code breaks, I fix code.
            
            $table->foreign('kode_desa')
                  ->references('kode_desa')
                  ->on('wilayah_desa')
                  ->onDelete('cascade')
                  ->name('fk_pendamping_desa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendamping');
    }
};
