<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $user
 * @var array<string, mixed> $target
 * @var list<array<string, mixed>> $events
 */
$isSelf = (int) $user['id'] === (int) $target['id'];
$isManager = $target['role'] === 'gestor';
$isActive = (int) $target['active'] === 1;
?>
<nav class="breadcrumb" aria-label="<?= e(t('common.location')) ?>">
    <a href="<?= e(url('/equipa')) ?>"><?= e(t('team.title')) ?></a>
    <span aria-hidden="true">/</span>
    <span><?= e($target['name']) ?></span>
</nav>

<section class="page-head">
    <div>
        <div class="page-head__meta">
            <span class="badge <?= $isManager ? 'badge--role-manager' : 'badge--role-staff' ?>">
                <?= e($isManager ? t('common.manager') : t('common.staff')) ?>
            </span>
            <span class="badge <?= $isActive ? 'badge--active' : 'badge--archived' ?>">
                <?= e($isActive ? t('common.active') : t('common.inactive')) ?>
            </span>
        </div>
        <h1><?= e($target['name']) ?></h1>
        <p class="text-muted"><?= e($target['email']) ?></p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--ghost" href="<?= e(url('/equipa')) ?>"><?= icon('back') ?> <?= e(t('team.back_team')) ?></a>
    </div>
</section>

<div class="grid grid--2-1">
    <section class="card">
        <header class="card__head">
            <div>
                <h2><?= e(t('team.user_detail')) ?></h2>
                <p class="text-muted"><?= e(t('team.role_help')) ?></p>
            </div>
            <span class="avatar avatar--large" aria-hidden="true"><?= e(initials((string) $target['name'])) ?></span>
        </header>

        <dl class="definition-list">
            <div>
                <dt><?= e(t('common.name')) ?></dt>
                <dd><?= e($target['name']) ?></dd>
            </div>
            <div>
                <dt><?= e(t('common.email')) ?></dt>
                <dd><?= e($target['email']) ?></dd>
            </div>
            <div>
                <dt><?= e(t('team.account_since', ['date' => format_date((string) $target['created_at'])])) ?></dt>
                <dd>
                    <?= $target['last_login_at'] !== null
                        ? e(t('team.last_login', ['date' => format_datetime((string) $target['last_login_at'])]))
                        : e(t('team.never_logged')) ?>
                </dd>
            </div>
            <div>
                <dt><?= e(t('common.language')) ?></dt>
                <dd><?= e(($target['locale'] ?? 'pt') === 'en' ? 'English (EN)' : 'Português (PT)') ?></dd>
            </div>
        </dl>

        <?php if ($isSelf): ?>
            <div class="alert alert--error" role="status">
                <span class="alert__icon" aria-hidden="true"><?= icon('alert') ?></span>
                <p><?= e(t('team.cannot_self_role')) ?></p>
            </div>
        <?php else: ?>
            <div class="admin-grid">
                <form method="post" action="<?= e(url('/equipa/utilizadores/' . $target['id'] . '/papel')) ?>" class="form">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label for="role"><?= e(t('common.role')) ?></label>
                        <select id="role" name="role">
                            <option value="colaborador" <?= !$isManager ? 'selected' : '' ?>><?= e(t('common.staff')) ?></option>
                            <option value="gestor" <?= $isManager ? 'selected' : '' ?>><?= e(t('common.manager')) ?></option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn--ghost"><?= e(t('common.save_changes')) ?></button>
                </form>

                <form method="post" action="<?= e(url('/equipa/utilizadores/' . $target['id'] . '/estado')) ?>" class="form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="active" value="<?= $isActive ? '0' : '1' ?>">
                    <p class="field__help">
                        <?= $isActive ? e(t('team.deactivate_confirm')) : e(t('team.account_activated')) ?>
                    </p>
                    <button type="submit" class="btn <?= $isActive ? 'btn--danger-ghost' : 'btn--primary' ?>"
                            <?= $isActive ? 'data-confirm="' . e(t('team.deactivate_confirm')) . '"' : '' ?>>
                        <?= e($isActive ? t('team.deactivate') : t('team.activate')) ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </section>

    <aside class="stack">
        <section class="callout callout--card">
            <?= icon('lock') ?>
            <p><?= e(t('team.admin_note')) ?></p>
        </section>

        <section class="card">
            <header class="card__head">
                <h2><?= e(t('history.title')) ?></h2>
            </header>

            <?php if ($events === []): ?>
                <p class="text-muted text-sm"><?= e(t('common.no_events')) ?></p>
            <?php else: ?>
                <ol class="event-list event-list--compact">
                    <?php foreach ($events as $event): ?>
                        <li class="event-list__item">
                            <span class="badge badge--neutral"><?= e(t('admin.action.' . $event['action'])) ?></span>
                            <div>
                                <p class="event-list__meta">
                                    <?= e($event['actor_name']) ?> · <?= e(format_datetime((string) $event['created_at'])) ?>
                                </p>
                                <?php if ($event['note'] !== null): ?>
                                    <p class="event-list__note"><?= e($event['note']) ?></p>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </section>
    </aside>
</div>
