<?php

declare(strict_types=1);

namespace ObraClara\Repositories;

use ObraClara\Support\Database;

final class EventRepository
{
    public static function log(
        int $changeId,
        int $userId,
        string $action,
        ?string $fromStatus,
        string $toStatus,
        ?string $note = null
    ): void {
        $statement = Database::pdo()->prepare(
            'INSERT INTO change_events (change_id, user_id, action, from_status, to_status, note, created_at)
             VALUES (:change_id, :user_id, :action, :from_status, :to_status, :note, :created_at)'
        );
        $statement->execute([
            'change_id'   => $changeId,
            'user_id'     => $userId,
            'action'      => $action,
            'from_status' => $fromStatus,
            'to_status'   => $toStatus,
            'note'        => $note,
            'created_at'  => now(),
        ]);
    }

    /** @return list<array<string, mixed>> */
    public static function forChange(int $changeId): array
    {
        $statement = Database::pdo()->prepare(
            'SELECT ev.*, u.name AS user_name, u.role AS user_role
               FROM change_events ev
               INNER JOIN users u ON u.id = ev.user_id
              WHERE ev.change_id = :change_id
              ORDER BY ev.created_at ASC, ev.id ASC'
        );
        $statement->execute(['change_id' => $changeId]);

        return $statement->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public static function recent(int $limit = 80): array
    {
        $statement = Database::pdo()->prepare(
            'SELECT ev.*, u.name AS user_name, u.role AS user_role,
                    ch.title AS change_title, ch.amount_cents,
                    p.id AS project_id, p.title AS project_title,
                    cl.name AS client_name
               FROM change_events ev
               INNER JOIN users u ON u.id = ev.user_id
               INNER JOIN changes ch ON ch.id = ev.change_id
               INNER JOIN projects p ON p.id = ch.project_id
               INNER JOIN clients cl ON cl.id = p.client_id
              ORDER BY ev.created_at DESC, ev.id DESC
              LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public static function forProject(int $projectId): array
    {
        $statement = Database::pdo()->prepare(
            'SELECT ev.*, u.name AS user_name, ch.title AS change_title
               FROM change_events ev
               INNER JOIN users u ON u.id = ev.user_id
               INNER JOIN changes ch ON ch.id = ev.change_id
              WHERE ch.project_id = :project_id
              ORDER BY ev.created_at DESC, ev.id DESC
              LIMIT 40'
        );
        $statement->execute(['project_id' => $projectId]);

        return $statement->fetchAll();
    }
}
