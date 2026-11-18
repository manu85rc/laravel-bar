<?php

namespace Tests\Feature;

use App\Models\Mesa;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Permission;
use App\Models\Producto;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagoMesaTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $role->permissions()->attach(
            Permission::create(['name' => 'Gestionar pedidos', 'slug' => 'admin.pedidos'])
        );
        $this->user = User::factory()->create(['role_id' => $role->id]);
    }

    private function mesaConPedido(array $lineas): Mesa
    {
        $mesa = Mesa::create(['numero' => 7, 'disponible' => true]);
        $total = array_sum(array_column($lineas, 'precio'));

        $pedido = Pedido::create([
            'mesa_id' => $mesa->id,
            'user_id' => $this->user->id,
            'estado' => 'servido',
            'total' => $total,
        ]);

        foreach ($lineas as $linea) {
            $producto = Producto::create(['nombre' => $linea['nombre'], 'precio' => $linea['precio']]);
            $pedido->productos()->attach($producto->id, [
                'cantidad' => $linea['cantidad'] ?? 1,
                'precio_unitario' => $linea['precio'],
            ]);
        }

        return $mesa;
    }

    public function test_pago_total_marca_todas_las_lineas_y_permite_cerrar(): void
    {
        $mesa = $this->mesaConPedido([
            ['nombre' => 'Cerveza', 'precio' => 60],
            ['nombre' => 'Alitas', 'precio' => 40],
        ]);

        $this->actingAs($this->user)
            ->post(route('pedidos.pagarMesa', $mesa), [
                'metodo_pago' => 'efectivo',
                'modo' => 'total',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(2, \DB::table('pago_producto')->count());
        $this->assertSame(1, Pago::where('mesa_id', $mesa->id)->count());

        $this->actingAs($this->user)
            ->post(route('pedidos.cerrarMesa', $mesa))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('pagado', $mesa->pedidos()->first()->estado);
    }

    public function test_cerrar_mesa_falla_si_hay_productos_sin_pagar(): void
    {
        $mesa = $this->mesaConPedido([
            ['nombre' => 'Cerveza', 'precio' => 60],
            ['nombre' => 'Alitas', 'precio' => 40],
        ]);

        $linea = $mesa->pedidos()->first()->productos()->first()->pivot;

        $this->actingAs($this->user)
            ->post(route('pedidos.pagarMesa', $mesa), [
                'metodo_pago' => 'tarjeta',
                'modo' => 'productos',
                'lineas' => [$linea->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(1, \DB::table('pago_producto')->count());

        $this->actingAs($this->user)
            ->post(route('pedidos.cerrarMesa', $mesa))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('servido', $mesa->pedidos()->first()->estado);
    }

    public function test_dividir_en_partes_crea_un_pago_por_persona(): void
    {
        $mesa = $this->mesaConPedido([
            ['nombre' => 'Cerveza', 'precio' => 60],
            ['nombre' => 'Alitas', 'precio' => 40],
        ]);

        $this->actingAs($this->user)
            ->post(route('pedidos.pagarMesa', $mesa), [
                'metodo_pago' => 'qr',
                'modo' => 'partes',
                'partes' => 2,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $pagos = Pago::where('mesa_id', $mesa->id)->orderBy('id')->get();
        $this->assertSame(2, $pagos->count());
        $this->assertSame(100.0, (float) $pagos->sum('monto'));
        $this->assertSame(2, \DB::table('pago_producto')->count());
        $this->assertSame(2, $pagos->first()->partes);
        $this->assertSame([50.0, 50.0], $pagos->pluck('monto')->map(fn ($m) => (float) $m)->all());

        $this->actingAs($this->user)
            ->post(route('pedidos.cerrarMesa', $mesa))
            ->assertSessionHas('success');
    }

    public function test_dividir_en_partes_da_montos_identicos_con_centavos(): void
    {
        $mesa = $this->mesaConPedido([
            ['nombre' => 'Cerveza', 'precio' => 60],
            ['nombre' => 'Alitas', 'precio' => 40],
            ['nombre' => 'Agua', 'precio' => 10.35],
        ]);

        $this->actingAs($this->user)
            ->post(route('pedidos.pagarMesa', $mesa), [
                'metodo_pago' => 'qr',
                'modo' => 'partes',
                'partes' => 3,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $pagos = Pago::where('mesa_id', $mesa->id)->orderBy('id')->get();
        $this->assertSame(3, $pagos->count());
        $this->assertSame([36.78, 36.78, 36.79], $pagos->pluck('monto')->map(fn ($m) => (float) $m)->all());
        $this->assertSame(110.35, (float) $pagos->sum('monto'));
        $this->assertSame(3, \DB::table('pago_producto')->count());

        $this->actingAs($this->user)
            ->post(route('pedidos.cerrarMesa', $mesa))
            ->assertSessionHas('success');
    }

    public function test_dividir_en_partes_con_mas_personas_que_lineas(): void
    {
        $mesa = $this->mesaConPedido([
            ['nombre' => 'Cerveza', 'precio' => 60],
            ['nombre' => 'Alitas', 'precio' => 40],
        ]);

        $this->actingAs($this->user)
            ->post(route('pedidos.pagarMesa', $mesa), [
                'metodo_pago' => 'efectivo',
                'modo' => 'partes',
                'partes' => 3,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $pagos = Pago::where('mesa_id', $mesa->id)->orderBy('id')->get();
        $this->assertSame(3, $pagos->count());
        $this->assertSame([33.33, 33.33, 33.34], $pagos->pluck('monto')->map(fn ($m) => (float) $m)->all());
        $this->assertSame(100.0, (float) $pagos->sum('monto'));
        $this->assertSame(2, \DB::table('pago_producto')->count());

        $this->actingAs($this->user)
            ->post(route('pedidos.cerrarMesa', $mesa))
            ->assertSessionHas('success');
    }

    public function test_no_se_puede_pagar_dos_veces_la_misma_linea(): void
    {
        $mesa = $this->mesaConPedido([
            ['nombre' => 'Cerveza', 'precio' => 60],
            ['nombre' => 'Alitas', 'precio' => 40],
        ]);

        $linea = $mesa->pedidos()->first()->productos()->first()->pivot;

        $this->actingAs($this->user)
            ->post(route('pedidos.pagarMesa', $mesa), [
                'metodo_pago' => 'efectivo',
                'modo' => 'productos',
                'lineas' => [$linea->id],
            ])
            ->assertSessionHas('success');

        $this->actingAs($this->user)
            ->post(route('pedidos.pagarMesa', $mesa), [
                'metodo_pago' => 'efectivo',
                'modo' => 'total',
            ])
            ->assertSessionHas('success');

        $this->assertSame(2, \DB::table('pago_producto')->count());
        $this->assertSame(2, Pago::count());
    }

    public function test_rutas_de_pago_requieren_permiso(): void
    {
        $mesa = $this->mesaConPedido([['nombre' => 'Cerveza', 'precio' => 60]]);

        $role = Role::create(['name' => 'Camarero', 'slug' => 'camarero']);
        $sinPermiso = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($sinPermiso)
            ->post(route('pedidos.pagarMesa', $mesa), [
                'metodo_pago' => 'efectivo',
                'modo' => 'total',
            ])
            ->assertRedirect();

        $this->actingAs($sinPermiso)
            ->post(route('pedidos.cerrarMesa', $mesa))
            ->assertRedirect();

        $this->assertSame(0, Pago::count());
    }

    public function test_index_muestra_boton_pagar(): void
    {
        $this->mesaConPedido([['nombre' => 'Cerveza', 'precio' => 60]]);

        $this->actingAs($this->user)
            ->get(route('pedidos.index'))
            ->assertOk()
            ->assertSee('Pagar')
            ->assertSee('Mesa 7');

        $this->actingAs($this->user)
            ->get(route('pedidos.index', ['tab' => 'historial']))
            ->assertOk()
            ->assertSee('Historial');
    }

    public function test_update_estado_no_permite_marcar_pagado_directamente(): void
    {
        $mesa = $this->mesaConPedido([['nombre' => 'Cerveza', 'precio' => 60]]);
        $pedido = $mesa->pedidos()->first();

        $this->actingAs($this->user)
            ->put(route('pedidos.updateEstado', $pedido), ['estado' => 'pagado'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('servido', $pedido->fresh()->estado);
        $this->assertSame(0, Pago::count());
        $this->assertSame(0, \DB::table('pago_producto')->count());

        $this->actingAs($this->user)
            ->put(route('pedidos.updateEstado', $pedido), ['estado' => 'listo'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('listo', $pedido->fresh()->estado);
    }

    public function test_pagar_rechaza_lineas_que_no_existen(): void
    {
        $mesa = $this->mesaConPedido([['nombre' => 'Cerveza', 'precio' => 60]]);

        $this->actingAs($this->user)
            ->post(route('pedidos.pagarMesa', $mesa), [
                'metodo_pago' => 'efectivo',
                'modo' => 'productos',
                'lineas' => [999999],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('lineas.0');

        $this->assertSame(0, Pago::count());
    }
}
