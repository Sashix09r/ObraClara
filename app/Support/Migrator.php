<?php

declare(strict_types=1);

namespace ObraClara\Support;

use PDO;
use RuntimeException;

/**
 * Migrações incrementais e idempotentes.
 *
 * A base existente nunca é recriada. Cada versão é registada em
 * schema_migrations e só corre uma vez. O install.php cria uma cópia de
 * segurança antes de aplicar versões pendentes.
 */
final class Migrator
{
    /** @return list<string> versões ainda não aplicadas */
    public static function pending(PDO $pdo): array
    {
        self::ensureRegistry($pdo);
        $applied = $pdo->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);

        return array_values(array_diff(array_keys(self::migrations()), $applied));
    }

    /** @return list<string> versões aplicadas nesta execução */
    public static function run(PDO $pdo): array
    {
        self::ensureRegistry($pdo);
        $applied = $pdo->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
        $executed = [];

        foreach (self::migrations() as $version => $callback) {
            if (in_array($version, $applied, true)) {
                continue;
            }

            $pdo->beginTransaction();

            try {
                $callback($pdo);
                $statement = $pdo->prepare(
                    'INSERT INTO schema_migrations (version, applied_at) VALUES (:version, :applied_at)'
                );
                $statement->execute(['version' => $version, 'applied_at' => now()]);
                $pdo->commit();
                $executed[] = $version;
            } catch (\Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw new RuntimeException('Falhou a migração ' . $version . ': ' . $exception->getMessage(), 0, $exception);
            }
        }

        return $executed;
    }

    private static function ensureRegistry(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS schema_migrations (
                version    TEXT PRIMARY KEY,
                applied_at TEXT NOT NULL
            )'
        );
    }

    /** @return array<string, callable(PDO): void> */
    private static function migrations(): array
    {
        return [
            '2026_10_29_001_preferencias_utilizador' => static function (PDO $pdo): void {
                self::addColumn($pdo, 'users', 'locale', "TEXT NOT NULL DEFAULT 'pt' CHECK (locale IN ('pt', 'en'))");
                self::addColumn($pdo, 'users', 'tour_completed_at', 'TEXT');
                self::addColumn($pdo, 'users', 'last_login_at', 'TEXT');
                self::addColumn($pdo, 'users', 'updated_at', 'TEXT');
                $pdo->exec('UPDATE users SET updated_at = created_at WHERE updated_at IS NULL');
                $pdo->exec('CREATE INDEX IF NOT EXISTS idx_users_role_active ON users (role, active)');
            },
            '2026_10_29_002_convites_e_auditoria' => static function (PDO $pdo): void {
                $schema = file_get_contents(OBRACLARA_ROOT . '/database/schema.sql');

                if ($schema === false || trim($schema) === '') {
                    throw new RuntimeException('database/schema.sql indisponível.');
                }

                $pdo->exec($schema);
            },
        ];
    }

    private static function addColumn(PDO $pdo, string $table, string $column, string $definition): void
    {
        $columns = $pdo->query('PRAGMA table_info(' . $table . ')')->fetchAll();
        foreach ($columns as $existing) {
            if (($existing['name'] ?? '') === $column) {
                return;
            }
        }

        $pdo->exec('ALTER TABLE ' . $table . ' ADD COLUMN ' . $column . ' ' . $definition);
    }
}
