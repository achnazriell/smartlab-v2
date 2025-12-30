<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\URL;

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
        // Set locale Carbon
        Carbon::setLocale('id');

        // Alias middleware role
        $this->app['router']->aliasMiddleware('role', RoleMiddleware::class);

        // Gunakan Tailwind untuk pagination
        Paginator::useTailwind();

        // Paksa HTTPS di production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
