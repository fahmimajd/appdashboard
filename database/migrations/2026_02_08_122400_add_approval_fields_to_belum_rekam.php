<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('belum_rekam', function (Blueprint $table) {
            // Proposed values for approval workflow
            $table->string('keterangan_proposed', 255)->nullable();

            // Metadata for pending approval
            $table->boolean('has_pending_approval')->default(false);
            $table->timestamp('last_proposed_at')->nullable();
            $table->char('last_proposed_by', 16)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('belum_rekam', function (Blueprint $table) {
            $table->dropColumn([
                'keterangan_proposed',
                'has_pending_approval',
                'last_proposed_at',
                'last_proposed_by',
            ]);
        });
    }
};
