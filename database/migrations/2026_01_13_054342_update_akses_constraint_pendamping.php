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
        // 1. Find existing check constraint on 'akses'
        // Condition usually contains "akses": 'akses IN ...' or '"akses" IS NOT NULL'
        // We know from check_constraints_detail.php that SYS_C0011380 has "akses IN ('Admin','Operator')"
        // But local dev might differ. Let's find by search_condition content.
        
        // Fetch attributes and select relevant check constraints
        $constraints = DB::select("SELECT constraint_name, search_condition 
                                   FROM user_constraints 
                                   WHERE table_name = 'PENDAMPING' 
                                   AND constraint_type = 'C'");

        foreach ($constraints as $c) {
             $condition = $c->search_condition;
             // Handle stream resource (Oracle LONG column)
             if (is_resource($condition)) {
                 $condition = stream_get_contents($condition);
             }
             
             // Check if condition relates to 'akses' and 'IN'
             // Note: search_condition might be case sensitive or contain quotes
             if (stripos($condition, 'akses') !== false && stripos($condition, 'IN') !== false) {
                 DB::statement("ALTER TABLE PENDAMPING DROP CONSTRAINT {$c->constraint_name}");
             }
        }

        // 2. Update data
        DB::table('pendamping')->where('akses', 'Operator')->update(['akses' => 'Pendamping']);
        DB::table('pendamping')->where('akses', 'User')->update(['akses' => 'Desa']); // If User maps to Desa

        // 3. Add new check constraint
        // 'Admin', 'Pendamping', 'Desa', 'Supervisor'
        DB::statement("ALTER TABLE PENDAMPING ADD CONSTRAINT pendamping_akses_check CHECK (akses IN ('Admin', 'Pendamping', 'Desa', 'Supervisor'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new constraint
        DB::statement("ALTER TABLE PENDAMPING DROP CONSTRAINT pendamping_akses_check");
        
        // Revert data? Hard to know which was Operator.
        // Re-add old constraint?
    }
};
