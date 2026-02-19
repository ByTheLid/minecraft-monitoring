<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Env;
use App\Core\Model;
use App\Models\User; // Assuming User model exists and extends Model

Env::load(__DIR__ . '/.env');

// We need to bypass auth check in User model if any. 
// Actually User model is simple.

echo "Testing Model::find...\n";
$user = User::find(1);
if ($user) {
    echo "User found: " . $user['username'] . "\n";
} else {
    echo "User 1 not found (this might be normal if DB is empty)\n";
}

echo "Testing Model::builder()...\n";
$count = User::builder()->count();
echo "Total users: $count\n";

// Test dynamic static call
echo "Testing User::where()...\n";
$users = User::where('id', '>', 0)->limit(5)->get();
echo "Fetched " . count($users) . " users via magic method.\n";
