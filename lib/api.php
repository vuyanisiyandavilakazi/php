<?php
/**
 * Port of src/lib/api.ts
 * Talks to api.squids.co.za directly (server-side cURL) instead of going
 * through a same-origin /api rewrite, since PHP has no Next.js rewrite
 * layer. Same retry-on-5xx behaviour, same timeout, same signed headers.
 */

require_once __DIR__ . '/internalSign.php';

const SQ_API_BASE = 'https://api.squids.co.za';
const SQ_MEDIA_BASE = 'https://api.squids.co.za/media'; // adjust if media is served from elsewhere

/**
 * Low-level signed request with one retry on 5xx / network failure,
 * mirroring fetchWithRetry() + apiFetch() in api.ts.
 *
 * @param string $method   GET/POST/etc
 * @param string $path     e.g. '/users/me' (NOT prefixed with /api)
 * @param array|null $body  will be JSON-encoded if present
 * @param string|null $token bearer token, if the user is authenticated
 * @param array $extraHeaders
 * @return array{ok: bool, status: int, data: mixed}
 */
function sq_api_fetch(string $method, string $path, ?array $body = null, ?string $token = null, array $extraHeaders = []): array {
    require_once __DIR__ . '/demo_data.php';
    if (SQ_DEMO_MODE) {
        return sq_demo_api_response($method, $path, $body);
    }

    $method = strtoupper($method);
    $url = SQ_API_BASE . $path;

    $headers = [
        'Accept: application/json',
        'X-Requested-With: XMLHttpRequest',
    ];
    if ($body !== null) $headers[] = 'Content-Type: application/json';
    if ($token) $headers[] = 'Authorization: Bearer ' . $token;
    // Forward the browser's own session cookie so server-rendered pages see
    // the same logged-in state the client-side app would (see lib/auth.php).
    if (!empty($_SERVER['HTTP_COOKIE'])) $headers[] = 'Cookie: ' . $_SERVER['HTTP_COOKIE'];

    // HMAC signature, same as the Next.js proxy added server-side
    $signed = sq_signed_headers($method, $path);
    foreach ($signed as $k => $v) $headers[] = "{$k}: {$v}";
    foreach ($extraHeaders as $k => $v) $headers[] = "{$k}: {$v}";

    $maxRetries = 1;
    $lastError = null;

    for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            $lastError = $err ?: 'Request failed';
            if ($attempt < $maxRetries) { usleep(500000 * ($attempt + 1)); continue; }
            return ['ok' => false, 'status' => 0, 'data' => ['ok' => false, 'error' => $lastError]];
        }

        if ($status >= 500 && $attempt < $maxRetries) {
            usleep(500000 * ($attempt + 1));
            continue;
        }

        $decoded = json_decode($raw, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            $decoded = ['ok' => false, 'error' => "HTTP {$status}"];
        }
        return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'data' => $decoded];
    }

    return ['ok' => false, 'status' => 0, 'data' => ['ok' => false, 'error' => $lastError ?? 'Request failed']];
}

function sq_api_get(string $path, ?string $token = null): array {
    return sq_api_fetch('GET', $path, null, $token);
}

function sq_api_post(string $path, ?array $body = null, ?string $token = null): array {
    return sq_api_fetch('POST', $path, $body, $token);
}

/**
 * Multipart form POST (port of apiFormPost). $fields may contain
 * CURLFile instances for file uploads.
 */
function sq_api_form_post(string $path, array $fields, ?string $token = null): array {
    $url = SQ_API_BASE . $path;
    $headers = ['Accept: application/json', 'X-Requested-With: XMLHttpRequest'];
    if ($token) $headers[] = 'Authorization: Bearer ' . $token;
    $signed = sq_signed_headers('POST', $path);
    foreach ($signed as $k => $v) $headers[] = "{$k}: {$v}";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POSTFIELDS     => $fields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
    ]);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($raw ?: '', true);
    if ($decoded === null) $decoded = ['ok' => false, 'error' => "HTTP {$status}"];
    return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'data' => $decoded];
}

// ---- Media / CDN URL helpers (port of the cdnUrl family in api.ts) ----

function sq_cdn_url(string $cid, int $w = 800, int $h = 600): string {
    if ($cid === '') return '';
    if (str_starts_with($cid, 'http')) return $cid;
    return SQ_MEDIA_BASE . "/c_fill,w_{$w},h_{$h}/{$cid}";
}

function sq_cdn_url_fit(string $cid, int $w = 1400, int $h = 1400): string {
    if ($cid === '') return '';
    if (str_starts_with($cid, 'http')) return $cid;
    return SQ_MEDIA_BASE . "/c_limit,w_{$w},h_{$h}/{$cid}";
}

function sq_avatar_url(string $cid, int $size = 96): string {
    if ($cid === '') return '/assets/avatar.svg';
    if (str_starts_with($cid, 'http')) return $cid;
    return SQ_MEDIA_BASE . "/c_fill,w_{$size},h_{$size},g_face/{$cid}";
}

function sq_comm_icon_url(string $cid, int $size = 64): string {
    if ($cid === '') return '/assets/community.svg';
    if (str_starts_with($cid, 'http')) return $cid;
    return SQ_MEDIA_BASE . "/c_fill,w_{$size},h_{$size}/{$cid}";
}

function sq_comm_banner_url(string $cid): string {
    if ($cid === '') return '';
    if (str_starts_with($cid, 'http')) return $cid;
    return SQ_MEDIA_BASE . "/c_fill,w_1600,h_640/{$cid}";
}

function sq_video_url(string $cid): string {
    if ($cid === '') return '';
    if (str_starts_with($cid, 'http')) return $cid;
    return SQ_MEDIA_BASE . "/v/{$cid}";
}

function sq_video_poster_url(string $cid, int $size = 160): string {
    if ($cid === '') return '';
    if (str_starts_with($cid, 'http')) return $cid;
    return SQ_MEDIA_BASE . "/v/f_jpg,q_auto,so_0,c_fill,w_{$size},h_{$size}/{$cid}";
}
