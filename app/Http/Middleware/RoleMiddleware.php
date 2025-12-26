<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect('/login'); // kalau belum login, redirect ke halaman login
        }

        // Cek apakah role user sesuai dengan yang diminta
        if (Auth::user()->role !== $role) {
            abort(403, 'Unauthorized'); // kalau role beda, tampilkan error 403
        }

        // Kalau lolos semua cek, lanjutkan request
        return $next($request);
    }
}
