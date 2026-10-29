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
            'rascunho' => 'Rascunho',
            'pendente' => 'Pendente',
            'aprovada' => 'Aprovada',
            'rejeitada' => 'Rejeitada',
            default    => $status,
        };
    }

    public static function projectStatus(string $status): string
    {
        return match ($status) {
            'planeada'  => 'Planeada',
            'em_curso'  => 'Em curso',
            'concluida' => 'Concluída',
            'arquivada' => 'Arquivada',
            default     => $status,
        };
    }

    public static function eventAction(string $action): string
    {
        return match ($action) {
            'criada'     => 'Criada',
            'editada'    => 'Editada',
            'submetida'  => 'Submetida',
            'aprovada'   => 'Aprovada',
            'rejeitada'  => 'Rejeitada',
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
