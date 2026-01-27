<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kinerja_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('kinerja_id');
            $table->string('field_name', 50);           // 'aktivasi_ikd', 'akta_kelahiran', etc.
            $table->integer('old_value')->nullable();
            $table->integer('proposed_value');
            $table->integer('final_value')->nullable(); // null if rejected
            $table->enum('action', ['approved', 'rejected']);
            $table->char('proposed_by', 16);            // NIK petugas who proposed
            $table->char('action_by', 16);              // NIK pendamping who approved/rejected
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->foreign('kinerja_id')
                  ->references('id')
                  ->on('kinerja_petugas')
                  ->onDelete('cascade');
            
            $table->index(['kinerja_id', 'created_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kinerja_approval_logs');
    }
};
