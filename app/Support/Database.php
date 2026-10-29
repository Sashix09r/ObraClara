<?php

declare(strict_types=1);

namespace ObraClara\Support;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Ligação única (por pedido) à base de dados SQLite.
 */
final class Database
{
    private static ?PDO $pdo = null;

    private static string $path = '';

    public static function init(string $path): void
    {
        self::$path = $path;
        self::$pdo = null;
    }

    public static function path(): string
    {
        return self::$path;
    }

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        if (self::$path === '') {
            throw new RuntimeException('A base de dados não foi configurada.');
        }

        if (!is_dir(dirname(self::$path))) {
            mkdir(dirname(self::$path), 0775, true);
        }

        try {
            $pdo = new PDO('sqlite:' . self::$path, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException('Não foi possível abrir a base de dados.', 0, $exception);
        }

        // Integridade referencial obrigatória em TODAS as ligações.
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 5000');

        self::$pdo = $pdo;

        return $pdo;
    }

    /** Substitui a ligação (usado pelos testes com base descartável). */
    public static function setPdo(?PDO $pdo): void
    {
        self::$pdo = $pdo;
    }
}
