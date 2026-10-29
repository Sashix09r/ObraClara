<?php

declare(strict_types=1);

/** @var string $content */
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Entrar · ObraClara</title>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="auth">
<a class="skip-link" href="#conteudo">Saltar para o conteúdo</a>
<?= $content ?>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
