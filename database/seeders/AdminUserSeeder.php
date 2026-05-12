<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if not exists
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'name' => 'Administrador',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'), // change in production
                'role' => User::ROLE_ADMIN,
            ]);
        }

        // Create a regular user if not exists
        if (!User::where('email', 'user@example.com')->exists()) {
            User::create([
                'name' => 'Usuario Regular',
                'email' => 'user@example.com',
                'password' => Hash::make('user123'), // change in production
                'role' => User::ROLE_USER,
            ]);
        }
    }
}
