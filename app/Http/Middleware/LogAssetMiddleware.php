<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Log;

class LogAssetMiddleware
{
    public function handle($request, Closure $next)
    {
        if (str_starts_with($request->path(), 'image/')) {
            Log::info('Asset request: ' . $request->fullUrl() . ' - File exists? ' . (file_exists(public_path($request->path())) ? 'yes' : 'no'));
        }
        return $next($request);
    }
}
