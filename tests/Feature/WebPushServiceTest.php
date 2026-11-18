<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\WebPushService;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\WebPush;
use Tests\TestCase;

class WebPushServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_payload_tiene_los_campos_del_service_worker(): void
    {
        $servicio = new WebPushService;

        $payload = $servicio->payload('Nuevo pedido', 'Mesa 3: se creó el pedido #1.', [
            'url' => '/pedidos',
            'tag' => 'pedido-1',
        ]);

        $this->assertSame('Nuevo pedido', $payload['title']);
        $this->assertSame('Mesa 3: se creó el pedido #1.', $payload['body']);
        $this->assertSame('/pedidos', $payload['url']);
        $this->assertSame('pedido-1', $payload['tag']);
        $this->assertSame('casual-bar', (new WebPushService)->payload('a', 'b')['tag']);
    }

    public function test_sin_suscripciones_no_envia(): void
    {
        $usuario = User::factory()->create();

        $enviadas = (new WebPushService)->sendToUser($usuario, 'Título', 'Mensaje');

        $this->assertSame(0, $enviadas);
    }

    public function test_envia_y_limpia_suscripciones_caducadas(): void
    {
        $usuario = User::factory()->create();
        $usuario->pushSubscriptions()->create([
            'endpoint' => 'https://push.example.com/activa',
            'p256dh' => 'p256dh-activa',
            'auth' => 'auth-activa',
        ]);
        $usuario->pushSubscriptions()->create([
            'endpoint' => 'https://push.example.com/caducada',
            'p256dh' => 'p256dh-caducada',
            'auth' => 'auth-caducada',
        ]);

        $webPush = \Mockery::mock(WebPush::class);
        $webPush->shouldReceive('queueNotification')->twice();
        // flush() declara el tipo de retorno \Generator: hay que devolver uno de verdad.
        $webPush->shouldReceive('flush')->once()->andReturnUsing(function () {
            yield new MessageSentReport(
                new Request('POST', 'https://push.example.com/activa'),
                new Response(201)
            );

            yield new MessageSentReport(
                new Request('POST', 'https://push.example.com/caducada'),
                new Response(410),
                false,
                'Expired subscription'
            );
        });

        $enviadas = (new WebPushService($webPush))
            ->sendToUser($usuario, 'Título', 'Mensaje');

        $this->assertSame(1, $enviadas);
        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $usuario->id,
            'endpoint' => 'https://push.example.com/activa',
        ]);
        $this->assertDatabaseMissing('push_subscriptions', ['endpoint' => 'https://push.example.com/caducada']);
    }
}
