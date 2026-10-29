<?php

declare(strict_types=1);

/**
 * Router do servidor integrado do PHP.
 * Uso: php -S localhost:8000 -t public public/router.php
 * Serve ficheiros estáticos existentes; tudo o resto vai para index.php.
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = __DIR__ . $path;

if ($path !== '/' && is_file($file) && !str_ends_with($file, '.php')) {
    return false;
}

require __DIR__ . '/index.php';
