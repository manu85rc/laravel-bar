<?php

namespace Tests\Feature;

use App\Models\MercadoPagoSetting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MercadoPagoTabTest extends TestCase
{
    use RefreshDatabase;

    private function userConRol(string $rol, array $permisos = []): User
    {
        Permission::firstOrCreate(['slug' => 'admin.pedidos'], ['name' => 'Gestionar pedidos']);
        Permission::firstOrCreate(['slug' => 'admin.dashboard'], ['name' => 'Ver dashboard']);

        $role = Role::firstOrCreate(['slug' => $rol], ['name' => ucfirst($rol)]);
        $role->permissions()->sync(Permission::whereIn('slug', $permisos)->pluck('id'));

        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_super_admin_ve_la_pestana_y_el_listado(): void
    {
        MercadoPagoSetting::getInstance()->update(['access_token' => 'APP_USR-TEST']);

        Http::fake([
            'api.mercadopago.com/*' => Http::response(['results' => [[
                'id' => 123,
                'status' => 'approved',
                'date_created' => '2026-10-20T15:00:00.000Z',
                'payment_method_id' => 'visa',
                'payment_type_id' => 'credit_card',
                'transaction_amount' => 1500.5,
                'currency_id' => 'ARS',
                'description' => 'Pago mesa 3',
                'site_id' => 'MLA',
                'payer' => [
                    'first_name' => 'Manuel',
                    'last_name' => 'García',
                    'email' => 'manuel@example.com',
                    'identification' => ['type' => 'CUIL', 'number' => '20319818732'],
                ],
            ]]]),
        ]);

        $this->actingAs($this->userConRol('super_admin'))
            ->get('/pedidos?tab=mercadopago')
            ->assertOk()
            ->assertSee('tab=mercadopago')
            ->assertSee('Pago mesa 3')
            ->assertSee('1,500.50')
            ->assertSee('Aprobado')
            ->assertSee('Tarjeta de crédito')
            ->assertSee('Manuel García')
            ->assertSee('manuel@example.com')
            ->assertSee('CUIL 20319818732');
    }

    public function test_transferencia_muestra_email_y_documento_del_pagador(): void
    {
        MercadoPagoSetting::getInstance()->update(['access_token' => 'APP_USR-TEST']);

        Http::fake([
            'api.mercadopago.com/*' => Http::response(['results' => [[
                'id' => 456,
                'status' => 'approved',
                'date_created' => '2026-10-20T15:00:00.000Z',
                'payment_method_id' => 'cvu',
                'payment_type_id' => 'bank_transfer',
                'transaction_amount' => 100000,
                'currency_id' => 'ARS',
                'description' => 'Bank Transfer',
                'site_id' => 'MLA',
                'payer' => [
                    'first_name' => null,
                    'last_name' => null,
                    'email' => 'manuelgarciayuli@gmail.com',
                    'identification' => ['type' => 'CUIL', 'number' => '20319818732'],
                ],
            ]]]),
        ]);

        $this->actingAs($this->userConRol('super_admin'))
            ->get('/pedidos?tab=mercadopago')
            ->assertOk()
            ->assertSee('Transferencia')
            ->assertSee('manuelgarciayuli@gmail.com')
            ->assertSee('CUIL 20319818732');
    }

    public function test_pagador_de_tarjeta_usa_el_titular(): void
    {
        MercadoPagoSetting::getInstance()->update(['access_token' => 'APP_USR-TEST']);

        Http::fake([
            'api.mercadopago.com/*' => Http::response(['results' => [[
                'id' => 789,
                'status' => 'approved',
                'date_created' => '2026-10-20T15:00:00.000Z',
                'payment_method_id' => 'debvisa',
                'payment_type_id' => 'debit_card',
                'transaction_amount' => 5000,
                'currency_id' => 'ARS',
                'card' => ['cardholder' => ['name' => 'GARCIA YULI MANUEL']],
                'site_id' => 'MLA',
            ]]]),
        ]);

        $this->actingAs($this->userConRol('super_admin'))
            ->get('/pedidos?tab=mercadopago')
            ->assertOk()
            ->assertSee('Tarjeta de débito')
            ->assertSee('GARCIA YULI MANUEL');
    }

    public function test_columna_pagador_se_puede_ocultar(): void
    {
        MercadoPagoSetting::getInstance()->update([
            'access_token' => 'APP_USR-TEST',
            'mostrar_pagador' => false,
        ]);

        Http::fake([
            'api.mercadopago.com/*' => Http::response(['results' => [[
                'id' => 321,
                'status' => 'approved',
                'date_created' => '2026-10-20T15:00:00.000Z',
                'payment_method_id' => 'cvu',
                'payment_type_id' => 'bank_transfer',
                'transaction_amount' => 100,
                'currency_id' => 'ARS',
                'payer' => ['email' => 'oculto@example.com'],
                'site_id' => 'MLA',
            ]]]),
        ]);

        $this->actingAs($this->userConRol('super_admin'))
            ->get('/pedidos?tab=mercadopago')
            ->assertOk()
            ->assertDontSee('oculto@example.com');
    }

    public function test_admin_ve_la_pestana(): void
    {
        Http::fake();

        $this->actingAs($this->userConRol('admin', ['admin.pedidos', 'admin.mercadopago']))
            ->get('/pedidos?tab=mercadopago')
            ->assertOk()
            ->assertSee('tab=mercadopago')
            ->assertSee('Mercado Pago');
    }

    public function test_staff_no_ve_la_pestana_y_le_muestra_activos(): void
    {
        $this->actingAs($this->userConRol('staff', ['admin.pedidos']))
            ->get('/pedidos?tab=mercadopago')
            ->assertOk()
            ->assertDontSee('tab=mercadopago')
            ->assertSee('data-tab="activos"', false);
    }

    public function test_staff_y_admin_no_pueden_ver_la_config(): void
    {
        $staff = $this->userConRol('staff', ['admin.pedidos']);
        $this->actingAs($staff)->get('/admin/mercado-pago')->assertRedirect();

        $admin = $this->userConRol('admin', ['admin.pedidos', 'admin.mercadopago']);
        $this->actingAs($admin)->get('/admin/mercado-pago')->assertRedirect();

        $this->actingAs($this->userConRol('super_admin'))->get('/admin/mercado-pago')->assertOk();
    }

    public function test_super_admin_configura_columnas_y_estados(): void
    {
        $this->actingAs($this->userConRol('super_admin'))
            ->put('/admin/mercado-pago', [
                'access_token' => 'APP_USR-123',
                'estados' => ['approved', 'pending'],
                'limite' => 20,
                'mostrar_fecha' => 1,
                'mostrar_monto' => 1,
            ])
            ->assertRedirect('/admin/mercado-pago');

        $settings = MercadoPagoSetting::getInstance();
        $this->assertSame('APP_USR-123', $settings->access_token);
        $this->assertSame(['approved', 'pending'], $settings->estados);
        $this->assertSame(20, $settings->limite);
        $this->assertTrue($settings->mostrar_fecha);
        $this->assertFalse($settings->mostrar_metodo);
    }

    public function test_admin_no_puede_modificar_la_config(): void
    {
        $admin = $this->userConRol('admin', ['admin.pedidos', 'admin.mercadopago']);

        $this->actingAs($admin)->put('/admin/mercado-pago', [
            'estados' => ['approved'],
            'limite' => 10,
        ])->assertRedirect();

        $this->assertNull(MercadoPagoSetting::getInstance()->access_token);
    }

    public function test_sin_token_muestra_estado_vacio(): void
    {
        Http::fake();

        $this->actingAs($this->userConRol('super_admin'))
            ->get('/pedidos?tab=mercadopago')
            ->assertOk()
            ->assertSee('Mercado Pago aún no está configurado');

        Http::assertNothingSent();
    }

    public function test_estados_configurados_filtran_la_respuesta(): void
    {
        MercadoPagoSetting::getInstance()->update([
            'access_token' => 'APP_USR-TEST',
            'estados' => ['approved'],
        ]);

        Http::fake([
            'api.mercadopago.com/*' => Http::response(['results' => [
                ['id' => 1, 'status' => 'approved', 'date_created' => now()->toIso8601String(), 'transaction_amount' => 100, 'site_id' => 'MLA'],
                ['id' => 2, 'status' => 'rejected', 'date_created' => now()->toIso8601String(), 'transaction_amount' => 200, 'site_id' => 'MLA'],
            ]]),
        ]);

        $this->actingAs($this->userConRol('super_admin'))
            ->get('/pedidos?tab=mercadopago')
            ->assertOk()
            ->assertSee('Aprobado')
            ->assertDontSee('Rechazado');
    }
}
