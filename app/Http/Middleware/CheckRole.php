<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        // If no role matches, redirect based on user's role
        if ($user->hasRole('Admin')) {
            return redirect()->route('home');
        } elseif ($user->hasRole('Guru')) {
            return redirect()->route('homeguru');
        } elseif ($user->hasRole('Murid')) {
            return redirect()->route('dashboard');
        }

        return redirect('/Beranda')->with('error', 'Akses ditolak.');
    }
}
