<?php

declare(strict_types=1);

namespace ObraClara\Repositories;

use ObraClara\Support\Database;

final class InviteRepository
{
    private const SELECT = 'SELECT i.*, creator.name AS creator_name, used.name AS used_by_name
                              FROM invites i
                              INNER JOIN users creator ON creator.id = i.created_by
                              LEFT JOIN users used ON used.id = i.used_by';

    /** @param array<string, mixed> $data */
    public static function create(array $data): int
    {
        $statement = Database::pdo()->prepare(
            'INSERT INTO invites (email, role, token_hash, expires_at, created_by, created_at)
             VALUES (:email, :role, :token_hash, :expires_at, :created_by, :created_at)'
        );
        $statement->execute([
            'email'      => mb_strtolower($data['email']),
            'role'       => $data['role'],
            'token_hash' => $data['token_hash'],
            'expires_at' => $data['expires_at'],
            'created_by' => $data['created_by'],
            'created_at' => $data['created_at'],
        ]);

        return (int) Database::pdo()->lastInsertId();
    }

    /** @return array<string, mixed>|null */
    public static function findByHash(string $tokenHash): ?array
    {
        $statement = Database::pdo()->prepare(self::SELECT . ' WHERE i.token_hash = :hash');
        $statement->execute(['hash' => $tokenHash]);
        $invite = $statement->fetch();

        return $invite ?: null;
    }

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        $statement = Database::pdo()->prepare(self::SELECT . ' WHERE i.id = :id');
        $statement->execute(['id' => $id]);
        $invite = $statement->fetch();

        return $invite ?: null;
    }

    /** @return array<string, mixed>|null */
    public static function pendingForEmail(string $email): ?array
    {
        $statement = Database::pdo()->prepare(
            self::SELECT . ' WHERE i.email = :email COLLATE NOCASE
                             AND i.used_at IS NULL AND i.revoked_at IS NULL
                             AND i.expires_at > :now
                             ORDER BY i.created_at DESC LIMIT 1'
        );
        $statement->execute(['email' => mb_strtolower(trim($email)), 'now' => now()]);
        $invite = $statement->fetch();

        return $invite ?: null;
    }

    /** @return list<array<string, mixed>> */
    public static function recent(int $limit = 40): array
    {
        $statement = Database::pdo()->prepare(
            self::SELECT . ' ORDER BY i.created_at DESC, i.id DESC LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public static function pending(int $limit = 20): array
    {
        $statement = Database::pdo()->prepare(
            self::SELECT . ' WHERE i.used_at IS NULL AND i.revoked_at IS NULL AND i.expires_at > :now
                             ORDER BY i.expires_at ASC LIMIT :limit'
        );
        $statement->bindValue('now', now());
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function markUsed(int $id, int $userId): void
    {
        $statement = Database::pdo()->prepare(
            'UPDATE invites SET used_at = :used_at, used_by = :used_by
              WHERE id = :id AND used_at IS NULL AND revoked_at IS NULL'
        );
        $statement->execute(['used_at' => now(), 'used_by' => $userId, 'id' => $id]);
    }

    public static function revoke(int $id): bool
    {
        $statement = Database::pdo()->prepare(
            'UPDATE invites SET revoked_at = :revoked_at
              WHERE id = :id AND used_at IS NULL AND revoked_at IS NULL'
        );
        $statement->execute(['revoked_at' => now(), 'id' => $id]);

        return $statement->rowCount() === 1;
    }

    public static function countPending(): int
    {
        $statement = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM invites
              WHERE used_at IS NULL AND revoked_at IS NULL AND expires_at > :now'
        );
        $statement->execute(['now' => now()]);

        return (int) $statement->fetchColumn();
    }
}
