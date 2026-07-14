<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityHeaders Middleware
 *
 * Injecte les headers HTTP de sécurité sur chaque réponse afin de protéger
 * contre les attaques XSS, clickjacking, MIME sniffing et injection de contenu.
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // ── 1. Empêche le clickjacking ────────────────────────────────────────
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // ── 2. Empêche le MIME-type sniffing ──────────────────────────────────
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // ── 3. Contrôle les informations du Referer ───────────────────────────
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // ── 4. Protection XSS legacy pour vieux navigateurs ───────────────────
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // ── 5. Désactive les fonctionnalités inutiles du navigateur ───────────
        $response->headers->set(
            'Permissions-Policy',
            'microphone=(), geolocation=(), payment=(), usb=()'
        );

        // ── 6. Content Security Policy ────────────────────────────────────────
        // Autorise : sources propres + Google Fonts (polices) + Vite HMR en dev uniquement + jsdelivr pour zxing-wasm
        $isLocal  = app()->environment('local');
        $appUrl   = rtrim(config('app.url', ''), '/');

        // En dev : on ajoute les URLs HMR Vite (websocket + http)
        $viteWs = $isLocal
            ? " ws://localhost:5173 http://localhost:5173 ws://127.0.0.1:5173 http://127.0.0.1:5173"
            : '';

        // En production : on s'assure que le domaine applicatif est explicitement autorisé
        $prodOrigin = (!$isLocal && $appUrl) ? " {$appUrl}" : '';

        $response->headers->set(
            'Content-Security-Policy',
            implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'wasm-unsafe-eval' https://fastly.jsdelivr.net{$viteWs}",   // unsafe-inline requis pour Inertia/Vite, jsdelivr pour zxing, wasm-unsafe-eval pour WebAssembly
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
                "font-src 'self' https://fonts.gstatic.com data:",
                "img-src 'self' data: blob:",
                "connect-src 'self' https://fastly.jsdelivr.net{$prodOrigin}{$viteWs}",
                "frame-ancestors 'self'",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
            ])
        );

        // ── 7. HSTS (uniquement en production HTTPS) ──────────────────────────
        if (!$isLocal && $request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // ── 8. Supprime le header "X-Powered-By" révélateur ──────────────────
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
