<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('belum_akte_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->char('belum_akte_nik', 16);           // NIK of the belum_akte record
            $table->string('field_name', 50);             // 'keterangan', 'no_akta_kelahiran'
            $table->string('old_value', 255)->nullable();
            $table->string('proposed_value', 255);
            $table->string('final_value', 255)->nullable(); // null if rejected
            $table->enum('action', ['approved', 'rejected']);
            $table->char('proposed_by', 16);              // NIK petugas who proposed
            $table->char('action_by', 16);                // NIK admin/pendamping who approved/rejected
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['belum_akte_nik', 'created_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('belum_akte_approval_logs');
    }
};
