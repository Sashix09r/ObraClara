<?php

declare(strict_types=1);

namespace ObraClara\Repositories;

use ObraClara\Support\Database;
use ObraClara\Support\NotFoundException;

final class ProjectRepository
{
    /**
     * Orçamento atualizado = base + soma das alterações APROVADAS.
     * A soma é feita em cêntimos inteiros diretamente em SQL.
     */
    private const SELECT = 'SELECT p.*, c.name AS client_name, c.contact AS client_contact,
               COALESCE((
                   SELECT SUM(ch.amount_cents) FROM changes ch
                    WHERE ch.project_id = p.id AND ch.status = \'aprovada\'
               ), 0) AS approved_cents
          FROM projects p
          INNER JOIN clients c ON c.id = p.client_id';

    /**
     * @return list<array<string, mixed>>
     */
    public static function search(string $query = '', string $status = ''): array
    {
        $sql = self::SELECT . ' WHERE 1 = 1';
        $params = [];

        if ($query !== '') {
            $sql .= ' AND (p.title LIKE :q OR c.name LIKE :q OR p.description LIKE :q)';
            $params['q'] = '%' . $query . '%';
        }

        if ($status === 'arquivada') {
            $sql .= ' AND p.status = \'arquivada\'';
        } elseif ($status !== '' && in_array($status, self::statuses(), true)) {
            $sql .= ' AND p.status = :status';
            $params['status'] = $status;
        } else {
            $sql .= ' AND p.status <> \'arquivada\'';
        }

        $sql .= " ORDER BY CASE p.status WHEN 'em_curso' THEN 0 WHEN 'planeada' THEN 1 WHEN 'concluida' THEN 2 ELSE 3 END,
                          p.updated_at DESC";

        $statement = Database::pdo()->prepare($sql);
        $statement->execute($params);

        return array_map([self::class, 'withBudget'], $statement->fetchAll());
    }

    /** @return array<string, mixed> */
    public static function find(int $id): array
    {
        $statement = Database::pdo()->prepare(self::SELECT . ' WHERE p.id = :id');
        $statement->execute(['id' => $id]);
        $project = $statement->fetch();

        if (!$project) {
            throw new NotFoundException(t('validation.project_not_found'));
        }

        return self::withBudget($project);
    }

    /** @return list<array<string, mixed>> */
    public static function forClient(int $clientId, bool $includeArchived = false): array
    {
        $sql = self::SELECT . ' WHERE p.client_id = :client_id';
        if (!$includeArchived) {
            $sql .= " AND p.status <> 'arquivada'";
        }
        $sql .= ' ORDER BY p.updated_at DESC';

        $statement = Database::pdo()->prepare($sql);
        $statement->execute(['client_id' => $clientId]);

        return array_map([self::class, 'withBudget'], $statement->fetchAll());
    }

    /** @param array<string, mixed> $data */
    public static function create(array $data): int
    {
        $statement = Database::pdo()->prepare(
            'INSERT INTO projects
                (client_id, title, description, base_budget_cents, start_date, status, created_by, created_at, updated_at)
             VALUES
                (:client_id, :title, :description, :base_budget_cents, :start_date, :status, :created_by, :created_at, :updated_at)'
        );
        $statement->execute([
            'client_id'         => $data['client_id'],
            'title'             => $data['title'],
            'description'       => $data['description'],
            'base_budget_cents' => $data['base_budget_cents'],
            'start_date'        => $data['start_date'],
            'status'            => $data['status'],
            'created_by'        => $data['created_by'],
            'created_at'        => $data['created_at'],
            'updated_at'        => $data['updated_at'],
        ]);

        return (int) Database::pdo()->lastInsertId();
    }

    /** @param array<string, mixed> $data */
    public static function update(int $id, array $data): void
    {
        $statement = Database::pdo()->prepare(
            'UPDATE projects
                SET client_id = :client_id, title = :title, description = :description,
                    base_budget_cents = :base_budget_cents, start_date = :start_date,
                    status = :status, updated_at = :updated_at
              WHERE id = :id'
        );
        $statement->execute([
            'client_id'         => $data['client_id'],
            'title'             => $data['title'],
            'description'       => $data['description'],
            'base_budget_cents' => $data['base_budget_cents'],
            'start_date'        => $data['start_date'],
            'status'            => $data['status'],
            'updated_at'        => $data['updated_at'],
            'id'                => $id,
        ]);
    }

    public static function setStatus(int $id, string $status): void
    {
        $statement = Database::pdo()->prepare(
            'UPDATE projects SET status = :status, updated_at = :updated_at WHERE id = :id'
        );
        $statement->execute(['status' => $status, 'updated_at' => now(), 'id' => $id]);
    }

    public static function pendingChangeCount(int $id): int
    {
        $statement = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM changes WHERE project_id = :id AND status = \'pendente\''
        );
        $statement->execute(['id' => $id]);

        return (int) $statement->fetchColumn();
    }

    /** @return list<string> */
    public static function statuses(): array
    {
        return ['planeada', 'em_curso', 'concluida', 'arquivada'];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private static function withBudget(array $row): array
    {
        $row['approved_cents'] = (int) $row['approved_cents'];
        $row['updated_budget_cents'] = (int) $row['base_budget_cents'] + (int) $row['approved_cents'];

        return $row;
    }
}
