<?php

namespace Tests\Feature;

use App\Models\Mesa;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Reserva;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MesaEditarTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Super Admin', 'slug' => 'super_admin']);
        $this->admin = User::factory()->create(['role_id' => $role->id]);
    }

    public function test_pagina_de_edicion_muestra_selector_de_color_y_eliminar(): void
    {
        $mesa = Mesa::create(['numero' => '1', 'color' => '#2d8f5e']);

        $this->actingAs($this->admin)
            ->get(route('admin.mesas.edit', $mesa))
            ->assertOk()
            ->assertSee('color-palette-edit')
            ->assertSee('Eliminar Mesa');
    }

    public function test_modal_del_plano_muestra_selector_de_color_y_eliminar(): void
    {
        Mesa::create(['numero' => '1']);

        $this->actingAs($this->admin)
            ->get(route('admin.mesas.index'))
            ->assertOk()
            ->assertSee('color-palette-modal')
            ->assertSee('eliminarMesaModal');
    }

    public function test_actualiza_el_color_de_la_mesa(): void
    {
        $mesa = Mesa::create(['numero' => '1']);

        $this->actingAs($this->admin)
            ->put(route('admin.mesas.update', $mesa), [
                'numero' => '1',
                'disponible' => 1,
                'color' => '#e74c3c',
            ])
            ->assertRedirect(route('admin.mesas.index'));

        $this->assertSame('#e74c3c', $mesa->fresh()->color);
    }

    public function test_permite_quitar_el_color(): void
    {
        $mesa = Mesa::create(['numero' => '1', 'color' => '#2d8f5e']);

        $this->actingAs($this->admin)
            ->put(route('admin.mesas.update', $mesa), [
                'numero' => '1',
                'disponible' => 1,
                'color' => '',
            ])
            ->assertRedirect(route('admin.mesas.index'));

        $this->assertNull($mesa->fresh()->color);
    }

    public function test_no_elimina_una_mesa_con_pedidos_reservas_o_pagos(): void
    {
        $mesaPedido = Mesa::create(['numero' => '1']);
        Pedido::create([
            'mesa_id' => $mesaPedido->id,
            'user_id' => $this->admin->id,
            'estado' => 'pendiente',
            'total' => 0,
        ]);

        $mesaReserva = Mesa::create(['numero' => '2']);
        Reserva::create([
            'user_id' => $this->admin->id,
            'mesa_id' => $mesaReserva->id,
            'fecha_hora' => now()->format('d-m-Y H:i'),
            'numero_personas' => 2,
            'estado' => 'confirmada',
        ]);

        $mesaPago = Mesa::create(['numero' => '3']);
        Pago::create([
            'mesa_id' => $mesaPago->id,
            'user_id' => $this->admin->id,
            'metodo_pago' => 'efectivo',
            'modo' => 'total',
            'monto' => 10,
        ]);

        foreach ([$mesaPedido, $mesaReserva, $mesaPago] as $mesa) {
            $this->actingAs($this->admin)
                ->deleteJson(route('admin.mesas.destroy', $mesa))
                ->assertStatus(409)
                ->assertJsonPath('success', false);

            $this->assertDatabaseHas('mesas', ['id' => $mesa->id]);
        }
    }

    public function test_elimina_una_mesa_sin_historial(): void
    {
        $mesa = Mesa::create(['numero' => '1']);

        $this->actingAs($this->admin)
            ->delete(route('admin.mesas.destroy', $mesa))
            ->assertRedirect(route('admin.mesas.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('mesas', ['id' => $mesa->id]);
    }
}
