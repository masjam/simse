<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and attach strong security headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // 1. Clickjacking protection
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // 2. MIME sniffing protection
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 3. Referrer privacy policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 4. Hardware and sensor permissions policy (Camera & Geolocation restricted to self for Kiosk/RFID/Attendance)
        $response->headers->set('Permissions-Policy', 'camera=(self), geolocation=(self), microphone=()');

        // 5. Cross-Origin Embedder / Opener Policies
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // 6. Content Security Policy (Report-Only in non-production, enforced in production)
        $cspDirectives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://challenges.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
            "font-src 'self' https://fonts.bunny.net data:",
            "img-src 'self' data: blob:",
            "connect-src 'self' ws: wss: https://challenges.cloudflare.com",
            "frame-src 'self' https://challenges.cloudflare.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        $cspHeader = implode('; ', $cspDirectives);

        if (app()->isProduction()) {
            $response->headers->set('Content-Security-Policy', $cspHeader);

            if ($request->isSecure()) {
                $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            }
        } else {
            $response->headers->set('Content-Security-Policy-Report-Only', $cspHeader);
        }

        return $response;
    }
}
