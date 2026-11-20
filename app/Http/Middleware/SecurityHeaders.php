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
        $nonce = $this->cspNonce();
        $response = $next($request);

        $this->addNonceToInlineScripts($response, $nonce);

        $imageSources = ["'self'", 'data:', 'blob:', 'https:'];
        $fontSources = ["'self'", 'data:', 'https://fonts.gstatic.com', 'https://cdnjs.cloudflare.com'];

        // reCAPTCHA v2 memuat script dan iframe dari dua origin Google ini.
        $recaptchaSources = ['https://www.google.com/recaptcha/', 'https://www.gstatic.com/recaptcha/'];

        // Alpine v3 mengevaluasi ekspresi directive (x-show, @click) saat runtime, jadi butuh unsafe-eval.
        $scriptSources = ["'self'", "'nonce-{$nonce}'", "'unsafe-eval'", 'https://cdn.jsdelivr.net', ...$recaptchaSources];
        $styleSources = ["'self'", "'unsafe-inline'", 'https://fonts.googleapis.com', 'https://cdnjs.cloudflare.com'];
        $connectSources = ["'self'", ...$recaptchaSources];

        if (app()->environment('local')) {
            $viteOrigins = $this->viteOrigins();

            foreach ($viteOrigins as $viteOrigin) {
                $imageSources[] = $viteOrigin;
                $fontSources[] = $viteOrigin;
                $scriptSources[] = $viteOrigin;
                $styleSources[] = $viteOrigin;
                $connectSources[] = $viteOrigin;
                $connectSources[] = str_replace('http://', 'ws://', $viteOrigin);
            }
        }

        $policies = [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            'img-src '.implode(' ', $imageSources),
            'font-src '.implode(' ', $fontSources),
            'style-src '.implode(' ', $styleSources),
            'script-src '.implode(' ', $scriptSources),
            "script-src-attr 'unsafe-inline'",
            'connect-src '.implode(' ', $connectSources),
            'frame-src '.implode(' ', ["'self'", 'https://www.google.com', 'https://maps.google.com', ...$recaptchaSources]),
        ];

        if (! app()->environment('local')) {
            $policies[] = 'upgrade-insecure-requests';
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Content-Security-Policy', implode('; ', $policies));

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * Stable nonce derived from APP_KEY so cached HTML keeps a valid nonce.
     */
    private function cspNonce(): string
    {
        return base64_encode(hash_hmac('sha256', 'csp-nonce', (string) config('app.key'), true));
    }

    /**
     * Vite origin bawaan plus origin aktif dari public/hot (port bisa berubah saat restart).
     *
     * @return array<int, string>
     */
    private function viteOrigins(): array
    {
        $origins = ['http://localhost:5173', 'http://127.0.0.1:5173'];
        $hotFile = public_path('hot');

        if (! is_file($hotFile)) {
            return $origins;
        }

        $hotUrl = trim((string) file_get_contents($hotFile));

        if ($hotUrl === '' || filter_var($hotUrl, FILTER_VALIDATE_URL) === false) {
            return $origins;
        }

        $parts = parse_url($hotUrl);

        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'], $parts['port'])) {
            return $origins;
        }

        $origin = sprintf('%s://%s:%d', $parts['scheme'], $parts['host'], $parts['port']);

        if (! in_array($origin, $origins, true)) {
            $origins[] = $origin;
        }

        return $origins;
    }

    private function addNonceToInlineScripts(Response $response, string $nonce): void
    {
        if (! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return;
        }

        $html = $response->getContent();

        if (! is_string($html) || $html === '') {
            return;
        }

        $withNonce = preg_replace(
            '/<script\b(?![^>]*\bsrc\s*=)(?![^>]*\bnonce\s*=)/i',
            '<script nonce="'.$nonce.'"',
            $html,
        );

        if (is_string($withNonce) && $withNonce !== $html) {
            $original = property_exists($response, 'original') ? $response->original : null;

            $response->setContent($withNonce);

            if (property_exists($response, 'original')) {
                $response->original = $original;
            }
        }
    }
}
