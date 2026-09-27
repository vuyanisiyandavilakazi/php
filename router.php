<?php
/**
 * Only used with `php -S host:port router.php` for local preview/testing.
 * Real hosting (Apache/Awardspace) uses .htaccess instead — see that file.
 * Emulates PATH_INFO for /api.php/x/y style calls and serves static files.
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri) && !str_ends_with($uri, '.php')) {
    return false; // let the built-in server serve the static file directly
}

if (str_starts_with($uri, '/api.php')) {
    $_SERVER['PATH_INFO'] = substr($uri, strlen('/api.php')) ?: '/';
    require __DIR__ . '/api.php';
    return true;
}

foreach (['s' => 'slug', 'p' => 'id', 'u' => 'handle'] as $prefix => $paramName) {
    if (preg_match('#^/' . $prefix . '/([^/]+)/?$#', $uri, $m)) {
        $_GET[$paramName] = $m[1];
        require __DIR__ . "/{$prefix}.php";
        return true;
    }
}

if ($uri === '/' || $uri === '') {
    require __DIR__ . '/index.php';
    return true;
}

// Direct .php request, e.g. /login.php
if (str_ends_with($uri, '.php') && file_exists(__DIR__ . $uri)) {
    require __DIR__ . $uri;
    return true;
}

$candidate = __DIR__ . $uri . '.php';
if (file_exists($candidate)) {
    require $candidate;
    return true;
}
$candidateIndex = __DIR__ . $uri . '/index.php';
if (file_exists($candidateIndex)) {
    require $candidateIndex;
    return true;
}

http_response_code(404);
echo "404 Not Found: {$uri}";
return true;
