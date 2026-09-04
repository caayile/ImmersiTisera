<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isVerified()) {
            return response()->json([
                'message' => 'Akun belum diverifikasi admin.',
                'verification_status' => $user?->verification_status,
            ], 403);
        }

        return $next($request);
    }
}
