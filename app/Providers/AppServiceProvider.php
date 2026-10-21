<?php

namespace App\Providers;

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
        // Implementación anterior: Livewire usa por defecto "layouts::app",
        // pero este proyecto no registra ese namespace de vistas.
        config(['livewire.component_layout' => 'components.layouts.app']);
    }
}
