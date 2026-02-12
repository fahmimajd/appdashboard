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
        // 1. Master Barang
        Schema::create('master_barang', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama', 100);
            $table->string('satuan', 20);
            $table->string('kategori', 30); // blangko, logistik, lainnya
            $table->boolean('auto_kurang')->default(false);
            $table->string('field_kinerja', 50)->nullable(); // field pengurangan otomatis
            $table->string('field_stok_laporan', 50)->nullable(); // field rekonsiliasi
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 2. Stok Barang
        Schema::create('stok_barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('master_barang')->cascadeOnDelete();
            $table->string('lokasi_tipe', 20); // dinas, kecamatan
            $table->char('kode_kecamatan', 6)->nullable()->index(); // null jika dinas
            $table->integer('jumlah')->default(0);
            $table->integer('stok_minimum')->default(0);
            $table->timestamps();

            // Unique constraint: satu barang satu stok per lokasi
            $table->unique(['barang_id', 'lokasi_tipe', 'kode_kecamatan'], 'unq_stok_brg_lok');
        });

        // 3. Mutasi Barang (Log)
        Schema::create('mutasi_barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('master_barang')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('tipe_mutasi', 20); // masuk, distribusi, pemakaian, penyesuaian
            $table->integer('jumlah'); // positif atau negatif
            
            // Asal
            $table->string('lokasi_asal_tipe', 20)->nullable();
            $table->char('lokasi_asal_kecamatan', 6)->nullable();
            
            // Tujuan
            $table->string('lokasi_tujuan_tipe', 20)->nullable();
            $table->char('lokasi_tujuan_kecamatan', 6)->nullable();
            
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('referensi_id')->nullable(); // ID kinerja jika pemakaian otomatis
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_barang');
        Schema::dropIfExists('stok_barang');
        Schema::dropIfExists('master_barang');
    }
};
