<?php

declare(strict_types=1);

namespace ObraClara\Repositories;

use ObraClara\Support\Database;

final class ClientRepository
{
    /**
     * Pesquisa por nome, email ou contacto, com contagem de obras associadas.
     *
     * @return list<array<string, mixed>>
     */
    public static function search(string $query, bool $includeArchived = false): array
    {
        $sql = 'SELECT c.*,
                       (SELECT COUNT(*) FROM projects p
                         WHERE p.client_id = c.id AND p.status <> \'arquivada\') AS active_projects
                  FROM clients c
                 WHERE 1 = 1';
        $params = [];

        if (!$includeArchived) {
            $sql .= ' AND c.active = 1';
        }

        if ($query !== '') {
            $sql .= ' AND (c.name LIKE :q OR c.email LIKE :q OR c.contact LIKE :q)';
            $params['q'] = '%' . $query . '%';
        }

        $sql .= ' ORDER BY c.active DESC, c.name COLLATE NOCASE';

        $statement = Database::pdo()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        $statement = Database::pdo()->prepare('SELECT * FROM clients WHERE id = :id');
        $statement->execute(['id' => $id]);
        $client = $statement->fetch();

        return $client ?: null;
    }

    /** @param array<string, mixed> $data */
    public static function create(array $data): int
    {
        $statement = Database::pdo()->prepare(
            'INSERT INTO clients (name, contact, email, notes, active, created_at, updated_at)
             VALUES (:name, :contact, :email, :notes, 1, :created_at, :updated_at)'
        );
        $statement->execute([
            'name'       => $data['name'],
            'contact'    => $data['contact'],
            'email'      => $data['email'],
            'notes'      => $data['notes'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
        ]);

        return (int) Database::pdo()->lastInsertId();
    }

    /** @param array<string, mixed> $data */
    public static function update(int $id, array $data): void
    {
        $statement = Database::pdo()->prepare(
            'UPDATE clients
                SET name = :name, contact = :contact, email = :email, notes = :notes, updated_at = :updated_at
              WHERE id = :id'
        );
        $statement->execute([
            'name'       => $data['name'],
            'contact'    => $data['contact'],
            'email'      => $data['email'],
            'notes'      => $data['notes'],
            'updated_at' => $data['updated_at'],
            'id'         => $id,
        ]);
    }

    public static function setActive(int $id, bool $active): void
    {
        $statement = Database::pdo()->prepare(
            'UPDATE clients SET active = :active, updated_at = :updated_at WHERE id = :id'
        );
        $statement->execute([
            'active'     => $active ? 1 : 0,
            'updated_at' => now(),
            'id'         => $id,
        ]);
    }

    public static function activeProjectCount(int $id): int
    {
        $statement = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM projects WHERE client_id = :id AND status <> \'arquivada\''
        );
        $statement->execute(['id' => $id]);

        return (int) $statement->fetchColumn();
    }

    /** @return list<array<string, mixed>> */
    public static function allActive(): array
    {
        return Database::pdo()
            ->query('SELECT id, name FROM clients WHERE active = 1 ORDER BY name COLLATE NOCASE')
            ->fetchAll();
    }
}
