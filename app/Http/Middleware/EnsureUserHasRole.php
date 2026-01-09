<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Sudah pasti lewat auth middleware, tapi kita amankan
        if (!$user) {
            abort(401);
        }

        // role_label harus ada dan cocok
        if (!$user->role_label || !in_array($user->role_label, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
