<?php

declare(strict_types=1);

/** Ecrã mostrado quando a base de dados ainda não foi criada. */
?><!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instalação necessária · ObraClara</title>
    <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>">
</head>
<body class="auth">
<main class="setup-card card">
    <p class="eyebrow">Primeiro arranque</p>
    <h1>A base de dados ainda não existe</h1>
    <p>Executa estes comandos na pasta do projeto para criar o esquema e os dados de demonstração:</p>
    <pre><code>php scripts/seed.php
php -S localhost:8000 -t public public/router.php</code></pre>
    <p class="text-muted">Depois recarrega esta página e entra com uma conta de demonstração.</p>
</main>
</body>
</html>
