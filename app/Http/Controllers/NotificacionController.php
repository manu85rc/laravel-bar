<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints JSON de la campana de notificaciones.
 *
 * Todos los endpoints trabajan SIEMPRE sobre
 * `$request->user()->notifications()`: ningún usuario puede leer, marcar
 * ni borrar notificaciones de otro usuario.
 */
class NotificacionController extends Controller
{
    /**
     * Lista las notificaciones del usuario + contador de no leídas.
     */
    public function index(Request $request): JsonResponse
    {
        $limite = $request->integer('limite', 15);
        $limite = max(1, min($limite, 50));

        $notificaciones = $request->user()->notifications()
            ->latest()
            ->limit($limite)
            ->get()
            ->map(fn ($notificacion) => [
                'id' => $notificacion->id,
                'titulo' => data_get($notificacion->data, 'titulo', 'Notificación'),
                'mensaje' => data_get($notificacion->data, 'mensaje'),
                'detalle' => data_get($notificacion->data, 'detalle'),
                'icono' => data_get($notificacion->data, 'icono', 'fa-solid fa-bell'),
                'color' => data_get($notificacion->data, 'color', 'oro'),
                'url' => data_get($notificacion->data, 'url'),
                'leida' => $notificacion->read_at !== null,
                'created_at' => optional($notificacion->created_at)->toIso8601String(),
            ]);

        return response()->json([
            'no_leidas' => $request->user()->notificacionesNoLeidas(),
            'notificaciones' => $notificaciones,
        ]);
    }

    /**
     * Marca una notificación como leída.
     */
    public function leer(Request $request, string $notificacion): JsonResponse
    {
        $item = $request->user()->notifications()->whereKey($notificacion)->first();

        if (! $item) {
            return response()->json(['ok' => false, 'mensaje' => 'Notificación no encontrada.'], 404);
        }

        if (! $item->read_at) {
            $item->markAsRead();
        }

        return response()->json([
            'ok' => true,
            'no_leidas' => $request->user()->notificacionesNoLeidas(),
        ]);
    }

    /**
     * Marca todas las notificaciones del usuario como leídas.
     */
    public function leerTodas(Request $request): JsonResponse
    {
        // Un único UPDATE: no carga todas las filas en memoria.
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'ok' => true,
            'no_leidas' => 0,
        ]);
    }

    /**
     * Elimina una notificación del usuario.
     */
    public function destroy(Request $request, string $notificacion): JsonResponse
    {
        $borradas = $request->user()->notifications()->whereKey($notificacion)->delete();

        if (! $borradas) {
            return response()->json(['ok' => false, 'mensaje' => 'Notificación no encontrada.'], 404);
        }

        return response()->json([
            'ok' => true,
            'no_leidas' => $request->user()->notificacionesNoLeidas(),
        ]);
    }

    /**
     * Vacía la campana del usuario (borra todas sus notificaciones).
     */
    public function limpiar(Request $request): JsonResponse
    {
        $borradas = $request->user()->notifications()->delete();

        return response()->json([
            'ok' => true,
            'borradas' => $borradas,
            'no_leidas' => 0,
        ]);
    }
}
