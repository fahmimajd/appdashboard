<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$constraints = DB::select("SELECT constraint_name, constraint_type 
                           FROM user_constraints 
                           WHERE table_name = 'PENDAMPING'");

foreach ($constraints as $c) {
    echo "Constraint: {$c->constraint_name} - Type: {$c->constraint_type}\n";
}
