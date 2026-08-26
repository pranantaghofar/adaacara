<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SecurityHeadersFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        $response->removeHeader('X-Powered-By');
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        $response->setHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->setHeader('Permissions-Policy', implode(', ', [
            'accelerometer=()',
            'autoplay=(self)',
            'camera=()',
            'display-capture=()',
            'encrypted-media=()',
            'fullscreen=(self)',
            'geolocation=()',
            'gyroscope=()',
            'magnetometer=()',
            'microphone=()',
            'payment=()',
            'usb=()',
        ]));
        $response->setHeader('Content-Security-Policy', implode('; ', [
            "default-src 'self' https: data: blob:",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "form-action 'self' https:",
            "img-src 'self' https: data: blob:",
            "font-src 'self' https: data:",
            "style-src 'self' https: 'unsafe-inline'",
            "script-src 'self' https: blob: 'unsafe-inline' 'unsafe-eval'",
            "connect-src 'self' https: wss:",
            "media-src 'self' https: data: blob:",
            "frame-src 'self' https:",
            "worker-src 'self' blob:",
            "manifest-src 'self'",
            'upgrade-insecure-requests',
        ]));

        $response->setHeader('Strict-Transport-Security', 'max-age=31536000');
    }
}
