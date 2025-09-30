<?php

namespace App\Providers;

use Illuminate\Auth\Events\Failed;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Listeners\LogFailedLogin;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Failed::class => [
            LogFailedLogin::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
