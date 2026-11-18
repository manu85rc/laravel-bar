<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super_admin', 'color' => '#e8b85c'],
            ['name' => 'Admin', 'slug' => 'admin', 'color' => '#63a4d8'],
            ['name' => 'Staff', 'slug' => 'staff', 'color' => '#4caf7d'],
            ['name' => 'Usuario', 'slug' => 'user', 'color' => '#9ca3af'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
