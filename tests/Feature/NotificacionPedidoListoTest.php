<?php

namespace Tests\Feature;

use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificacionPedidoListoTest extends TestCase
{
    use RefreshDatabase;

    private function mesa(int $numero = 1): Mesa
    {
        return Mesa::create(['numero' => (string) $numero]);
    }

    private function pedido(User $mesero, string $estado = 'pendiente'): Pedido
    {
        return Pedido::create([
            'mesa_id' => $this->mesa()->id,
            'user_id' => $mesero->id,
            'estado' => $estado,
            'total' => 10,
        ]);
    }

    public function test_avisa_solo_en_la_transicion_a_listo(): void
    {
        $mesero = User::factory()->create();
        $pedido = $this->pedido($mesero);

        $pedido->update(['estado' => 'preparando']);
        $this->assertSame(0, $mesero->notifications()->count());

        $pedido->update(['estado' => 'listo']);
        $this->assertSame(1, $mesero->notifications()->count());
        $this->assertSame(1, $mesero->notificacionesNoLeidas());

        // Re-guardar o seguir cambiando el estado no duplica el aviso.
        $pedido->update(['total' => 12]);
        $pedido->update(['estado' => 'sirvido']);
        $pedido->update(['estado' => 'pagado']);

        $this->assertSame(1, $mesero->notifications()->count());
        $this->assertSame(1, $mesero->notificacionesNoLeidas());
    }

    public function test_payload_en_espanol_y_scoping_por_usuario(): void
    {
        $mesero = User::factory()->create();
        $otro = User::factory()->create();

        $pedido = $this->pedido($mesero);
        $pedido->update(['estado' => 'listo']);

        $this->actingAs($mesero)
            ->getJson(route('notificaciones.index'))
            ->assertOk()
            ->assertJsonPath('no_leidas', 1)
            ->assertJsonCount(1, 'notificaciones')
            ->assertJsonPath('notificaciones.0.titulo', 'Pedido listo')
            ->assertJsonPath('notificaciones.0.leida', false)
            ->assertJsonPath('notificaciones.0.mensaje', 'Mesa 1: el pedido #'.$pedido->id.' ya está listo para servir.');

        // Otro usuario no ve la notificación...
        $this->actingAs($otro)
            ->getJson(route('notificaciones.index'))
            ->assertOk()
            ->assertJsonPath('no_leidas', 0)
            ->assertJsonCount(0, 'notificaciones');

        // ...ni puede marcarla como leída.
        $id = $mesero->notifications()->first()->id;

        $this->actingAs($otro)
            ->postJson(route('notificaciones.leer', $id))
            ->assertNotFound();

        $this->actingAs($mesero)
            ->postJson(route('notificaciones.leer', $id))
            ->assertOk()
            ->assertJsonPath('no_leidas', 0);

        $this->assertNotNull($mesero->notifications()->first()->read_at);

        $this->actingAs($mesero)
            ->postJson(route('notificaciones.leer-todas'))
            ->assertOk()
            ->assertJsonPath('no_leidas', 0);
    }

    public function test_limpia_notificaciones_al_eliminar_el_pedido(): void
    {
        $mesero = User::factory()->create();
        $pedido = $this->pedido($mesero);

        $pedido->update(['estado' => 'listo']);
        $this->assertSame(1, $mesero->notifications()->count());

        $pedido->delete();

        $this->assertSame(0, $mesero->notifications()->count());
    }

    public function test_eliminar_usuario_limpia_sus_notificaciones(): void
    {
        $rolSuperAdmin = Role::create(['name' => 'Super Admin', 'slug' => 'super_admin']);
        $superAdmin = User::factory()->create(['role_id' => $rolSuperAdmin->id]);

        $mesero = User::factory()->create();
        $pedido = $this->pedido($mesero);
        $pedido->update(['estado' => 'listo']);

        $this->assertDatabaseCount('notifications', 2);
        $this->assertSame(1, $mesero->notifications()->count());

        $this->actingAs($superAdmin)
            ->delete(route('admin.users.destroy', $mesero))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseCount('notifications', 1);
        $this->assertSame(0, $mesero->notifications()->count());
        $this->assertSame(1, $superAdmin->fresh()->notifications()->count());
    }

    public function test_comando_de_limpieza_purga_huerfanas(): void
    {
        $mesero = User::factory()->create();
        $pedido = $this->pedido($mesero);
        $pedido->update(['estado' => 'listo']);

        // Se va el usuario sin pasar por el controller: queda huérfana.
        $mesero->delete();
        $this->assertDatabaseCount('notifications', 1);

        $this->artisan('notificaciones:limpiar')->assertSuccessful();

        $this->assertDatabaseCount('notifications', 0);
    }
}
