<?php

declare(strict_types=1);

namespace ObraClara\Support;

/**
 * Proteção CSRF: um token por sessão, comparado em tempo constante.
 * Todos os pedidos POST são verificados no front controller.
 */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function check(?string $token): bool
    {
        $expected = $_SESSION['csrf_token'] ?? null;

        if (!is_string($expected) || $expected === '' || !is_string($token) || $token === '') {
            return false;
        }

        return hash_equals($expected, $token);
    }

    public static function rotate(): void
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
