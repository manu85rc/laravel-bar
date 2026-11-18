<?php

namespace App\Providers;

use App\Models\Branding;
use App\Models\Pedido;
use App\Models\ReservationSetting;
use App\Observers\PedidoObserver;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            $timezone = DB::table('reservation_settings')->value('timezone');
        } catch (Exception $e) {
            $timezone = null;
        }

        $timezone = $timezone ?: ReservationSetting::DEFAULT_TIMEZONE;
        date_default_timezone_set($timezone);
        config(['app.timezone' => $timezone]);

        try {
            $branding = Branding::getInstance();
        } catch (Exception $e) {
            // If table doesn't exist yet (during migration) or any other DB issue
            $branding = new Branding;
            $branding->app_name = 'Laravel Bar';
            $branding->logo_path = null;
            $branding->favicon_path = null;
            $branding->font_family = null;
            $branding->primary_color = '#0e0d0b';
            $branding->background_color = '#0e0d0b';
        }

        View::share('branding', $branding);

        // Notificaciones: el observer avisará cuando un pedido pasa a 'listo'.
        Pedido::observe(PedidoObserver::class);
    }
}
