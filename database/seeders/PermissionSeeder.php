<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Ver dashboard', 'slug' => 'admin.dashboard'],
            ['name' => 'Gestionar usuarios', 'slug' => 'admin.users'],
            ['name' => 'Gestionar mesas', 'slug' => 'admin.mesas'],
            ['name' => 'Gestionar menú', 'slug' => 'admin.menu'],
            ['name' => 'Gestionar pedidos', 'slug' => 'admin.pedidos'],
            ['name' => 'Gestionar reservas', 'slug' => 'admin.reservas'],
            ['name' => 'Configurar negocio', 'slug' => 'admin.branding'],
            ['name' => 'Configurar horarios', 'slug' => 'admin.reservation-settings'],
            ['name' => 'Gestionar impresoras', 'slug' => 'admin.printers'],
            ['name' => 'Ver Mercado Pago', 'slug' => 'admin.mercadopago'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['slug' => $perm['slug']], $perm);
        }

        $adminPerms = ['admin.dashboard', 'admin.users', 'admin.mesas', 'admin.menu', 'admin.pedidos', 'admin.reservas', 'admin.branding', 'admin.reservation-settings', 'admin.printers', 'admin.mercadopago'];
        $staffPerms = ['admin.dashboard', 'admin.pedidos'];

        $adminRole = Role::where('slug', 'admin')->first();
        $staffRole = Role::where('slug', 'staff')->first();

        if ($adminRole) {
            $adminRole->permissions()->sync(Permission::whereIn('slug', $adminPerms)->pluck('id'));
        }

        if ($staffRole) {
            $staffRole->permissions()->sync(Permission::whereIn('slug', $staffPerms)->pluck('id'));
        }
    }
}
