<?php

declare(strict_types=1);

namespace ObraClara\Support;

/**
 * Rótulos em português e classes CSS para estados e ações.
 * O estado é sempre apresentado com texto, nunca apenas com cor.
 */
final class Labels
{
    public static function changeStatus(string $status): string
    {
        return match ($status) {
            'rascunho' => t('status.draft'),
            'pendente' => t('status.pending'),
            'aprovada' => t('status.approved'),
            'rejeitada' => t('status.rejected'),
            default    => $status,
        };
    }

    public static function projectStatus(string $status): string
    {
        return match ($status) {
            'planeada'  => t('status.planned'),
            'em_curso'  => t('status.in_progress'),
            'concluida' => t('status.completed'),
            'arquivada' => t('status.archived'),
            default     => $status,
        };
    }

    public static function eventAction(string $action): string
    {
        return match ($action) {
            'criada'     => t('status.created'),
            'editada'    => t('status.edited'),
            'submetida'  => t('status.submitted'),
            'aprovada'   => t('status.approved'),
            'rejeitada'  => t('status.rejected'),
            default      => $action,
        };
    }

    public static function eventClass(string $action): string
    {
        return match ($action) {
            'aprovada'  => 'badge--approved',
            'rejeitada' => 'badge--rejected',
            'submetida' => 'badge--pending',
            'editada'   => 'badge--neutral',
            default     => 'badge--draft',
        };
    }
}
