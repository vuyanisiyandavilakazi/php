<?php
/**
 * Include this at the very top of every page, before any output.
 * Sets up session, security headers, device detection, and pulls in
 * the shared lib functions every page will need.
 */

declare(strict_types=1);

// No PHP session needed for auth — the app uses a cookie set directly by
// api.squids.co.za, which we just forward. See lib/auth.php for details.

require_once __DIR__ . '/device.php';
require_once __DIR__ . '/geo.php';
require_once __DIR__ . '/csp.php';
require_once __DIR__ . '/internalSign.php';
require_once __DIR__ . '/api.php';
require_once __DIR__ . '/auth.php';

sq_send_security_headers();

$SQ_DEVICE = sq_get_device();
$SQ_NONCE  = sq_csp_nonce();
$SQ_USER   = sq_current_user(); // null if guest
