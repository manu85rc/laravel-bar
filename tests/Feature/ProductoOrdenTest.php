<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Permission;
use App\Models\Producto;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoOrdenTest extends TestCase
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

    public function test_reordenar_asigna_orden_dentro_de_cada_grupo(): void
    {
        $admin = $this->menuAdmin();

        $cat = Categoria::create(['name' => 'Café']);
        $a = Producto::create(['nombre' => 'A', 'precio' => 1, 'categoria_id' => $cat->id, 'orden' => 0]);
        $b = Producto::create(['nombre' => 'B', 'precio' => 1, 'categoria_id' => $cat->id, 'orden' => 1]);
        $sin = Producto::create(['nombre' => 'Sin', 'precio' => 1, 'orden' => 0]);

        $this->actingAs($admin)
            ->postJson(route('admin.productos.reordenar'), [
                'grupos' => [
                    ['grupo' => 'cat-'.$cat->id, 'categoria_id' => $cat->id, 'productos' => [$b->id, $a->id]],
                    ['grupo' => 'sin', 'categoria_id' => null, 'productos' => [$sin->id]],
                ],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame(0, Producto::find($b->id)->orden);
        $this->assertSame(1, Producto::find($a->id)->orden);
        $this->assertSame(0, Producto::find($sin->id)->orden);
    }

    public function test_reordenar_mueve_producto_entre_categorias_en_posicion_exacta(): void
    {
        $admin = $this->menuAdmin();

        $origen = Categoria::create(['name' => 'Café']);
        $dest = Categoria::create(['name' => 'Comida']);
        $a = Producto::create(['nombre' => 'A', 'precio' => 1, 'categoria_id' => $dest->id, 'orden' => 0]);
        $b = Producto::create(['nombre' => 'B', 'precio' => 1, 'categoria_id' => $dest->id, 'orden' => 1]);
        $m = Producto::create(['nombre' => 'M', 'precio' => 1, 'categoria_id' => $origen->id, 'orden' => 0]);

        $this->actingAs($admin)
            ->postJson(route('admin.productos.reordenar'), [
                'grupos' => [
                    ['grupo' => 'cat-'.$origen->id, 'categoria_id' => $origen->id, 'productos' => []],
                    ['grupo' => 'cat-'.$dest->id, 'categoria_id' => $dest->id, 'productos' => [$a->id, $m->id, $b->id]],
                ],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $m->refresh();
        $this->assertSame((int) $dest->id, (int) $m->categoria_id);
        $this->assertSame(1, $m->orden);
        $this->assertSame(0, Producto::find($a->id)->orden);
        $this->assertSame(2, Producto::find($b->id)->orden);
    }

    public function test_reordenar_valida_productos_inexistentes(): void
    {
        $admin = $this->menuAdmin();

        $this->actingAs($admin)
            ->postJson(route('admin.productos.reordenar'), [
                'grupos' => [
                    ['grupo' => 'sin', 'categoria_id' => null, 'productos' => [999999]],
                ],
            ])
            ->assertStatus(422);
    }

    public function test_cambiar_categoria_mueve_y_asigna_orden_al_final(): void
    {
        $admin = $this->menuAdmin();

        $cat = Categoria::create(['name' => 'Café']);
        $dest = Categoria::create(['name' => 'Comida']);
        Producto::create(['nombre' => 'D', 'precio' => 1, 'categoria_id' => $dest->id, 'orden' => 3]);
        $p = Producto::create(['nombre' => 'M', 'precio' => 1, 'categoria_id' => $cat->id, 'orden' => 0]);

        $this->actingAs($admin)
            ->postJson(route('admin.productos.cambiar-categoria', $p), [
                'categoria_id' => $dest->id,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $p->refresh();
        $this->assertSame((int) $dest->id, (int) $p->categoria_id);
        $this->assertSame(4, $p->orden);
    }

    public function test_cambiar_categoria_a_sin_categoria(): void
    {
        $admin = $this->menuAdmin();

        $cat = Categoria::create(['name' => 'Café']);
        Producto::create(['nombre' => 'X', 'precio' => 1, 'orden' => 2]);
        $p = Producto::create(['nombre' => 'M', 'precio' => 1, 'categoria_id' => $cat->id, 'orden' => 0]);

        $this->actingAs($admin)
            ->postJson(route('admin.productos.cambiar-categoria', $p), [
                'categoria_id' => null,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $p->refresh();
        $this->assertNull($p->categoria_id);
        $this->assertSame(3, $p->orden);
    }

    public function test_store_asigna_orden_automatico_al_crear(): void
    {
        $admin = $this->menuAdmin();

        $cat = Categoria::create(['name' => 'Café']);
        Producto::create(['nombre' => 'Existe', 'precio' => 5, 'categoria_id' => $cat->id, 'orden' => 7]);

        $this->actingAs($admin)
            ->post(route('admin.productos.store'), [
                'nombre' => 'Nuevo',
                'precio' => 3,
                'categoria_id' => $cat->id,
            ])
            ->assertRedirect(route('admin.menu.index'));

        $nuevo = Producto::where('nombre', 'Nuevo')->first();
        $this->assertSame(8, $nuevo->orden);
    }

    public function test_menu_index_muestra_productos_por_orden(): void
    {
        $admin = $this->menuAdmin();

        $cat = Categoria::create(['name' => 'Café']);
        Producto::create(['nombre' => 'B', 'precio' => 1, 'categoria_id' => $cat->id, 'orden' => 1]);
        Producto::create(['nombre' => 'A', 'precio' => 1, 'categoria_id' => $cat->id, 'orden' => 0]);

        $this->actingAs($admin)
            ->get(route('admin.menu.index'))
            ->assertOk()
            ->assertSeeInOrder(['A', 'B']);
    }

    public function test_rutas_requieren_permiso_admin_menu(): void
    {
        $role = Role::create(['name' => 'Usuario', 'slug' => 'user']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $p = Producto::create(['nombre' => 'M', 'precio' => 1]);

        $this->actingAs($user)
            ->postJson(route('admin.productos.reordenar'), ['grupos' => [[$p->id]]])
            ->assertRedirect('/');
        $this->actingAs($user)
            ->postJson(route('admin.productos.cambiar-categoria', $p), ['categoria_id' => null])
            ->assertRedirect('/');
    }
}
