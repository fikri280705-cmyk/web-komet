<?php
/**
 * Router untuk PHP built-in server (php -S).
 * File statis dilayani langsung; selain itu masuk ke front controller.
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$file = __DIR__ . $uri;

if ($uri !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
