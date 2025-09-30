<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use App\Models\Setting;

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

        // Dynamically apply timezone from settings (global scope)
        // Guard against early boot (before migrations) and cache lookups for performance
        try {
            if (Schema::hasTable('settings')) {
                $tz = Cache::rememberForever('settings.global.timezone', function () {
                    return Setting::whereNull('school_id')->value('timezone');
                });
                if (!empty($tz)) {
                    Config::set('app.timezone', $tz);
                    \date_default_timezone_set($tz);
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore to avoid breaking during installation/migrations
        }
    }
}
