<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$constraints = DB::select("SELECT constraint_name, search_condition 
                           FROM user_constraints 
                           WHERE table_name = 'PENDAMPING' AND constraint_type = 'C'");

foreach ($constraints as $c) {
    echo "Constraint: {$c->constraint_name}\nCondition: {$c->search_condition}\n---\n";
}
