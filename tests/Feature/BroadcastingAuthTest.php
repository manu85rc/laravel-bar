<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastingAuthTest extends TestCase
{
    use RefreshDatabase;

    private function rol(string $slug, string $name): Role
    {
        return Role::create(['name' => $name, 'slug' => $slug]);
    }

    private function usarReverb(): void
    {
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-key',
            'broadcasting.connections.reverb.secret' => 'test-secret',
            'broadcasting.connections.reverb.app_id' => 'test-app',
        ]);

        // Los canales se registran sobre el broadcaster por defecto; al cambiar
        // de driver hay que volver a cargar `routes/channels.php`.
        app(BroadcastManager::class)->purge('reverb');
        require base_path('routes/channels.php');
    }

    /**
     * @return array<string, string>
     */
    private function payload(): array
    {
        return [
            'channel_name' => 'private-staff',
            'socket_id' => '123456.789012',
        ];
    }

    public function test_staff_puede_autorizar_el_canal_privado_staff(): void
    {
        $this->usarReverb();

        $staff = User::factory()->create([
            'role_id' => $this->rol('staff', 'Staff')->id,
        ]);

        $this->actingAs($staff)
            ->postJson('/broadcasting/auth', $this->payload())
            ->assertOk()
            ->assertJsonStructure(['auth']);
    }

    public function test_un_cliente_no_puede_autorizar_el_canal_privado_staff(): void
    {
        $this->usarReverb();

        $cliente = User::factory()->create([
            'role_id' => $this->rol('user', 'Usuario')->id,
        ]);

        $this->actingAs($cliente)
            ->postJson('/broadcasting/auth', $this->payload())
            ->assertForbidden();
    }

    public function test_un_invitado_no_puede_autorizar_el_canal_privado_staff(): void
    {
        $this->usarReverb();

        $this->postJson('/broadcasting/auth', $this->payload())
            ->assertForbidden();
    }
}
