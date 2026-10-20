<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        // Tamu (guest) ditangani oleh middleware 'auth'.
        // Di sini satpam fokus memeriksa kecocokan peran dan keaktifan akun.
        if ($user === null || ! $user->hasRole($role)) {
            abort(403);
        }

        return $next($request);
    }
}
