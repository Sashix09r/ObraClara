<?php

declare(strict_types=1);

namespace ObraClara\Services;

use ObraClara\Repositories\ChangeRepository;
use ObraClara\Repositories\EventRepository;
use ObraClara\Repositories\ProjectRepository;
use ObraClara\Support\Database;

final class DashboardService
{
    /** @return array<string, mixed> */
    public static function stats(): array
    {
        $pdo = Database::pdo();

        $activeProjects = (int) $pdo
            ->query("SELECT COUNT(*) FROM projects WHERE status IN ('planeada', 'em_curso')")
            ->fetchColumn();

        $activeClients = (int) $pdo
            ->query('SELECT COUNT(*) FROM clients WHERE active = 1')
            ->fetchColumn();

        $forecast = (int) $pdo
            ->query(
                "SELECT COALESCE(SUM(p.base_budget_cents), 0)
                        + COALESCE((
                            SELECT SUM(ch.amount_cents)
                              FROM changes ch
                              INNER JOIN projects p2 ON p2.id = ch.project_id
                             WHERE ch.status = 'aprovada' AND p2.status <> 'arquivada'
                        ), 0)
                   FROM projects p
                  WHERE p.status <> 'arquivada'"
            )
            ->fetchColumn();

        return [
            'active_projects'  => $activeProjects,
            'active_clients'   => $activeClients,
            'pending_changes'  => ChangeRepository::countByStatus('pendente'),
            'draft_changes'    => ChangeRepository::countByStatus('rascunho'),
            'approved_extra'   => ChangeRepository::approvedTotal(),
            'forecast_total'   => $forecast,
            'pending'          => ChangeRepository::pending(8),
            'projects'         => array_slice(ProjectRepository::search('', ''), 0, 6),
            'events'           => EventRepository::recent(8),
        ];
    }
}
