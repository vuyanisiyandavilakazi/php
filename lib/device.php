<?php
/**
 * Port of src/lib/device.ts + the device-sniffing part of middleware.ts.
 * Next.js did this in middleware and passed it down via header/cookie.
 * In PHP we just compute it directly per-request — same result, one less hop.
 */

const SQ_MOBILE_UA_RE = '/Android|iPhone|iPod|Windows Phone|BlackBerry|IEMobile|Opera Mini/i';
const SQ_TABLET_UA_RE = '/iPad|Android(?!.*Mobile)/i';

function sq_get_device(): string {
    static $device = null;
    if ($device !== null) return $device;

    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $isTablet = preg_match(SQ_TABLET_UA_RE, $ua) === 1;
    $isMobile = !$isTablet && preg_match(SQ_MOBILE_UA_RE, $ua) === 1;

    $device = $isMobile ? 'mobile' : ($isTablet ? 'tablet' : 'desktop');

    // Mirror the cookie the old middleware set, so it's consistent if you
    // ever add client-side reads of it (e.g. via document.cookie).
    if (!headers_sent()) {
        setcookie('sq_device', $device, [
            'expires'  => time() + 86400,
            'path'     => '/',
            'samesite' => 'Lax',
            'secure'   => true,
        ]);
    }
    return $device;
}

function sq_is_mobile_device(): bool {
    return sq_get_device() === 'mobile';
}
