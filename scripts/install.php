<?php

declare(strict_types=1);

/**
 * Cria a base de dados SQLite e aplica o esquema e as migrações.
 * Uso: php scripts/install.php
 * Idempotente: não apaga dados existentes e faz cópia antes de migrar.
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use ObraClara\Support\Database;
use ObraClara\Support\Migrator;

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDERR, "ERRO: a extensão pdo_sqlite não está ativa no PHP.\n");
    exit(1);
}

$path = Database::path();
$isNew = !is_file($path);
$pdo = Database::pdo();
$schema = file_get_contents(OBRACLARA_ROOT . '/database/schema.sql');

if ($schema === false || trim($schema) === '') {
    fwrite(STDERR, "ERRO: database/schema.sql não encontrado ou vazio.\n");
    exit(1);
}

// Instalação nova: criar tudo. Instalação existente: nunca recriar tabelas.
if ($isNew) {
    $pdo->exec($schema);
}

$pending = Migrator::pending($pdo);
$backup = null;

if (!$isNew && $pending !== []) {
    $backupDir = dirname($path) . '/backups';
    if (!is_dir($backupDir) && !mkdir($backupDir, 0775, true) && !is_dir($backupDir)) {
        fwrite(STDERR, "ERRO: não foi possível criar {$backupDir}.\n");
        exit(1);
    }

    $backup = $backupDir . '/obraclara-' . date('Ymd-His') . '.sqlite';
    if (!copy($path, $backup)) {
        fwrite(STDERR, "ERRO: não foi possível criar a cópia de segurança.\n");
        exit(1);
    }
}

$executed = Migrator::run($pdo);

$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
    ->fetchAll(PDO::FETCH_COLUMN);

echo ($isNew ? 'Base de dados criada: ' : 'Esquema verificado: ') . $path . PHP_EOL;
echo 'Tabelas: ' . implode(', ', $tables) . PHP_EOL;

if ($backup !== null) {
    echo 'Cópia de segurança antes das migrações: ' . $backup . PHP_EOL;
}

echo 'Migrações aplicadas nesta execução: ' . ($executed === [] ? 'nenhuma (já estava atualizado)' : implode(', ', $executed)) . PHP_EOL;

$users = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($users === 0) {
    echo PHP_EOL . 'A base está vazia. Para criar os dados de demonstração:' . PHP_EOL;
    echo '  php scripts/seed.php --reset' . PHP_EOL;
}
