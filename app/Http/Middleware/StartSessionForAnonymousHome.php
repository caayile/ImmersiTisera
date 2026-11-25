<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Symfony\Component\HttpFoundation\Response;

class StartSessionForAnonymousHome extends StartSession
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if ($request->isMethod('GET') && $request->path() === '/' && ! $request->hasCookie(config('session.cookie'))) {
            $request->setLaravelSession(app('session')->driver('array'));

            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
