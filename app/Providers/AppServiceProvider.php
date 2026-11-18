<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (isset($_ENV['VERCEL']) || getenv('VERCEL')) {
            $this->app->useStoragePath('/tmp/storage');
        }
    }

    public function boot(): void
    {
        if ($this->app->environment('production') || isset($_ENV['VERCEL'])) {
            URL::forceScheme('https');
        }
    }
}
