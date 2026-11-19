<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorSetup
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->isAdmin()
            && session('two_factor_setup_required')
            && ! $request->routeIs('two-factor.*')) {
            return redirect()->route('two-factor.setup');
        }

        return $next($request);
    }
}
