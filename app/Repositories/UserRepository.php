<?php

declare(strict_types=1);

namespace ObraClara\Repositories;

use ObraClara\Support\Database;

final class UserRepository
{
    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        $statement = Database::pdo()->prepare('SELECT * FROM users WHERE id = :id');
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    /** @return array<string, mixed>|null */
    public static function findByEmail(string $email): ?array
    {
        $statement = Database::pdo()->prepare('SELECT * FROM users WHERE email = :email COLLATE NOCASE');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public static function updatePassword(int $id, string $hash): void
    {
        $statement = Database::pdo()->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $statement->execute(['hash' => $hash, 'id' => $id]);
    }

    /** @return list<array<string, mixed>> */
    public static function all(): array
    {
        return Database::pdo()
            ->query('SELECT id, name, email, role, active FROM users ORDER BY name')
            ->fetchAll();
    }
}
