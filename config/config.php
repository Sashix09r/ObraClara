<?php

declare(strict_types=1);

/**
 * Configuração da aplicação ObraClara V2.
 *
 * A base de dados fica intencionalmente FORA de public/ para nunca ser
 * servida pelo servidor web. `OBRACLARA_DB` permite usar uma base
 * descartável nos testes sem tocar nos dados de demonstração.
 */
return [
    'app_name'     => 'ObraClara',
    'db_path'      => getenv('OBRACLARA_DB') ?: dirname(__DIR__) . '/database/obraclara.sqlite',
    'timezone'     => 'Europe/Lisbon',
    'session_name' => 'obraclara_sess',
];
