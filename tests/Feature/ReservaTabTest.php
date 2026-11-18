<?php

namespace Tests\Feature;

use App\Models\Reserva;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservaTabTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $role = Role::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin']);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function reserva(User $user, string $fecha, string $observaciones): Reserva
    {
        return Reserva::create([
            'user_id' => $user->id,
            'fecha_hora' => $fecha,
            'numero_personas' => 2,
            'observaciones' => $observaciones,
            'estado' => 'pendiente',
        ]);
    }

    public function test_proximas_muestra_solo_reservas_futuras(): void
    {
        $user = $this->superAdmin();

        $this->reserva($user, now()->addDay()->format('d-m-Y H:i'), 'Reserva futura');
        $this->reserva($user, now()->subDays(2)->format('d-m-Y H:i'), 'Reserva reciente');
        $this->reserva($user, now()->subMonths(2)->format('d-m-Y H:i'), 'Reserva vieja');

        $this->actingAs($user)
            ->get('/reservas')
            ->assertOk()
            ->assertSee('Próximas')
            ->assertSee('Historial')
            ->assertSee('Reserva futura')
            ->assertDontSee('Reserva reciente')
            ->assertDontSee('Reserva vieja');
    }

    public function test_historial_muestra_reservas_del_ultimo_mes(): void
    {
        $user = $this->superAdmin();

        $this->reserva($user, now()->addDay()->format('d-m-Y H:i'), 'Reserva futura');
        $this->reserva($user, now()->subDays(2)->format('d-m-Y H:i'), 'Reserva reciente');
        $this->reserva($user, now()->subMonths(2)->format('d-m-Y H:i'), 'Reserva vieja');

        $this->actingAs($user)
            ->get('/reservas?tab=historial')
            ->assertOk()
            ->assertSee('Reserva reciente')
            ->assertDontSee('Reserva futura')
            ->assertDontSee('Reserva vieja');
    }

    public function test_conteos_por_pestana(): void
    {
        $user = $this->superAdmin();

        $this->reserva($user, now()->addDay()->format('d-m-Y H:i'), 'Reserva futura');
        $this->reserva($user, now()->subDays(2)->format('d-m-Y H:i'), 'Reserva reciente');
        $this->reserva($user, now()->subMonths(2)->format('d-m-Y H:i'), 'Reserva vieja');

        $response = $this->actingAs($user)->get('/reservas');
        $response->assertOk();
        $this->assertSame(1, $response->viewData('proximasCount'));
        $this->assertSame(1, $response->viewData('historialCount'));
    }

    public function test_tab_invalida_ca_en_proximas(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/reservas?tab=otra')
            ->assertOk()
            ->assertSee('No hay reservas próximas.');
    }

    public function test_update_status_vuelve_a_la_pestana_activa(): void
    {
        $user = $this->superAdmin();
        $reserva = $this->reserva($user, now()->addDay()->format('d-m-Y H:i'), 'Reserva futura');

        $this->actingAs($user)
            ->from('/reservas?tab=historial')
            ->patch("/reservas/{$reserva->id}/status", ['estado' => 'confirmada'])
            ->assertRedirect('/reservas?tab=historial');

        $this->assertSame('confirmada', $reserva->fresh()->estado);
    }

    public function test_clean_past_purga_solo_reservas_viejas(): void
    {
        $user = $this->superAdmin();

        $futura = $this->reserva($user, now()->addDay()->format('d-m-Y H:i'), 'Reserva futura');
        $reciente = $this->reserva($user, now()->subDays(2)->format('d-m-Y H:i'), 'Reserva reciente');
        $vieja = $this->reserva($user, now()->subMonths(2)->format('d-m-Y H:i'), 'Reserva vieja');

        $this->artisan('reservas:clean-past')
            ->expectsOutputToContain('1 reserva(s) de más de un mes de antigüedad eliminada(s).')
            ->assertSuccessful();

        $this->assertNotNull(Reserva::find($futura->id));
        $this->assertNotNull(Reserva::find($reciente->id));
        $this->assertNull(Reserva::find($vieja->id));
    }
}
