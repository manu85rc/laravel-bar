<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permiso = Permission::updateOrCreate(
            ['slug' => 'admin.mercadopago'],
            ['name' => 'Ver Mercado Pago']
        );

        $rolAdmin = Role::where('slug', 'admin')->first();
        if ($rolAdmin && ! $rolAdmin->permissions()->where('slug', 'admin.mercadopago')->exists()) {
            $rolAdmin->permissions()->attach($permiso);
        }
    }

    public function down(): void
    {
        Permission::where('slug', 'admin.mercadopago')->delete();
    }
};
