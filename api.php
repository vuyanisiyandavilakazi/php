<?php
/**
 * Port of src/app/api/[...path]/route.ts
 * Generic signed reverse-proxy to api.squids.co.za, reused by shell.js the
 * same way client code called Next.js's /api/* catch-all — just point
 * fetch() at /api.php/whatever instead of /api/whatever.
 *
 * Path is taken from PATH_INFO (needs a rewrite rule on real hosting —
 * see .htaccess in this folder; PHP's built-in dev server needs router.php,
 * also included, since it has no .htaccess support).
 *
 * In demo mode (lib/demo_data.php) this transparently returns canned data
 * via sq_api_fetch(), so the whole UI works with zero real backend.
 */

require_once __DIR__ . '/lib/internalSign.php';
require_once __DIR__ . '/lib/geo.php';
require_once __DIR__ . '/lib/api.php';

$pathInfo = $_SERVER['PATH_INFO'] ?? '';
$pathInfo = '/' . ltrim($pathInfo, '/');
$pathname = '/api' . $pathInfo;
$qs = $_SERVER['QUERY_STRING'] ?? '';
$fullPath = $pathname . ($qs ? "?{$qs}" : '');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$body = null;
if (!in_array($method, ['GET', 'HEAD'], true)) {
    $raw = file_get_contents('php://input');
    $body = $raw ? json_decode($raw, true) : null;
}

$res = sq_api_fetch($method, $fullPath, $body);

http_response_code($res['status'] ?: 200);
header('Content-Type: application/json');
echo json_encode($res['data']);
