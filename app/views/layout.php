<?php

declare(strict_types=1);

/**
 * Layout principal da aplicação autenticada.
 *
 * @var string $content
 * @var string $title
 * @var array<string, mixed>|null $user
 * @var string $nav
 * @var list<array{type: string, message: string}> $flash
 */

$isManager = $user !== null && $user['role'] === 'gestor';
$navigation = [
    ['key' => 'dashboard', 'label' => 'Painel', 'href' => '/', 'icon' => 'dashboard'],
    ['key' => 'clients', 'label' => 'Clientes', 'href' => '/clientes', 'icon' => 'clients'],
    ['key' => 'projects', 'label' => 'Obras', 'href' => '/obras', 'icon' => 'projects'],
    ['key' => 'history', 'label' => 'Histórico', 'href' => '/historico', 'icon' => 'history'],
];
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> · ObraClara</title>
    <link rel="icon" href="<?= e(asset('favicon.svg')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="app">
<a class="skip-link" href="#conteudo">Saltar para o conteúdo</a>

<div class="shell">
    <aside class="sidebar" id="sidebar" data-sidebar>
        <a class="brand" href="<?= e(url('/')) ?>">
            <span class="brand__mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round">
                    <path d="M4 20V9.5L12 4l8 5.5V20"/>
                    <path d="M9.5 20v-5.5h5V20"/>
                </svg>
            </span>
            <span class="brand__text">
                <strong>ObraClara</strong>
                <small>Gestão de obras</small>
            </span>
        </a>

        <p class="sidebar__label">Menu</p>
        <nav class="nav" aria-label="Navegação principal">
            <?php foreach ($navigation as $item): ?>
                <a class="nav__link<?= $nav === $item['key'] ? ' is-active' : '' ?>"
                   href="<?= e(url($item['href'])) ?>"
                   <?= $nav === $item['key'] ? 'aria-current="page"' : '' ?>>
                    <?= icon($item['icon']) ?>
                    <span><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar__footer">
            <?php if ($user !== null): ?>
                <div class="user-card">
                    <span class="avatar" aria-hidden="true"><?= e(initials((string) $user['name'])) ?></span>
                    <span class="user-card__info">
                        <strong><?= e($user['name']) ?></strong>
                        <span class="badge <?= $isManager ? 'badge--manager' : 'badge--staff' ?>">
                            <?= $isManager ? 'Gestor' : 'Colaborador' ?>
                        </span>
                    </span>
                    <form method="post" action="<?= e(url('/logout')) ?>" class="user-card__logout">
                        <?= csrf_field() ?>
                        <button type="submit" class="logout-btn" title="Terminar sessão">
                            <?= icon('logout') ?><span>Sair</span>
                        </button>
                    </form>
                </div>
                <p class="sidebar__note">Projeto académico · dados fictícios</p>
            <?php else: ?>
                <a class="btn btn--ghost btn--block" href="<?= e(url('/login')) ?>">Entrar</a>
            <?php endif; ?>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <button class="icon-btn nav-toggle" type="button" data-nav-toggle
                    aria-controls="sidebar" aria-expanded="false" aria-label="Abrir menu">
                <?= icon('menu') ?>
            </button>
            <p class="topbar__context"><?= e($user !== null ? 'Sessão de ' . $user['name'] : 'ObraClara') ?></p>
            <p class="topbar__date"><?= e(today_label()) ?></p>
        </header>

        <main class="content" id="conteudo">
            <?php foreach ($flash as $message): ?>
                <div class="alert alert--<?= $message['type'] === 'success' ? 'success' : 'error' ?>" role="status">
                    <span class="alert__icon" aria-hidden="true">
                        <?= icon($message['type'] === 'success' ? 'check' : 'alert') ?>
                    </span>
                    <p><?= e($message['message']) ?></p>
                    <button type="button" class="alert__close" data-dismiss aria-label="Fechar mensagem">
                        <?= icon('close') ?>
                    </button>
                </div>
            <?php endforeach; ?>

            <?= $content ?>
        </main>

        <footer class="footer">
            <span>ObraClara V2 — aplicação local de demonstração</span>
            <span>PHP <?= e(PHP_VERSION) ?> · SQLite</span>
        </footer>
    </div>
</div>

<div class="nav-backdrop" data-nav-backdrop hidden></div>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
