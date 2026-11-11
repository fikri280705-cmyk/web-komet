<?php
/**
 * Router lokal untuk PHP built-in server.
 * Jalankan: php -S localhost:8000 -t public public/server.php
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$file = __DIR__ . $uri;

if ($uri !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
