<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as OAuthUser;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'google-id',
            'services.google.client_secret' => 'google-secret',
            'services.google.redirect' => null,
            'services.facebook.client_id' => 'facebook-id',
            'services.facebook.client_secret' => 'facebook-secret',
            'services.facebook.redirect' => null,
        ]);
    }

    private function role(string $slug): Role
    {
        return Role::firstOrCreate(
            ['slug' => $slug],
            ['name' => ucfirst($slug)],
        );
    }

    private function fakeGoogle(array $attributes = []): void
    {
        Socialite::fake('google', OAuthUser::fake(array_merge([
            'id' => 'google-123',
            'nickname' => 'juan.perez',
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
        ], $attributes)));
    }

    public function test_entrar_con_google_crea_un_usuario_cliente(): void
    {
        $this->role('user');
        $this->fakeGoogle();

        $this->get(route('social.callback', ['provider' => 'google']))
            ->assertRedirect(route('home'));

        $user = User::where('email', 'juan@example.com')->firstOrFail();

        $this->assertSame('Juan Pérez', $user->name);
        $this->assertTrue($user->isUser());
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
        ]);
    }

    public function test_un_usuario_creado_por_social_no_tiene_contrasena(): void
    {
        $this->role('user');
        $this->fakeGoogle();

        $this->get(route('social.callback', ['provider' => 'google']))->assertRedirect(route('home'));

        $user = User::where('email', 'juan@example.com')->firstOrFail();

        $this->assertFalse($user->hasPassword());
        $this->assertFalse($user->canUnlinkSocialAccount());
    }

    public function test_el_login_por_contrasena_falla_para_una_cuenta_solo_social(): void
    {
        $this->role('user');
        $this->fakeGoogle();

        $this->get(route('social.callback', ['provider' => 'google']));
        auth()->logout();

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => 'juan@example.com',
                'password' => 'cualquier-cosa',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_el_callback_no_duplica_la_cuenta_al_repetir_login(): void
    {
        $this->role('user');
        $this->fakeGoogle();

        $this->get(route('social.callback', ['provider' => 'google']));
        auth()->logout();

        $this->fakeGoogle();
        $this->get(route('social.callback', ['provider' => 'google']))->assertRedirect(route('home'));

        $this->assertSame(1, User::count());
        $this->assertSame(1, SocialAccount::count());
    }

    public function test_el_ultimo_login_conserva_el_token_actualizado(): void
    {
        $this->role('user');
        $this->fakeGoogle();
        $this->get(route('social.callback', ['provider' => 'google']));

        auth()->logout();

        $this->fakeGoogle(['nickname' => 'juan.perez.nuevo']);
        $this->get(route('social.callback', ['provider' => 'google']));

        $this->assertSame('juan.perez.nuevo', SocialAccount::first()->nickname);
    }

    public function test_autovincula_un_cliente_existente_que_uso_el_mismo_correo(): void
    {
        $this->role('user');
        $existing = User::factory()->create([
            'email' => 'juan@example.com',
            'role_id' => $this->role('user')->id,
        ]);

        $this->fakeGoogle();

        $this->get(route('social.callback', ['provider' => 'google']))->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($existing);
        $this->assertSame(1, User::count());
        $this->assertDatabaseHas('social_accounts', ['user_id' => $existing->id]);
    }

    public function test_no_autovincula_una_cuenta_con_rol_privilegiado(): void
    {
        $this->role('admin');
        $admin = User::factory()->create([
            'email' => 'juan@example.com',
            'role_id' => $this->role('admin')->id,
        ]);

        $this->fakeGoogle();

        $this->from(route('login'))
            ->get(route('social.callback', ['provider' => 'google']))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseCount('social_accounts', 0);
        $this->assertDatabaseMissing('social_accounts', ['user_id' => $admin->id]);
    }

    public function test_el_rol_user_debe_existir_para_crear_cuentas_sociales(): void
    {
        $this->fakeGoogle();

        $this->withoutExceptionHandling();

        $this->expectException(\RuntimeException::class);

        $this->get(route('social.callback', ['provider' => 'google']));
    }

    public function test_un_proveedor_sin_configurar_muestra_error_en_lugar_de_explotar(): void
    {
        config(['services.facebook.client_id' => null]);

        $this->from(route('login'))
            ->get(route('social.callback', ['provider' => 'facebook']))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_el_login_social_respeta_la_intencion_de_reserva(): void
    {
        $this->role('user');
        $this->fakeGoogle();

        $this->get(route('social.redirect', ['provider' => 'google', 'redirect_from' => 'reservar']));

        $this->get(route('social.callback', ['provider' => 'google']))
            ->assertRedirect(route('home'))
            ->assertSessionHas('open_reservar', true);
    }

    public function test_un_usuario_autenticado_puede_vincular_una_cuenta_social(): void
    {
        $userRole = $this->role('staff');
        $staff = User::factory()->create(['role_id' => $userRole->id]);

        $this->actingAs($staff);
        $this->fakeGoogle();

        $this->get(route('social.redirect', ['provider' => 'google']));
        $this->get(route('social.callback', ['provider' => 'google']))
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $staff->id,
            'provider' => 'google',
        ]);
        $this->assertAuthenticatedAs($staff);
    }

    public function test_no_se_puede_vincular_una_cuenta_ya_vinculada_a_otro_usuario(): void
    {
        $this->role('user');
        $other = User::factory()->create(['role_id' => $this->role('user')->id]);
        $other->socialAccounts()->create([
            'provider' => 'google',
            'provider_id' => 'google-123',
        ]);

        $me = User::factory()->create(['role_id' => $this->role('user')->id]);

        $this->actingAs($me);
        $this->fakeGoogle();

        $this->get(route('social.redirect', ['provider' => 'google']));
        $this->get(route('social.callback', ['provider' => 'google']))
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('social_accounts', 1);
    }

    public function test_no_se_puede_desvincular_la_ultima_forma_de_acceso(): void
    {
        $this->role('user');
        $user = User::factory()->create(['role_id' => $this->role('user')->id]);
        $user->forceFill(['password' => null])->save();

        $account = $user->socialAccounts()->create([
            'provider' => 'google',
            'provider_id' => 'google-123',
        ]);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->delete(route('social.unlink', $account))
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseHas('social_accounts', ['id' => $account->id]);
    }

    public function test_se_puede_desvincular_cuando_existe_una_contrasena(): void
    {
        $this->role('user');
        $user = User::factory()->create(['role_id' => $this->role('user')->id]);

        $account = $user->socialAccounts()->create([
            'provider' => 'google',
            'provider_id' => 'google-123',
        ]);

        $this->actingAs($user)
            ->delete(route('social.unlink', $account))
            ->assertRedirect(route('profile.edit'));

        $this->assertDatabaseMissing('social_accounts', ['id' => $account->id]);
    }

    public function test_no_se_puede_desvincular_la_cuenta_de_otro_usuario(): void
    {
        $this->role('user');
        $other = User::factory()->create(['role_id' => $this->role('user')->id]);
        $me = User::factory()->create(['role_id' => $this->role('user')->id]);

        $account = $other->socialAccounts()->create([
            'provider' => 'google',
            'provider_id' => 'google-999',
        ]);

        $this->actingAs($me)
            ->delete(route('social.unlink', $account))
            ->assertForbidden();

        $this->assertDatabaseHas('social_accounts', ['id' => $account->id]);
    }
}
