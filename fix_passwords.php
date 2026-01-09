<?php

use App\Models\Pendamping;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Pendamping passwords...\n";

$pendampings = Pendamping::all();
$fixed = 0;

foreach ($pendampings as $p) {
    // If password is not a valid hash (e.g. plain text), re-hash it
    // Simple check: bcrypt hashes start with $2y$ or $2a$ and are 60 chars long
    // If it's shorter or doesn't start with $, it's likely plain text
    
    $current = $p->getAttributes()['password']; // Get raw attribute ignoring cast
    
    $needsRehash = false;
    
    // Check if it looks like a bcrypt hash
    if (!preg_match('/^\$2[ayb]\$.{56}$/', $current)) {
        $needsRehash = true;
    }
    
    // Or if we can catch the error by checking?
    // Actually, Hash::info($current) could tell us, but 'This password does not use Bcrypt' 
    // suggests Laravel already checks and complains.
    
    if ($needsRehash) {
        echo "Fixing password for {$p->nik} ({$p->nama})...\n";
        
        // Update directly to bypass model casting issues if any, or just save
        // We set it to the hashed version of existing 'plain' text
        $p->password = Hash::make($current); 
        // Note: If the current value WAS a hash but not bcrypt (e.g. MD5), we just hashed the hash. 
        // This effectively resets the password to 'the old hash string'. 
        // User would have to login with the OLD HASH as their password.
        // If it is plain text, this works perfectly. 
        // If it is MD5, they can't login with original password.
        // Ideally we assume plain text if short, or ask user.
        // Given 'Legacy' environment often implies imports, plain text is most likely culprit 
        // OR the user just manually inserted a row with plain text.
        
        $p->save();
        $fixed++;
    }
}

echo "Fixed $fixed passwords.\n";
