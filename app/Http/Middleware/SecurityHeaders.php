<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request dan inject security headers
     * untuk protect against common vulnerabilities seperti:
     * XSS, Clickjacking, MIME sniffing, dan enforce HTTPS
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // X-Frame-Options: prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // X-Content-Type-Options: prevent MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // X-XSS-Protection: enable XSS filtering (legacy browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer-Policy: control referrer information
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Strict-Transport-Security: enforce HTTPS (hanya untuk production)
        if (config('app.env') === 'production') {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        // Content-Security-Policy: prevent XSS dan data injection attacks
        // Note: Customize sesuai kebutuhan aplikasi
        // Untuk development, allow Vite dev server (localhost:5173)
        $viteDevServer = config('app.env') === 'local' ? ' http://localhost:5173' : '';

        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'{$viteDevServer}; ".
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'{$viteDevServer}; ".
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net{$viteDevServer}; ".
            "img-src 'self' data: https:; ".
            "font-src 'self' data: https://fonts.bunny.net; ".
            "connect-src 'self'{$viteDevServer} ws://localhost:5173"
        );

        return $response;
    }
}
