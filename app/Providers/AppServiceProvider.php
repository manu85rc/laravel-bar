<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Branding;
use Illuminate\Support\Facades\View;
use Exception;

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
            $branding = new Branding();
            $branding->app_name = 'Laravel Bar';
            $branding->logo_path = null;
            $branding->favicon_path = null;
        }
        
        View::share('branding', $branding);
    }
}
