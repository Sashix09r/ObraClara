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
        $statement->execute(['email' => mb_strtolower(trim($email))]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    /** @return list<array<string, mixed>> */
    public static function search(string $query = ''): array
    {
        $sql = 'SELECT id, name, email, role, active, locale, tour_completed_at, last_login_at, created_at
                  FROM users WHERE 1 = 1';
        $params = [];

        if ($query !== '') {
            $sql .= ' AND (name LIKE :q OR email LIKE :q)';
            $params['q'] = '%' . $query . '%';
        }

        $sql .= " ORDER BY active DESC, CASE role WHEN 'gestor' THEN 0 ELSE 1 END, name COLLATE NOCASE";

        $statement = Database::pdo()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public static function all(): array
    {
        return Database::pdo()
            ->query('SELECT id, name, email, role, active, locale, created_at FROM users ORDER BY name')
            ->fetchAll();
    }

    /** @param array<string, mixed> $data */
    public static function create(array $data): int
    {
        $statement = Database::pdo()->prepare(
            'INSERT INTO users (name, email, password_hash, role, active, locale, created_at, updated_at)
             VALUES (:name, :email, :password_hash, :role, 1, :locale, :created_at, :updated_at)'
        );
        $statement->execute([
            'name'          => $data['name'],
            'email'         => mb_strtolower($data['email']),
            'password_hash' => $data['password_hash'],
            'role'          => $data['role'],
            'locale'        => $data['locale'] ?? 'pt',
            'created_at'    => $data['created_at'],
            'updated_at'    => $data['created_at'],
        ]);

        return (int) Database::pdo()->lastInsertId();
    }

    public static function updatePassword(int $id, string $hash): void
    {
        $statement = Database::pdo()->prepare(
            'UPDATE users SET password_hash = :hash, updated_at = :updated_at WHERE id = :id'
        );
        $statement->execute(['hash' => $hash, 'updated_at' => now(), 'id' => $id]);
    }

    public static function updateProfile(int $id, string $name, string $email, string $locale): void
    {
        $statement = Database::pdo()->prepare(
            'UPDATE users SET name = :name, email = :email, locale = :locale, updated_at = :updated_at WHERE id = :id'
        );
        $statement->execute([
            'name'       => $name,
            'email'      => mb_strtolower($email),
            'locale'     => $locale,
            'updated_at' => now(),
            'id'         => $id,
        ]);
    }

    public static function setRole(int $id, string $role): void
    {
        $statement = Database::pdo()->prepare(
            'UPDATE users SET role = :role, updated_at = :updated_at WHERE id = :id'
        );
        $statement->execute(['role' => $role, 'updated_at' => now(), 'id' => $id]);
    }

    public static function setActive(int $id, bool $active): void
    {
        $statement = Database::pdo()->prepare(
            'UPDATE users SET active = :active, updated_at = :updated_at WHERE id = :id'
        );
        $statement->execute(['active' => $active ? 1 : 0, 'updated_at' => now(), 'id' => $id]);
    }

    public static function setLocale(int $id, string $locale): void
    {
        $statement = Database::pdo()->prepare('UPDATE users SET locale = :locale WHERE id = :id');
        $statement->execute(['locale' => $locale, 'id' => $id]);
    }

    public static function markLogin(int $id): void
    {
        $statement = Database::pdo()->prepare(
            'UPDATE users SET last_login_at = :last_login_at WHERE id = :id'
        );
        $statement->execute(['last_login_at' => now(), 'id' => $id]);
    }

    public static function markTourCompleted(int $id): void
    {
        $statement = Database::pdo()->prepare(
            'UPDATE users SET tour_completed_at = :tour_completed_at WHERE id = :id'
        );
        $statement->execute(['tour_completed_at' => now(), 'id' => $id]);
    }

    public static function activeManagerCount(): int
    {
        return (int) Database::pdo()
            ->query("SELECT COUNT(*) FROM users WHERE role = 'gestor' AND active = 1")
            ->fetchColumn();
    }

    public static function isActiveManager(int $id): bool
    {
        $statement = Database::pdo()->prepare(
            "SELECT COUNT(*) FROM users WHERE id = :id AND role = 'gestor' AND active = 1"
        );
        $statement->execute(['id' => $id]);

        return (int) $statement->fetchColumn() === 1;
    }
}
