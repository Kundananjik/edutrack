<?php

/**
 * File: includes/security_headers.php
 * Purpose: Apply security-related HTTP headers globally.
 *  - Content-Security-Policy with per-request nonce support (script/style nonces)
 *  - Referrer-Policy, X-Content-Type-Options, X-Frame-Options, Permissions-Policy
 * Helpers:
 *  - et_csp_nonce(): string — current request nonce
 *  - et_csp_attr('script'|'style'): string — nonce attribute to inject into inline tags
 */

// Generate a per-request CSP nonce
if (!isset($GLOBALS['ET_CSP_NONCE'])) {
    $GLOBALS['ET_CSP_NONCE'] = rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=');
}

if (!function_exists('et_csp_nonce')) {
    function et_csp_nonce(): string
    {
        return (string)($GLOBALS['ET_CSP_NONCE'] ?? '');
    }
}
if (!function_exists('et_csp_attr')) {
    function et_csp_attr(string $tag = 'script'): string
    {
        $n = et_csp_nonce();
        return $n ? ('nonce="' . htmlspecialchars($n, ENT_QUOTES, 'UTF-8') . '"') : '';
    }
}

if (!headers_sent()) {
    $nonce = et_csp_nonce();

    // Content Security Policy tuned for common CDNs used in the app
    // Remove 'unsafe-inline' and permit inline via nonce
    $csp = [
        "default-src 'self'",
        "img-src 'self' data:",
        "style-src 'self' 'unsafe-inline' http://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
        "script-src 'self' 'nonce-{$nonce}' http://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com",
        "font-src 'self' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
        "connect-src 'self'",
        "object-src 'none'",
        "base-uri 'self'",
        "frame-ancestors 'self'"
    ];
    header('Content-Security-Policy: ' . implode('; ', $csp));

    // Additional security headers
    header('Referrer-Policy: same-origin');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}
