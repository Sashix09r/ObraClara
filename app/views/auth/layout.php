<?php

declare(strict_types=1);

/** @var string $content */
$title = $title ?? t('auth.login_title');
?><!doctype html>
<html lang="<?= e(\ObraClara\Support\Lang::htmlLocale()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> · ObraClara</title>
    <link rel="icon" href="<?= e(asset('favicon.svg')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="auth">
<a class="skip-link" href="#conteudo"><?= e(t('common.skip')) ?></a>
<?= $content ?>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
