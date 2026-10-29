<?php

declare(strict_types=1);

namespace ObraClara\Http\Controllers;

abstract class Controller
{
    protected function redirect(string $path): never
    {
        header('Location: ' . url($path));
        exit;
    }

    /** @param array<string, mixed> $params */
    protected function id(array $params, string $key = 'id'): int
    {
        $value = $params[$key] ?? null;

        if (!is_string($value) || !ctype_digit($value)) {
            throw new \ObraClara\Support\NotFoundException('Identificador inválido.');
        }

        return (int) $value;
    }
}
