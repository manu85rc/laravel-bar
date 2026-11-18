<?php

namespace Tests\Feature;

use App\Events\PedidoCambio;
use App\Models\Categoria;
use App\Models\Mesa;
use App\Models\OfflineSync;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Role;
use App\Models\User;
use App\Models\Zona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OfflineSyncTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        $rol = Role::create(['name' => 'Staff', 'slug' => 'staff']);

        return User::factory()->create(['role_id' => $rol->id]);
    }

    private function mesaConProducto(): array
    {
        $mesa = Mesa::create(['numero' => '7', 'disponible' => true]);
        $categoria = Categoria::create(['name' => 'Bebidas']);
        $producto = Producto::create([
            'nombre' => 'Cerveza',
            'precio' => 500,
            'categoria_id' => $categoria->id,
            'disponible' => true,
        ]);

        return [$mesa, $producto];
    }

    private function pedidoConLinea(Mesa $mesa, Producto $producto, User $usuario): Pedido
    {
        $pedido = Pedido::create([
            'mesa_id' => $mesa->id,
            'user_id' => $usuario->id,
            'estado' => 'pendiente',
            'total' => 1000,
        ]);
        $pedido->productos()->attach($producto->id, [
            'cantidad' => 2,
            'precio_unitario' => 500,
        ]);

        return $pedido;
    }

    public function test_sync_crea_el_pedido_y_es_idempotente(): void
    {
        Event::fake([PedidoCambio::class]);
        [$mesa, $producto] = $this->mesaConProducto();
        $usuario = $this->staff();

        $payload = [
            'ops' => [[
                'client_id' => 'cliente-abc-123',
                'type' => 'pedido',
                'mesa_id' => $mesa->id,
                'productos' => [['id' => $producto->id, 'cantidad' => 2]],
            ]],
        ];

        $respuesta = $this->actingAs($usuario)->postJson(route('offline.sync'), $payload);

        $respuesta->assertOk()->assertJsonPath('results.0.ok', true);
        $this->assertDatabaseCount('pedidos', 1);
        $this->assertDatabaseHas('offline_sync', [
            'client_id' => 'cliente-abc-123',
            'estado' => 'sincronizado',
            'user_id' => $usuario->id,
        ]);

        $this->actingAs($usuario)->postJson(route('offline.sync'), $payload)
            ->assertOk()
            ->assertJsonPath('results.0.duplicated', true);
        $this->assertDatabaseCount('pedidos', 1);
    }

    public function test_sync_require_client_id(): void
    {
        $this->actingAs($this->staff())
            ->postJson(route('offline.sync'), ['ops' => [['type' => 'pedido']]])
            ->assertStatus(422)
            ->assertJsonValidationErrors('ops.0.client_id');
    }

    public function test_un_reintento_tras_error_actualiza_el_registro_sin_duplicarlo(): void
    {
        Event::fake([PedidoCambio::class]);
        [$mesa, $producto] = $this->mesaConProducto();
        $usuario = $this->staff();

        OfflineSync::create([
            'client_id' => 'cliente-reintento',
            'type' => 'pedido',
            'user_id' => $usuario->id,
            'payload' => [],
            'estado' => 'error',
            'error' => 'fallo anterior',
        ]);

        $this->actingAs($usuario)->postJson(route('offline.sync'), [
            'ops' => [[
                'client_id' => 'cliente-reintento',
                'type' => 'pedido',
                'mesa_id' => $mesa->id,
                'productos' => [['id' => $producto->id, 'cantidad' => 1]],
            ]],
        ])->assertOk()->assertJsonPath('results.0.ok', true);

        $this->assertDatabaseCount('offline_sync', 1);
        $this->assertDatabaseCount('pedidos', 1);
        $this->assertDatabaseHas('offline_sync', [
            'client_id' => 'cliente-reintento',
            'estado' => 'sincronizado',
        ]);
    }

    public function test_producto_inexistente_no_crea_pedido(): void
    {
        [$mesa] = $this->mesaConProducto();

        $this->actingAs($this->staff())->postJson(route('offline.sync'), [
            'ops' => [[
                'client_id' => 'cliente-producto-invalido',
                'type' => 'pedido',
                'mesa_id' => $mesa->id,
                'productos' => [['id' => 999999, 'cantidad' => 1]],
            ]],
        ])->assertOk()->assertJsonPath('results.0.ok', false);

        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_snapshots_y_endpoints_de_conectividad(): void
    {
        $zona = Zona::create(['nombre' => 'Planta baja']);
        $mesa = Mesa::create(['numero' => '3', 'zona_id' => $zona->id, 'disponible' => true]);
        $categoria = Categoria::create(['name' => 'Cafés']);
        Producto::create([
            'nombre' => 'Espresso',
            'precio' => 300,
            'categoria_id' => $categoria->id,
            'disponible' => true,
        ]);

        $usuario = $this->staff();
        $this->actingAs($usuario)->getJson(route('offline.mesas'))
            ->assertOk()
            ->assertJsonFragment(['id' => $mesa->id, 'numero' => '3']);
        $this->actingAs($usuario)->getJson(route('offline.menu'))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Espresso']);

        $this->getJson(route('offline.csrf'))->assertOk()->assertJsonStructure(['csrf_token']);
        $this->get(route('ping'))->assertNoContent();
    }

    public function test_sync_cambia_estado_solo_hacia_adelante_y_no_duplica(): void
    {
        Notification::fake();
        Event::fake([PedidoCambio::class]);
        [$mesa, $producto] = $this->mesaConProducto();
        $usuario = $this->staff();
        $pedido = $this->pedidoConLinea($mesa, $producto, $usuario);

        $this->actingAs($usuario)->postJson(route('offline.sync'), [
            'ops' => [[
                'client_id' => 'estado-adelante',
                'type' => 'pedido_estado',
                'pedido_id' => $pedido->id,
                'estado' => 'listo',
            ]],
        ])->assertOk()->assertJsonPath('results.0.ok', true);
        $this->assertDatabaseHas('pedidos', ['id' => $pedido->id, 'estado' => 'listo']);
        $this->assertDatabaseHas('offline_sync', [
            'client_id' => 'estado-adelante',
            'type' => 'pedido_estado',
            'estado' => 'sincronizado',
        ]);

        $this->actingAs($usuario)->postJson(route('offline.sync'), [
            'ops' => [[
                'client_id' => 'estado-adelante',
                'type' => 'pedido_estado',
                'pedido_id' => $pedido->id,
                'estado' => 'listo',
            ]],
        ])->assertOk()->assertJsonPath('results.0.duplicated', true);

        $this->actingAs($usuario)->postJson(route('offline.sync'), [
            'ops' => [[
                'client_id' => 'estado-atras',
                'type' => 'pedido_estado',
                'pedido_id' => $pedido->id,
                'estado' => 'pendiente',
            ]],
        ])->assertOk()
            ->assertJsonPath('results.0.ok', true)
            ->assertJsonPath('results.0.skipped', true);

        $this->assertDatabaseHas('pedidos', ['id' => $pedido->id, 'estado' => 'listo']);
    }

    public function test_sync_pago_y_cierre_de_mesa_son_idempotentes(): void
    {
        Notification::fake();
        Event::fake([PedidoCambio::class]);
        [$mesa, $producto] = $this->mesaConProducto();
        $usuario = $this->staff();
        $pedido = $this->pedidoConLinea($mesa, $producto, $usuario);
        $lineaId = $pedido->productos()->first()->pivot->id;

        $pago = [
            'ops' => [[
                'client_id' => 'pago-productos',
                'type' => 'pago_mesa',
                'mesa_id' => $mesa->id,
                'metodo_pago' => 'efectivo',
                'modo' => 'productos',
                'lineas' => [$lineaId],
            ]],
        ];

        $this->actingAs($usuario)->postJson(route('offline.sync'), $pago)
            ->assertOk()
            ->assertJsonPath('results.0.ok', true);
        $this->assertDatabaseCount('pagos', 1);
        $this->assertDatabaseCount('pago_producto', 1);

        $this->actingAs($usuario)->postJson(route('offline.sync'), $pago)
            ->assertOk()
            ->assertJsonPath('results.0.duplicated', true);
        $this->assertDatabaseCount('pagos', 1);

        $this->actingAs($usuario)->postJson(route('offline.sync'), [
            'ops' => [[
                'client_id' => 'cerrar-mesa-offline',
                'type' => 'cerrar_mesa',
                'mesa_id' => $mesa->id,
            ]],
        ])->assertOk()->assertJsonPath('results.0.ok', true);
        $this->assertDatabaseHas('pedidos', ['id' => $pedido->id, 'estado' => 'pagado']);

        $this->actingAs($usuario)->postJson(route('offline.sync'), [
            'ops' => [[
                'client_id' => 'cerrar-mesa-repetido',
                'type' => 'cerrar_mesa',
                'mesa_id' => $mesa->id,
            ]],
        ])->assertOk()->assertJsonPath('results.0.ok', true);
        $this->assertDatabaseCount('pagos', 1);
    }

    public function test_sync_rechaza_pago_en_partes_offline(): void
    {
        [$mesa] = $this->mesaConProducto();

        $this->actingAs($this->staff())->postJson(route('offline.sync'), [
            'ops' => [[
                'client_id' => 'pago-partes',
                'type' => 'pago_mesa',
                'mesa_id' => $mesa->id,
                'metodo_pago' => 'efectivo',
                'modo' => 'partes',
                'partes' => 2,
            ]],
        ])->assertOk()
            ->assertJsonPath('results.0.ok', false)
            ->assertJsonPath('results.0.fatal', true);
        $this->assertDatabaseCount('pagos', 0);
    }

    public function test_snapshots_de_pedidos_y_sesion_para_workspace_offline(): void
    {
        [$mesa, $producto] = $this->mesaConProducto();
        $usuario = $this->staff();
        $pedido = $this->pedidoConLinea($mesa, $producto, $usuario);
        $lineaId = $pedido->productos()->first()->pivot->id;

        $this->actingAs($usuario)->getJson(route('offline.pedidos'))
            ->assertOk()
            ->assertJsonPath('grupos.0.mesa.id', $mesa->id)
            ->assertJsonPath('grupos.0.pedidos.0.id', $pedido->id)
            ->assertJsonFragment(['id' => $lineaId, 'pedido_id' => $pedido->id]);

        $this->actingAs($usuario)->getJson(route('offline.session'))
            ->assertOk()
            ->assertJsonPath('is_staff', true)
            ->assertJsonPath('nombre', $usuario->name);
    }
}
