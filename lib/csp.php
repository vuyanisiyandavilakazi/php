<?php
/**
 * Port of the buildCsp() function in src/middleware.ts.
 * Call sq_send_security_headers() once at the top of every page, before
 * any output, and use sq_csp_nonce() on every <script> tag you write.
 */

function sq_csp_nonce(): string {
    static $nonce = null;
    if ($nonce === null) {
        $nonce = base64_encode(random_bytes(16));
    }
    return $nonce;
}

function sq_build_csp(string $nonce): string {
    $directives = [
        "default-src 'self'",
        "script-src 'self' 'nonce-{$nonce}' 'strict-dynamic' https://api.squids.co.za https://static.cloudflareinsights.com https://www.googletagmanager.com https://cdn.mxpnl.com https://us-assets.i.posthog.com",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
        "img-src 'self' data: blob: https://res.cloudinary.com https://api.squids.co.za https://lh3.googleusercontent.com https://www.onlinegames.io https://img.youtube.com https://i.ytimg.com https://www.google-analytics.com https://www.googletagmanager.com",
        "media-src 'self' https://res.cloudinary.com https://api.squids.co.za",
        "font-src 'self' data: https://fonts.gstatic.com",
        "connect-src 'self' https://api.squids.co.za https://static.cloudflareinsights.com https://cloudflareinsights.com https://sqgames.netlify.app https://www.google-analytics.com https://*.google-analytics.com https://*.analytics.google.com https://us.i.posthog.com https://us-assets.i.posthog.com https://api-eu.mixpanel.com https://api.mixpanel.com https://cdn.mxpnl.com",
        "frame-src 'self' https://squids.co.za https://sqgames.netlify.app",
        "object-src 'none'",
        "base-uri 'self'",
        "form-action 'self'",
    ];
    return implode('; ', $directives);
}

function sq_send_security_headers(): void {
    if (headers_sent()) return;
    $nonce = sq_csp_nonce();
    header('Content-Security-Policy: ' . sq_build_csp($nonce));
    header('X-Sq-Device: ' . sq_get_device());
}
