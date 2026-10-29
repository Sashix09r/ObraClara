<?php

declare(strict_types=1);

namespace ObraClara\Support;

use RuntimeException;

/**
 * Operação pedida viola uma regra de negócio (ex.: decidir duas vezes).
 */
final class BusinessRuleException extends RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
