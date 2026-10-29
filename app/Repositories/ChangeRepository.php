<?php

declare(strict_types=1);

namespace ObraClara\Repositories;

use ObraClara\Support\Database;
use ObraClara\Support\NotFoundException;

final class ChangeRepository
{
    private const SELECT = 'SELECT ch.*, p.title AS project_title, p.status AS project_status,
               p.client_id, cl.name AS client_name,
               author.name AS author_name,
               decider.name AS decider_name
          FROM changes ch
          INNER JOIN projects p ON p.id = ch.project_id
          INNER JOIN clients cl ON cl.id = p.client_id
          INNER JOIN users author ON author.id = ch.created_by
          LEFT JOIN users decider ON decider.id = ch.decided_by';

    /** @return list<array<string, mixed>> */
    public static function forProject(int $projectId): array
    {
        $statement = Database::pdo()->prepare(
            self::SELECT . ' WHERE ch.project_id = :project_id
                            ORDER BY CASE ch.status WHEN \'pendente\' THEN 0 WHEN \'rascunho\' THEN 1 ELSE 2 END,
                                     ch.created_at DESC'
        );
        $statement->execute(['project_id' => $projectId]);

        return $statement->fetchAll();
    }

    /** @return array<string, mixed> */
    public static function find(int $id): array
    {
        $statement = Database::pdo()->prepare(self::SELECT . ' WHERE ch.id = :id');
        $statement->execute(['id' => $id]);
        $change = $statement->fetch();

        if (!$change) {
            throw new NotFoundException('A alteração indicada não existe.');
        }

        return $change;
    }

    /** @param array<string, mixed> $data */
    public static function create(array $data): int
    {
        $statement = Database::pdo()->prepare(
            'INSERT INTO changes
                (project_id, title, description, amount_cents, status, created_by, created_at, updated_at)
             VALUES
                (:project_id, :title, :description, :amount_cents, :status, :created_by, :created_at, :updated_at)'
        );
        $statement->execute([
            'project_id'   => $data['project_id'],
            'title'        => $data['title'],
            'description'  => $data['description'],
            'amount_cents' => $data['amount_cents'],
            'status'       => $data['status'],
            'created_by'   => $data['created_by'],
            'created_at'   => $data['created_at'],
            'updated_at'   => $data['updated_at'],
        ]);

        return (int) Database::pdo()->lastInsertId();
    }

    /** @param array<string, mixed> $data */
    public static function updateDraft(int $id, array $data): void
    {
        $statement = Database::pdo()->prepare(
            'UPDATE changes
                SET title = :title, description = :description, amount_cents = :amount_cents, updated_at = :updated_at
              WHERE id = :id AND status = \'rascunho\''
        );
        $statement->execute([
            'title'        => $data['title'],
            'description'  => $data['description'],
            'amount_cents' => $data['amount_cents'],
            'updated_at'   => $data['updated_at'],
            'id'           => $id,
        ]);
    }

    public static function markSubmitted(int $id): void
    {
        $statement = Database::pdo()->prepare(
            'UPDATE changes
                SET status = \'pendente\', submitted_at = :submitted_at, updated_at = :submitted_at
              WHERE id = :id AND status = \'rascunho\''
        );
        $statement->execute(['submitted_at' => now(), 'id' => $id]);
    }

    public static function markDecided(int $id, string $status, int $managerId, ?string $note): void
    {
        $statement = Database::pdo()->prepare(
            'UPDATE changes
                SET status = :status, decided_at = :decided_at, decided_by = :decided_by,
                    decision_note = :decision_note, updated_at = :decided_at
              WHERE id = :id AND status = \'pendente\''
        );
        $statement->execute([
            'status'        => $status,
            'decided_at'    => now(),
            'decided_by'    => $managerId,
            'decision_note' => $note,
            'id'            => $id,
        ]);
    }

    /** Soma aprovada das obras não arquivadas (coerente com o orçamento previsto). */
    public static function approvedTotal(): int
    {
        return (int) Database::pdo()
            ->query(
                "SELECT COALESCE(SUM(ch.amount_cents), 0)
                   FROM changes ch
                   INNER JOIN projects p ON p.id = ch.project_id
                  WHERE ch.status = 'aprovada' AND p.status <> 'arquivada'"
            )
            ->fetchColumn();
    }

    public static function countByStatus(string $status): int
    {
        $statement = Database::pdo()->prepare('SELECT COUNT(*) FROM changes WHERE status = :status');
        $statement->execute(['status' => $status]);

        return (int) $statement->fetchColumn();
    }

    /** @return list<array<string, mixed>> */
    public static function pending(int $limit = 8): array
    {
        $statement = Database::pdo()->prepare(
            self::SELECT . " WHERE ch.status = 'pendente'
                             ORDER BY ch.submitted_at ASC, ch.id ASC LIMIT :limit"
        );
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public static function recent(int $limit = 6): array
    {
        $statement = Database::pdo()->prepare(
            self::SELECT . ' ORDER BY ch.created_at DESC LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

}
