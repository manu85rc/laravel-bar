<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaOrdenTest extends TestCase
{
    use RefreshDatabase;

    private function menuAdmin(): User
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $role->permissions()->attach(
            Permission::create(['name' => 'Gestionar menú', 'slug' => 'admin.menu'])
        );

        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_reordenar_subcategorias_asigna_orden_dentro_de_su_padre(): void
    {
        $admin = $this->menuAdmin();

        $padre = Categoria::create(['name' => 'Padre', 'orden' => 0]);
        $a = Categoria::create(['name' => 'Hija A', 'parent_id' => $padre->id, 'orden' => 0]);
        $b = Categoria::create(['name' => 'Hija B', 'parent_id' => $padre->id, 'orden' => 1]);

        $this->actingAs($admin)
            ->postJson(route('admin.categorias.reordenar'), [
                'orden' => [$b->id, $a->id],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame(0, Categoria::find($b->id)->orden);
        $this->assertSame(1, Categoria::find($a->id)->orden);
    }

    public function test_reordenar_subcategorias_no_afecta_a_otras_categorias(): void
    {
        $admin = $this->menuAdmin();

        $padre = Categoria::create(['name' => 'Padre', 'orden' => 0]);
        $otroPadre = Categoria::create(['name' => 'Otro', 'orden' => 1]);
        $a = Categoria::create(['name' => 'Hija A', 'parent_id' => $padre->id, 'orden' => 0]);
        $b = Categoria::create(['name' => 'Hija B', 'parent_id' => $padre->id, 'orden' => 1]);

        $this->actingAs($admin)
            ->postJson(route('admin.categorias.reordenar'), [
                'orden' => [$b->id, $a->id],
            ])
            ->assertOk();

        $this->assertSame(0, Categoria::find($padre->id)->orden);
        $this->assertSame(1, Categoria::find($otroPadre->id)->orden);
    }

    public function test_menu_index_muestra_subcategorias_por_orden(): void
    {
        $admin = $this->menuAdmin();

        $padre = Categoria::create(['name' => 'Padre', 'orden' => 0]);
        Categoria::create(['name' => 'Hija B', 'parent_id' => $padre->id, 'orden' => 1]);
        Categoria::create(['name' => 'Hija A', 'parent_id' => $padre->id, 'orden' => 0]);

        $this->actingAs($admin)
            ->get(route('admin.menu.index'))
            ->assertOk()
            ->assertSee('subcategorias-sortable', false)
            ->assertSee('drag-handle-sub', false)
            ->assertSeeInOrder(['Hija A', 'Hija B']);
    }

    public function test_store_asigna_orden_automatico_entre_hermanas(): void
    {
        $admin = $this->menuAdmin();

        $padre = Categoria::create(['name' => 'Padre', 'orden' => 0]);
        Categoria::create(['name' => 'Hija existente', 'parent_id' => $padre->id, 'orden' => 7]);
        Categoria::create(['name' => 'Otra raíz', 'orden' => 20]);

        $this->actingAs($admin)
            ->post(route('admin.categorias.store'), [
                'name' => 'Hija nueva',
                'parent_id' => $padre->id,
            ])
            ->assertRedirect(route('admin.menu.index'));

        $nueva = Categoria::where('name', 'Hija nueva')->first();
        $this->assertSame(8, $nueva->orden);
    }
}
