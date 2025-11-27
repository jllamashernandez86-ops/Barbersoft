<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        // Prevenir el escaneo innecesario de archivos en producción
        if ($this->app->isLocal()) {
            // Aumentar tiempo de ejecución en desarrollo
            set_time_limit(120);
        }
    }
}
