<?php

declare(strict_types=1);

/**
 * Cria a base de dados SQLite e aplica o esquema.
 * Uso: php scripts/install.php
 * Idempotente: não apaga dados existentes.
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use ObraClara\Support\Database;

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDERR, "ERRO: a extensão pdo_sqlite não está ativa no PHP.\n");
    exit(1);
}

$path = Database::path();
$isNew = !is_file($path);

$schema = file_get_contents(OBRACLARA_ROOT . '/database/schema.sql');
if ($schema === false || trim($schema) === '') {
    fwrite(STDERR, "ERRO: database/schema.sql não encontrado ou vazio.\n");
    exit(1);
}

$pdo = Database::pdo();
$pdo->exec($schema);

$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
    ->fetchAll(PDO::FETCH_COLUMN);

echo ($isNew ? 'Base de dados criada: ' : 'Esquema verificado: ') . $path . PHP_EOL;
echo 'Tabelas: ' . implode(', ', $tables) . PHP_EOL;

$users = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($users === 0) {
    echo PHP_EOL . 'A base está vazia. Para criar os dados de demonstração:' . PHP_EOL;
    echo '  php scripts/seed.php --reset' . PHP_EOL;
}
