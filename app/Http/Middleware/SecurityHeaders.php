<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://fonts.googleapis.com http://127.0.0.1:5173 http://localhost:5173; " .
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com https://cdn.jsdelivr.net http://127.0.0.1:5173 http://localhost:5173; " .
            "img-src 'self' data: blob: https://insp.rembangkab.go.id http://127.0.0.1:5173 http://localhost:5173; " .
            "font-src 'self' data: https://fonts.gstatic.com; " .
            "connect-src 'self' ws://127.0.0.1:5173 ws://localhost:5173 http://127.0.0.1:5173 http://localhost:5173; " .
            "frame-src 'self'; " .
            "object-src 'self'; " .
            "base-uri 'self'; " .
            "form-action 'self';"
        );

        return $response;
    }
}
