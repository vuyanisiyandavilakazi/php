<?php
/**
 * Port of src/lib/internalSign.ts
 * Signs requests to api.squids.co.za the same way the Next.js app did:
 * HMAC-SHA256 over "METHOD:PATHNAME:TIMESTAMP", hex-encoded.
 */

function sq_signed_headers(string $method, string $pathname, array $extra = []): array {
    $secret = getenv('SQ_INTERNAL_SECRET') ?: ($_ENV['SQ_INTERNAL_SECRET'] ?? null);

    if (!$secret || $secret === 'REPLACE_WITH_LOCAL_DEV_SECRET') {
        error_log('[internalSign] SQ_INTERNAL_SECRET is missing or still the local-dev placeholder — signed requests to api.squids.co.za will 401. Set a real value in your hosting env and make sure PHP can read it.');
        return $extra;
    }

    $ts  = (string) round(microtime(true) * 1000); // match Date.now() (ms)
    $sig = hash_hmac('sha256', "{$method}:{$pathname}:{$ts}", $secret);

    return array_merge($extra, [
        'X-Sq-Timestamp' => $ts,
        'X-Sq-Signature' => $sig,
    ]);
}
