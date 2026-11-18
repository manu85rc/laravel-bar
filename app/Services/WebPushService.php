<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription as PushSubscriptionMinishlink;
use Minishlink\WebPush\WebPush;

/**
 * Envía notificaciones Web Push (sistema operativo) a las suscripciones
 * guardadas de los usuarios, usando minishlink/web-push con VAPID.
 *
 * El envío es síncrono y tolerante a fallos: nunca rompe el flujo del
 * pedido. Las suscripciones caducadas (404/410) se limpian al vuelo.
 */
class WebPushService
{
    public function __construct(private readonly ?WebPush $webPush = null) {}

    /**
     * Envía a todas las suscripciones de un usuario.
     */
    public function sendToUser(User $user, string $titulo, string $mensaje, array $extra = []): int
    {
        return $this->dispatch($user->pushSubscriptions, $titulo, $mensaje, $extra);
    }

    /**
     * Envía a los push de todos los usuarios listados (p. ej. el personal),
     * opcionalmente excluyendo a quien hizo la acción.
     */
    public function sendToUsers(
        iterable $users,
        string $titulo,
        string $mensaje,
        array $extra = [],
        ?int $exceptUserId = null,
    ): int {
        $suscripciones = collect($users)
            ->when($exceptUserId, fn ($c, $userId) => $c->reject(fn ($u) => $u->id === $userId))
            ->flatMap(fn ($u) => $u->pushSubscriptions ?? []);

        return $this->dispatch($suscripciones, $titulo, $mensaje, $extra);
    }

    /**
     * Ejecuta el envío real y limpia suscripciones caducadas.
     *
     * @return int notificaciones entregadas con éxito
     */
    public function dispatch(iterable $suscripciones, string $titulo, string $mensaje, array $extra = []): int
    {
        $suscripciones = collect($suscripciones)->filter(fn ($s) => filled($s->endpoint));

        if ($suscripciones->isEmpty()) {
            return 0;
        }

        try {
            $webPush = $this->webPush ?? $this->nuevoWebPush();
            $payload = json_encode(
                $this->payload($titulo, $mensaje, $extra),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            );

            foreach ($suscripciones as $suscripcion) {
                $webPush->queueNotification($this->suscripcion($suscripcion), $payload);
            }

            $enviadas = 0;

            foreach ($webPush->flush() as $reporte) {
                if ($reporte->isSuccess()) {
                    $enviadas++;

                    continue;
                }

                if ($reporte->isSubscriptionExpired()) {
                    PushSubscription::query()
                        ->where('endpoint', $reporte->getEndpoint())
                        ->delete();
                }
            }

            return $enviadas;
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar la notificación push', [
                'titulo' => $titulo,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Payload que recibirá el service worker (`public/push-handlers.js`).
     *
     * @return array<string, mixed>
     */
    public function payload(string $titulo, string $mensaje, array $extra = []): array
    {
        return array_merge([
            'title' => $titulo,
            'body' => $mensaje,
            'icon' => asset('pwa-192x192.png'),
            'badge' => asset('pwa-192x192.png'),
            'tag' => 'casual-bar',
        ], $extra);
    }

    private function nuevoWebPush(): WebPush
    {
        return new WebPush([
            'VAPID' => [
                'subject' => (string) config('webpush.subject'),
                'publicKey' => (string) config('webpush.public_key'),
                'privateKey' => (string) config('webpush.private_key'),
            ],
            'timeout' => config('webpush.timeout', 10),
        ]);
    }

    private function suscripcion(PushSubscription $suscripcion): PushSubscriptionMinishlink
    {
        return PushSubscriptionMinishlink::create([
            'endpoint' => $suscripcion->endpoint,
            'publicKey' => $suscripcion->p256dh,
            'authToken' => $suscripcion->auth,
            'contentEncoding' => $suscripcion->content_encoding
                ?? PushSubscription::CONTENT_ENCODING_AES128GCM,
        ]);
    }
}
