<?php

declare(strict_types=1);

namespace ObraClara\Support;

use RuntimeException;

/**
 * Registo inexistente ou ID inválido.
 */
final class NotFoundException extends RuntimeException
{
    public function __construct(string $message = 'O registo pedido não existe.')
    {
        parent::__construct($message);
    }
}
