<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $imageSources = ["'self'", 'data:', 'blob:', 'https:'];
        $fontSources = ["'self'", 'data:', 'https://fonts.gstatic.com', 'https://cdnjs.cloudflare.com'];
        $scriptSources = ["'self'", "'unsafe-inline'", "'unsafe-eval'", 'https://cdn.jsdelivr.net'];
        $styleSources = ["'self'", "'unsafe-inline'", 'https://fonts.googleapis.com', 'https://cdnjs.cloudflare.com'];
        $connectSources = ["'self'"];

        if (app()->environment('local')) {
            $viteOrigins = ['http://localhost:5173', 'http://127.0.0.1:5173'];

            foreach ($viteOrigins as $viteOrigin) {
                $imageSources[] = $viteOrigin;
                $fontSources[] = $viteOrigin;
                $scriptSources[] = $viteOrigin;
                $styleSources[] = $viteOrigin;
                $connectSources[] = $viteOrigin;
                $connectSources[] = str_replace('http://', 'ws://', $viteOrigin);
            }
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            'img-src '.implode(' ', $imageSources),
            'font-src '.implode(' ', $fontSources),
            'style-src '.implode(' ', $styleSources),
            'script-src '.implode(' ', $scriptSources),
            'connect-src '.implode(' ', $connectSources),
            "frame-src 'self' https://www.google.com https://maps.google.com",
        ]));

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
