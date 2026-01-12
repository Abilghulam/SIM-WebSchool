<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Jika belum login, biarkan lewat (auth middleware yang akan handle)
        if (!$user) {
            return $next($request);
        }

        // Jika akun dinonaktifkan
        if (!$user->is_active) {
            Auth::logout();

            // invalidate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['username' => 'Akun Anda dinonaktifkan. Silakan hubungi admin.']);
        }

        return $next($request);
    }
}
