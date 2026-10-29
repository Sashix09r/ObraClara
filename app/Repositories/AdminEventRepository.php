<?php

declare(strict_types=1);

namespace ObraClara\Repositories;

use ObraClara\Support\Database;

final class AdminEventRepository
{
    public const ACTIONS = [
        'convite_criado', 'convite_revogado', 'conta_criada', 'perfil_alterado',
        'palavra_passe_alterada', 'papel_alterado', 'conta_ativada',
        'conta_desativada', 'idioma_alterado',
    ];

    public static function log(int $actorId, string $action, string $targetType, int $targetId, ?string $note = null): void
    {
        if (!in_array($action, self::ACTIONS, true)) {
            throw new \InvalidArgumentException('Ação de auditoria inválida.');
        }

        $statement = Database::pdo()->prepare(
            'INSERT INTO admin_events (actor_id, action, target_type, target_id, note, created_at)
             VALUES (:actor_id, :action, :target_type, :target_id, :note, :created_at)'
        );
        $statement->execute([
            'actor_id'    => $actorId,
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'note'        => $note,
            'created_at'  => now(),
        ]);
    }

    /** @return list<array<string, mixed>> */
    public static function forTarget(string $type, int $id, int $limit = 20): array
    {
        $statement = Database::pdo()->prepare(
            'SELECT ev.*, u.name AS actor_name
               FROM admin_events ev
               INNER JOIN users u ON u.id = ev.actor_id
              WHERE ev.target_type = :type AND ev.target_id = :id
              ORDER BY ev.created_at DESC, ev.id DESC
              LIMIT :limit'
        );
        $statement->bindValue('type', $type);
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}
