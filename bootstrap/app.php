<?php
// bootstrap/app.php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware; // <-- Ensure this is imported
use Illuminate\Foundation\Configuration\Exceptions;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', 
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register your custom route middleware here
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'update.last.activity' => \App\Http\Middleware\UpdateLastActivity::class,
        ]);

        // Append to web middleware stack so it runs on every web request
        $middleware->web(append: [
            \App\Http\Middleware\UpdateLastActivity::class,
        ]);

        // If you have any global middleware, they'd go here for api stack as well if needed.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();