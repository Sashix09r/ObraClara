<?php

declare(strict_types=1);

/** Ecrã mostrado quando a base de dados ainda não foi criada. */
?><!doctype html>
<html lang="<?= e(\ObraClara\Support\Lang::htmlLocale()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(t('errors.setup_title')) ?> · ObraClara</title>
    <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>">
</head>
<body class="auth">
<main class="setup-card card">
    <p class="eyebrow"><?= e(t('errors.setup_kicker')) ?></p>
    <h1><?= e(t('errors.setup_title')) ?></h1>
    <p><?= e(t('errors.setup_text')) ?></p>
    <pre><code>php scripts/install.php
php scripts/seed.php --reset
php -S localhost:8000 -t public public/router.php</code></pre>
    <p class="text-muted"><?= e(t('errors.setup_after')) ?></p>
</main>
</body>
</html>
