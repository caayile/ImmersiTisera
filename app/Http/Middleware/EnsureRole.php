<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->isActive()) {
            auth()->logout();

            return redirect()->route('login')->with('error', 'Akun dinonaktifkan.');
        }

        $normalized = $user->isParticipant() ? 'participant' : $user->role;

        if (! in_array($normalized, $roles, true) && ! in_array($user->role, $roles, true)) {
            abort(403, 'Akses tidak diizinkan untuk peran ini.');
        }

        return $next($request);
    }
}
