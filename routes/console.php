<?php

use App\Models\Reserva;
use App\Services\NotificacionService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('reservas:clean-past', function () {
    $deleted = Reserva::where('fecha_hora', '<', now()->subMonth())->delete();
    $this->info("{$deleted} reserva(s) de más de un mes de antigüedad eliminada(s).");
})->purpose('Elimina las reservas con más de un mes de antigüedad');

Artisan::command('notificaciones:limpiar {--dias=30 : Días de antigüedad para purgar}', function () {
    $dias = (int) $this->option('dias');

    $resultado = app(NotificacionService::class)->purgar($dias);

    $this->info("Notificaciones purgadas (antigüedad > {$dias} días): "
        ."{$resultado['leidas']} leída(s), {$resultado['no_leidas']} no leída(s), "
        ."{$resultado['huerfanas']} de usuario(s) eliminado(s), "
        ."{$resultado['pedidos']} de pedido(s) eliminado(s).");
})->purpose('Purga notificaciones antiguas, huérfanas y de pedidos eliminados');
