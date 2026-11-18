<?php

namespace Tests\Feature;

use App\Events\PedidoCambio;
use App\Models\Mesa;
use App\Models\Producto;
use App\Models\Role;
use App\Models\User;
use App\Services\PedidoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PedidoTiempoRealTest extends TestCase
{
    use RefreshDatabase;

    private function rol(string $slug, string $name): Role
    {
        return Role::create(['name' => $name, 'slug' => $slug]);
    }

    public function test_crear_pedido_emite_evento_y_notifica_al_resto_del_staff(): void
    {
        $rolStaff = $this->rol('staff', 'Staff');
        $rolAdmin = $this->rol('admin', 'Admin');

        $actor = User::factory()->create(['role_id' => $rolStaff->id]);
        $companero = User::factory()->create(['role_id' => $rolStaff->id]);
        $admin = User::factory()->create(['role_id' => $rolAdmin->id]);

        $mesa = Mesa::create(['numero' => '1']);
        $producto = Producto::create(['nombre' => 'Cerveza', 'precio' => 50, 'disponible' => true]);

        Event::fake([PedidoCambio::class]);

        $pedido = app(PedidoService::class)->crear([
            'mesa_id' => $mesa->id,
            'productos' => [['id' => $producto->id, 'cantidad' => 2]],
        ], $actor->id);

        Event::assertDispatched(PedidoCambio::class, function (PedidoCambio $event) use ($mesa, $actor) {
            return $event->accion === 'creado'
                && $event->actorId === $actor->id
                && $event->pedido->mesa_id === $mesa->id;
        });

        // El actor no se auto-notifica; el resto del personal sí recibe el aviso.
        $this->assertSame(0, $actor->notifications()->count());
        $this->assertSame(1, $companero->fresh()->notifications()->count());
        $this->assertSame(1, $admin->fresh()->notifications()->count());
        $this->assertSame(100, $pedido->fresh()->total);
    }

    public function test_fragmento_y_vista_devuelven_el_listado(): void
    {
        $superAdmin = User::factory()->create([
            'role_id' => $this->rol('super_admin', 'Super Admin')->id,
        ]);

        $mesa = Mesa::create(['numero' => '5']);
        $producto = Producto::create(['nombre' => 'Cerveza', 'precio' => 50, 'disponible' => true]);

        Event::fake([PedidoCambio::class]);

        app(PedidoService::class)->crear([
            'mesa_id' => $mesa->id,
            'productos' => [['id' => $producto->id, 'cantidad' => 1]],
        ], $superAdmin->id);

        $this->actingAs($superAdmin)
            ->get(route('pedidos.index'))
            ->assertOk()
            ->assertSee('pedidos-contenido');

        $respuesta = $this->actingAs($superAdmin)
            ->getJson(route('pedidos.fragmento'))
            ->assertOk()
            ->assertJsonStructure(['html', 'grupos', 'activosCount', 'historialCount'])
            ->assertJsonPath('activosCount', 1)
            ->assertJsonPath('historialCount', 0);

        $this->assertStringContainsString('Mesa 5', $respuesta->json('html'));
        $this->assertCount(1, $respuesta->json('grupos'));
    }
}
