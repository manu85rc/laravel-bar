<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Migrate old admin if exists (from previous seeder)
        User::where('email', 'admin@example.com')->update([
            'email' => 'admin@admin.com',
            'name' => 'Super Administrador',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        // Create or update super admin
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SUPER_ADMIN,
            ]
        );

        // Create a regular user if not exists
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Usuario Regular',
                'password' => Hash::make('user123'),
                'role' => User::ROLE_USER,
            ]
        );
    }
}
