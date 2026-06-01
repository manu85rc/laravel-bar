<?php

namespace App\Providers;

use App\Models\Branding;
use Exception;
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
            $branding = Branding::getInstance();
        } catch (Exception $e) {
            // If table doesn't exist yet (during migration) or any other DB issue
            $branding = new Branding;
            $branding->app_name = 'Laravel Bar';
            $branding->logo_path = null;
            $branding->favicon_path = null;
            $branding->font_family = null;
            $branding->primary_color = '#6366f1';
            $branding->background_color = '#f9fafb';
        }

        View::share('branding', $branding);
    }
}
