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

        // PENANGANAN HTTPS YANG LEBIH KUAT
        // Menggunakan environment() bawaan Laravel alih-alih env()
        if ($this->app->environment('production', 'staging')) {
            // Memaksa semua URL yang di-generate Laravel (route, asset) pakai HTTPS
            URL::forceScheme('https');

            // Opsional tapi sangat dianjurkan:
            // Memaksa request agar dikenali sebagai HTTPS (sangat berguna jika pakai Cloudflare/Proxy)
            request()->server->set('HTTPS', 'on');
        }
    }
}
