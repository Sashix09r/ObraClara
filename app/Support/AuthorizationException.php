<?php

declare(strict_types=1);

namespace ObraClara\Support;

use RuntimeException;

/**
 * Pedido autenticado mas sem permissão para a operação.
 */
final class AuthorizationException extends RuntimeException
{
    public function __construct(string $message = 'Não tem permissão para esta operação.')
    {
        parent::__construct($message);
    }
}
