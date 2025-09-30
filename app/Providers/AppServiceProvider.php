<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register bindings/services here
    }

    public function boot(): void
    {
        // App-level bootstrapping goes here
        // Note: User observer is registered in EventServiceProvider
    }
}
