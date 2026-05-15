<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::create([
    'name' => 'Admin2',
    'email' => 'admin2@example.com',
    'password' => Hash::make('password'),
    'role' => 'admin',
]);

echo 'Created admin user with ID: '.$user->id.PHP_EOL;
