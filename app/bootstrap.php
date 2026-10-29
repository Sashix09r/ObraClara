<?php

declare(strict_types=1);

use ObraClara\Support\Database;

defined('OBRACLARA_ROOT') || define('OBRACLARA_ROOT', dirname(__DIR__));

require OBRACLARA_ROOT . '/app/Support/helpers.php';

/**
 * Autoload pequeno e explícito: ObraClara\Support\Database -> app/Support/Database.php.
 * Sem Composer, para manter a instalação simples no Laragon.
 */
spl_autoload_register(static function (string $class): void {
    $prefix = 'ObraClara\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $path = OBRACLARA_ROOT . '/app/' . $relative . '.php';

    if (is_file($path)) {
        require $path;
    }
});

$config = require OBRACLARA_ROOT . '/config/config.php';

date_default_timezone_set($config['timezone']);

Database::init($config['db_path']);

/** Devolve um valor da configuração carregada no arranque. */
function config(string $key, mixed $default = null): mixed
{
    global $config;

    return $config[$key] ?? $default;
}
