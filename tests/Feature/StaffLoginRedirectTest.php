<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffLoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function role(string $slug, array $permissions = []): Role
    {
        $role = Role::create(['name' => ucfirst($slug), 'slug' => $slug]);

        $ids = collect($permissions)->map(function (string $slug) {
            return Permission::firstOrCreate(
                ['slug' => $slug],
                ['name' => $slug],
            )->id;
        });

        $role->permissions()->attach($ids);

        return $role;
    }

    public function test_staff_sin_dashboard_es_redirigido_a_pedidos_al_iniciar_sesion(): void
    {
        $role = $this->role('staff', ['admin.pedidos', 'admin.reservas']);
        $staff = User::factory()->create(['role_id' => $role->id]);

        $this->post(route('login'), [
            'email' => $staff->email,
            'password' => 'password',
        ])->assertRedirect(route('pedidos.index'));

        $this->assertAuthenticatedAs($staff);
    }

    public function test_staff_sin_dashboard_no_entra_en_bucle_al_pedir_dashboard(): void
    {
        $role = $this->role('staff', ['admin.pedidos']);
        $staff = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($staff)
            ->get('/dashboard')
            ->assertRedirect(route('pedidos.index'));
    }

    public function test_staff_sin_ningun_permiso_de_panel_va_a_la_home(): void
    {
        $role = $this->role('staff');
        $staff = User::factory()->create(['role_id' => $role->id]);

        $this->post(route('login'), [
            'email' => $staff->email,
            'password' => 'password',
        ])->assertRedirect(route('home'));
    }

    public function test_admin_con_dashboard_sigue_yendo_al_dashboard(): void
    {
        $role = $this->role('admin', ['admin.dashboard', 'admin.pedidos']);
        $admin = User::factory()->create(['role_id' => $role->id]);

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_un_usuario_normal_va_a_la_home(): void
    {
        $role = $this->role('user');
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('home'));
    }
}
