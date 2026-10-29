<?php

declare(strict_types=1);

namespace ObraClara\Support;

/**
 * Mensagens de feedback guardadas entre redirecionamentos.
 */
final class Flash
{
    public static function add(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    public static function success(string $message): void
    {
        self::add('success', $message);
    }

    public static function error(string $message): void
    {
        self::add('error', $message);
    }

    /** @return list<array{type: string, message: string}> */
    public static function consume(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);

        return is_array($messages) ? $messages : [];
    }
}
