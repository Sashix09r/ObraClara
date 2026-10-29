<?php

declare(strict_types=1);

namespace ObraClara\Support;

use RuntimeException;

/**
 * Erros de validação por campo, apresentados junto ao formulário.
 */
final class ValidationException extends RuntimeException
{
    /** @param array<string, string> $errors */
    public function __construct(private readonly array $errors)
    {
        parent::__construct('Existem campos por corrigir.');
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }
}
