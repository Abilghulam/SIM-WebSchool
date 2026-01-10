<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // kalau belum login, biarin auth middleware yang handle
        if (!$user) {
            return $next($request);
        }

        if (!$user->must_change_password) {
            return $next($request);
        }

        // whitelist route yang boleh diakses saat wajib ganti password
        if (
            $request->routeIs('password.change') ||
            $request->routeIs('password.change.update') ||
            $request->routeIs('logout') ||
            $request->routeIs('profile.*') // opsional: boleh akses profile
        ) {
            return $next($request);
        }

        return redirect()->route('password.change')
            ->with('warning', 'Kamu harus mengganti password sebelum melanjutkan.');
    }
}
