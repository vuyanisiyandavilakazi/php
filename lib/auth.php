<?php
/**
 * Port of the *server-visible* half of src/lib/store.ts's useAuth.
 *
 * Real mode: auth is a cookie set directly by api.squids.co.za on login;
 * PHP just forwards the browser's Cookie header on every proxied call and
 * asks GET /api/users/me who that resolves to.
 *
 * Demo mode (see lib/demo_data.php): sq_api_get() is intercepted entirely,
 * so this same function works unchanged — it just gets canned data back.
 */

function sq_current_user(): ?array {
    static $cached = false;
    if ($cached !== false) return $cached;

    $res = sq_api_get('/api/users/me');
    if ($res['ok'] && is_array($res['data']) && !empty($res['data']['ok'])) {
        $cached = $res['data']['user'] ?? null;
    } else {
        $cached = null;
    }
    return $cached;
}

function sq_is_logged_in(): bool {
    return sq_current_user() !== null;
}
