<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Attempt to list tables. Adjust for Oracle if needed.
    // For Oracle, it's usually uppercase.
    $tables = DB::select('select table_name from user_tables'); 
    // If that fails or returns empty, try standard Laravel/MySQL way just in case
    if (empty($tables)) {
         $tables = DB::select('SHOW TABLES'); 
    }
    
    foreach ($tables as $table) {
        // Normalize table name property access
        $tableName = $table->table_name ?? $table->TABLE_NAME ?? array_values((array)$table)[0];
        echo $tableName . PHP_EOL;
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
