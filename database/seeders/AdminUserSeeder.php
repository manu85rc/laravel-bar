<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('slug', 'super_admin')->first();
        $userRole = Role::where('slug', 'user')->first();

        User::where('email', 'admin@example.com')->update([
            'email' => 'admin@admin.com',
            'name' => 'Super Administrador',
            'password' => Hash::make('password'),
            'role_id' => $superAdminRole?->id,
        ]);

        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('password'),
                'role_id' => $superAdminRole?->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Usuario Regular',
                'password' => Hash::make('user123'),
                'role_id' => $userRole?->id,
            ]
        );
    }
}
