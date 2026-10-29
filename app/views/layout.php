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
    ['key' => 'dashboard', 'label' => t('nav.dashboard'), 'href' => '/', 'icon' => 'dashboard'],
    ['key' => 'clients', 'label' => t('nav.clients'), 'href' => '/clientes', 'icon' => 'clients'],
    ['key' => 'projects', 'label' => t('nav.projects'), 'href' => '/obras', 'icon' => 'projects'],
    ['key' => 'history', 'label' => t('nav.history'), 'href' => '/historico', 'icon' => 'history'],
];
if ($isManager) {
    $navigation[] = ['key' => 'team', 'label' => t('nav.team'), 'href' => '/equipa', 'icon' => 'team'];
}

$path = current_path();
$tourPage = 'dashboard';
if (preg_match('#^/obras/\d+#', $path) === 1) {
    $tourPage = 'project';
} elseif (str_starts_with($path, '/equipa')) {
    $tourPage = 'team';
} elseif (str_starts_with($path, '/perfil')) {
    $tourPage = 'profile';
} elseif (str_starts_with($path, '/clientes')) {
    $tourPage = 'clients';
} elseif (str_starts_with($path, '/obras')) {
    $tourPage = 'projects';
} elseif (str_starts_with($path, '/historico')) {
    $tourPage = 'history';
}
$tourState = 'done';
if ($user !== null && ($user['tour_completed_at'] ?? null) === null) {
    $tourState = 'new';
}
$tourForced = isset($_GET['tour']) ? '1' : '0';
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
<body class="app"
      data-tour-state="<?= e($tourState) ?>"
      data-tour-page="<?= e($tourPage) ?>"
      data-tour-forced="<?= e($tourForced) ?>"
      data-csrf="<?= e(\ObraClara\Support\Csrf::token()) ?>"
      data-locale="<?= e(\ObraClara\Support\Lang::locale()) ?>"
      data-confirm-default="<?= e(t('common.confirm')) ?>"
      data-home="<?= e(url('/')) ?>"
      data-tour-done="<?= e(t('tour.completed')) ?>"
      data-tour-next="<?= e(t('tour.next')) ?>"
      data-tour-finish="<?= e(t('tour.finish')) ?>"
      data-tour-step="<?= e(t('tour.step')) ?>"
      data-tour-endpoint="<?= e(url('/perfil/tutorial')) ?>"
      data-copy-error="<?= e(t('common.copy')) ?>">
<a class="skip-link" href="#conteudo"><?= e(t('common.skip')) ?></a>

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
                <small><?= e(t('app.tagline')) ?></small>
            </span>
        </a>

        <p class="sidebar__label"><?= e(t('common.menu')) ?></p>
        <nav class="nav" aria-label="<?= e(t('common.navigation')) ?>">
            <?php foreach ($navigation as $item): ?>
                <a class="nav__link<?= $nav === $item['key'] ? ' is-active' : '' ?>"
                   href="<?= e(url($item['href'])) ?>"
                   data-tour-id="nav-<?= e($item['key']) ?>"
                   data-tour-title="<?= e(t('tour.nav_' . $item['key'] . '_title')) ?>"
                   data-tour-text="<?= e(t('tour.nav_' . $item['key'] . '_text')) ?>"
                   <?= $nav === $item['key'] ? 'aria-current="page"' : '' ?>>
                    <?= icon($item['icon']) ?>
                    <span><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar__footer">
            <?php if ($user !== null): ?>
                <div class="user-card" data-tour-id="profile-menu"
                     data-tour-title="<?= e(t('tour.profile_title')) ?>"
                     data-tour-text="<?= e(t('tour.profile_text')) ?>">
                    <a class="avatar avatar--link" href="<?= e(url('/perfil')) ?>"
                       aria-label="<?= e(t('common.profile')) ?>"><?= e(initials((string) $user['name'])) ?></a>
                    <span class="user-card__info">
                        <a href="<?= e(url('/perfil')) ?>"><strong><?= e($user['name']) ?></strong></a>
                        <span class="badge <?= $isManager ? 'badge--manager' : 'badge--staff' ?>">
                            <?= $isManager ? e(t('common.manager')) : e(t('common.staff')) ?>
                        </span>
                    </span>
                    <form method="post" action="<?= e(url('/logout')) ?>" class="user-card__logout">
                        <?= csrf_field() ?>
                        <button type="submit" class="logout-btn" title="<?= e(t('common.logout')) ?>">
                            <?= icon('logout') ?><span><?= e(t('common.logout')) ?></span>
                        </button>
                    </form>
                </div>
                <p class="sidebar__note"><?= e(t('app.academic')) ?></p>
            <?php else: ?>
                <a class="btn btn--ghost btn--block" href="<?= e(url('/login')) ?>"><?= e(t('auth.login_button')) ?></a>
            <?php endif; ?>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <button class="icon-btn nav-toggle" type="button" data-nav-toggle
                    aria-controls="sidebar" aria-expanded="false" aria-label="<?= e(t('common.open_menu')) ?>">
                <?= icon('menu') ?>
            </button>
            <a class="topbar__brand" href="<?= e(url('/')) ?>" aria-label="ObraClara">
                <span class="topbar__brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round">
                        <path d="M4 20V9.5L12 4l8 5.5V20"/>
                        <path d="M9.5 20v-5.5h5V20"/>
                    </svg>
                </span>
                <span>ObraClara</span>
            </a>
            <p class="topbar__context"><?= e($user !== null ? t('layout.session', ['name' => $user['name']]) : 'ObraClara') ?></p>
            <p class="topbar__date"><?= e(today_label()) ?></p>

            <?php if ($user !== null): ?>
                <div class="topbar__tools">
                    <form method="post" action="<?= e(url('/perfil/idioma')) ?>" class="lang-switch"
                          aria-label="<?= e(t('layout.language')) ?>"
                          data-tour-id="language-switch"
                          data-tour-title="<?= e(t('tour.language_title')) ?>"
                          data-tour-text="<?= e(t('tour.language_text')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="redirect" value="<?= e($path) ?>">
                        <button type="submit" name="locale" value="pt" class="<?= current_locale() === 'pt' ? 'is-active' : '' ?>"
                                aria-pressed="<?= current_locale() === 'pt' ? 'true' : 'false' ?>">PT</button>
                        <span aria-hidden="true">|</span>
                        <button type="submit" name="locale" value="en" class="<?= current_locale() === 'en' ? 'is-active' : '' ?>"
                                aria-pressed="<?= current_locale() === 'en' ? 'true' : 'false' ?>">EN</button>
                    </form>

                    <button type="button" class="icon-btn" data-tour-help
                            data-tour-target="<?= e($tourPage) ?>"
                            title="<?= e(t('layout.help')) ?>" aria-label="<?= e(t('layout.help')) ?>">
                        <?= icon('help') ?>
                    </button>

                    <a class="topbar__profile" href="<?= e(url('/perfil')) ?>" aria-label="<?= e(t('common.profile')) ?>">
                        <span class="avatar avatar--sm" aria-hidden="true"><?= e(initials((string) $user['name'])) ?></span>
                        <span class="sr-only"><?= e(t('common.profile')) ?></span>
                    </a>
                </div>
            <?php endif; ?>
        </header>

        <main class="content" id="conteudo">
            <?php foreach ($flash as $message): ?>
                <div class="alert alert--<?= $message['type'] === 'success' ? 'success' : 'error' ?>" role="status">
                    <span class="alert__icon" aria-hidden="true">
                        <?= icon($message['type'] === 'success' ? 'check' : 'alert') ?>
                    </span>
                    <p><?= e($message['message']) ?></p>
                    <button type="button" class="alert__close" data-dismiss aria-label="<?= e(t('common.dismiss')) ?>">
                        <?= icon('close') ?>
                    </button>
                </div>
            <?php endforeach; ?>

            <?= $content ?>
        </main>

        <footer class="footer">
            <span><?= e(t('app.footer')) ?></span>
            <span>PHP <?= e(PHP_VERSION) ?> · SQLite</span>
        </footer>
    </div>
</div>

<div class="nav-backdrop" data-nav-backdrop hidden></div>

<div class="tour" data-tour hidden>
    <div class="tour__spotlight" data-tour-spotlight aria-hidden="true"></div>
    <section class="tour__popover" data-tour-popover role="dialog" aria-modal="false"
             aria-labelledby="tour-title" aria-describedby="tour-text" tabindex="-1">
        <p class="tour__progress" data-tour-progress></p>
        <h2 id="tour-title" data-tour-heading></h2>
        <p id="tour-text" data-tour-body></p>
        <div class="tour__actions">
            <button type="button" class="btn btn--link" data-tour-skip><?= e(t('tour.skip')) ?></button>
            <span class="tour__spacer"></span>
            <button type="button" class="btn btn--ghost" data-tour-prev><?= e(t('tour.previous')) ?></button>
            <button type="button" class="btn btn--primary" data-tour-next><?= e(t('tour.next')) ?></button>
        </div>
    </section>
</div>

<div class="tour-welcome" data-tour-welcome hidden>
    <section class="tour-welcome__card card" role="dialog" aria-modal="true" aria-labelledby="welcome-title" tabindex="-1">
        <span class="tour-welcome__mark" aria-hidden="true"><?= icon('help') ?></span>
        <h2 id="welcome-title"><?= e(t('tour.welcome_title')) ?></h2>
        <p><?= e(t('tour.welcome_text')) ?></p>
        <div class="tour-welcome__actions">
            <button type="button" class="btn btn--ghost" data-tour-welcome-later><?= e(t('tour.later')) ?></button>
            <button type="button" class="btn btn--primary" data-tour-welcome-start><?= e(t('tour.start')) ?></button>
        </div>
    </section>
</div>

<div class="toast" data-toast role="status" aria-live="polite" hidden></div>

<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
