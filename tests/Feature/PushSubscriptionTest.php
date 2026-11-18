<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    const ENDPOINT = 'https://fcm.googleapis.com/fcm/send/abc123';

    private function rol(string $slug, string $name): Role
    {
        return Role::create(['name' => $name, 'slug' => $slug]);
    }

    /**
     * @return array<string, string>
     */
    private function payload(): array
    {
        return [
            'endpoint' => self::ENDPOINT,
            'p256dh' => 'BNcRdreALRFXTkOOUHK1EtK2wtaz5Ry4YfYCA_0QTpQtUbVlUls0VJXg7A8u-Ts1XbjhazAkj7I99e8QcYP7DkM',
            'auth' => 'tBHItJI5svbpez7KI4CCXg==',
            'content_encoding' => 'aes128gcm',
        ];
    }

    public function test_el_personal_puede_suscribirse(): void
    {
        $staff = User::factory()->create([
            'role_id' => $this->rol('staff', 'Staff')->id,
        ]);

        $this->actingAs($staff)
            ->postJson('/push/subscribe', $this->payload())
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $staff->id,
            'endpoint' => self::ENDPOINT,
        ]);
    }

    public function test_suscribirse_dos_veces_con_el_mismo_endpoint_no_duplica(): void
    {
        $staff = User::factory()->create([
            'role_id' => $this->rol('staff', 'Staff')->id,
        ]);

        $this->actingAs($staff)->postJson('/push/subscribe', $this->payload())->assertOk();
        $this->actingAs($staff)->postJson('/push/subscribe', $this->payload())->assertOk();

        $this->assertSame(1, $staff->pushSubscriptions()->count());
    }

    public function test_el_personal_puede_darse_de_baja(): void
    {
        $staff = User::factory()->create([
            'role_id' => $this->rol('staff', 'Staff')->id,
        ]);

        $this->actingAs($staff)->postJson('/push/subscribe', $this->payload())->assertOk();

        $this->actingAs($staff)
            ->deleteJson('/push/unsubscribe', ['endpoint' => self::ENDPOINT])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseMissing('push_subscriptions', [
            'user_id' => $staff->id,
            'endpoint' => self::ENDPOINT,
        ]);
    }

    public function test_un_cliente_no_puede_suscribirse(): void
    {
        $cliente = User::factory()->create([
            'role_id' => $this->rol('user', 'Cliente')->id,
        ]);

        $this->actingAs($cliente)
            ->postJson('/push/subscribe', $this->payload())
            ->assertForbidden();
    }

    public function test_un_invitado_no_puede_suscribirse(): void
    {
        $this->postJson('/push/subscribe', $this->payload())
            ->assertUnauthorized();
    }

    public function test_la_suscripcion_de_un_usuario_no_afecta_a_otro(): void
    {
        $rolStaff = $this->rol('staff', 'Staff');

        $staff1 = User::factory()->create(['role_id' => $rolStaff->id]);
        $staff2 = User::factory()->create(['role_id' => $rolStaff->id]);

        $this->actingAs($staff1)->postJson('/push/subscribe', $this->payload())->assertOk();

        $this->actingAs($staff2)
            ->deleteJson('/push/unsubscribe', ['endpoint' => self::ENDPOINT])
            ->assertOk();

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $staff1->id,
            'endpoint' => self::ENDPOINT,
        ]);
    }
}
