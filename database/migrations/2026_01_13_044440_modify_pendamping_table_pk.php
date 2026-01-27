<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find system generated constraint names
        $pk = DB::selectOne("SELECT constraint_name FROM user_constraints WHERE table_name = 'PENDAMPING' AND constraint_type = 'P'");
        $uk = DB::selectOne("SELECT constraint_name FROM user_constraints WHERE table_name = 'PENDAMPING' AND constraint_type = 'U'");

        Schema::table('pendamping', function (Blueprint $table) use ($pk, $uk) {
            if ($pk) {
                $table->dropPrimary($pk->constraint_name);
            }
            if ($uk) {
                $table->dropUnique($uk->constraint_name);
            }
        });

        Schema::table('pendamping', function (Blueprint $table) {
             // Add new auto-increment ID
             // Check if id exists first? Migration failed before adding ID, so likely not there.
             // But if partial run... user said nik still PK.
             $table->id()->first();
             
             // Add composite unique constraint
             $table->unique(['nik', 'kode_desa'], 'pendamping_nik_desa_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendamping', function (Blueprint $table) {
            $table->dropUnique('pendamping_nik_desa_unique');
            $table->dropColumn('id');
            // Revert unique
            $table->unique('kode_desa', 'pendamping_kode_desa_unique');
            // Revert PK (Caution: this fails if duplicates exist)
            $table->primary('nik');
        });
    }
};
