<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Endpoints JSON de suscripciones Web Push (notificaciones con la app cerrada).
 *
 * Reservados al personal del bar (staff/admin/super_admin): los clientes no
 * reciben push.
 */
class PushSubscriptionController extends Controller
{
    public function __construct(private readonly WebPushService $webPush) {}

    /**
     * Alta (o actualización) de una suscripción del usuario autenticado.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);

        $datos = $request->validate([
            'endpoint' => ['required', 'string', 'max:2048'],
            'p256dh' => ['required', 'string', 'max:2048'],
            'auth' => ['required', 'string', 'max:1024'],
            'content_encoding' => ['nullable', Rule::in(['aes128gcm', 'aesgcm'])],
        ]);

        $datos['content_encoding'] = $datos['content_encoding']
            ?? PushSubscription::CONTENT_ENCODING_AES128GCM;

        $request->user()->pushSubscriptions()->updateOrCreate(
            ['endpoint' => $datos['endpoint']],
            [
                'p256dh' => $datos['p256dh'],
                'auth' => $datos['auth'],
                'content_encoding' => $datos['content_encoding'],
            ],
        );

        return response()->json([
            'ok' => true,
            'suscripciones' => $request->user()->pushSubscriptions()->count(),
        ]);
    }

    /**
     * Baja de una suscripción del usuario autenticado.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);

        $datos = $request->validate([
            'endpoint' => ['required', 'string', 'max:2048'],
        ]);

        $borradas = $request->user()->pushSubscriptions()
            ->where('endpoint', $datos['endpoint'])
            ->delete();

        return response()->json([
            'ok' => true,
            'borradas' => $borradas,
            'suscripciones' => $request->user()->pushSubscriptions()->count(),
        ]);
    }

    /**
     * Envía una notificación de prueba a las suscripciones del usuario.
     */
    public function test(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);

        $enviadas = $this->webPush->sendToUser(
            $request->user(),
            '🔔 Notificación de prueba',
            'Las notificaciones push de Casual Bar están funcionando.',
            ['url' => '/pedidos', 'tag' => 'test'],
        );

        return response()->json([
            'ok' => true,
            'enviadas' => $enviadas,
        ]);
    }

    private function authorizeStaff(Request $request): void
    {
        abort_unless($request->user()->isStaff(), 403, 'Solo el personal puede recibir notificaciones push.');
    }
}
