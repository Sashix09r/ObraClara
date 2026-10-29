<?php

declare(strict_types=1);

use ObraClara\Support\Labels;

/**
 * @var array<string, mixed> $user
 * @var array<string, mixed> $client
 * @var list<array<string, mixed>> $projects
 */
$isManager = $user['role'] === 'gestor';
$isActive = (int) $client['active'] === 1;
?>
<nav class="breadcrumb" aria-label="<?= e(t('common.location')) ?>">
    <a href="<?= e(url('/clientes')) ?>"><?= e(t('clients.title')) ?></a>
    <span aria-hidden="true">/</span>
    <span><?= e($client['name']) ?></span>
</nav>

<section class="page-head">
    <div>
        <div class="page-head__meta">
            <?php if ($isActive): ?>
                <span class="badge badge--active"><?= e(t('common.active')) ?></span>
            <?php else: ?>
                <span class="badge badge--archived"><?= e(t('common.archived')) ?></span>
            <?php endif; ?>
            <span class="text-muted text-sm"><?= e(t('clients.since', ['date' => format_date($client['created_at'])])) ?></span>
        </div>
        <h1><?= e($client['name']) ?></h1>
        <p class="text-muted"><?= e(t('clients.works_count', ['count' => count($projects)])) ?></p>
    </div>
    <?php if ($isManager): ?>
        <div class="page-head__actions">
            <a class="btn btn--ghost" href="<?= e(url('/clientes/' . $client['id'] . '/editar')) ?>"><?= icon('edit') ?> <?= e(t('common.edit')) ?></a>
            <?php if ($isActive): ?>
                <form method="post" action="<?= e(url('/clientes/' . $client['id'] . '/arquivar')) ?>"
                      data-confirm="<?= e(t('clients.archive_confirm')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn--danger-ghost"><?= icon('archive') ?> <?= e(t('clients.archive')) ?></button>
                </form>
            <?php else: ?>
                <form method="post" action="<?= e(url('/clientes/' . $client['id'] . '/reativar')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn--primary"><?= e(t('clients.reactivate')) ?></button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<div class="grid grid--2-1">
    <section class="card" aria-labelledby="obras-cliente">
        <header class="card__head">
            <div>
                <h2 id="obras-cliente"><?= e(t('clients.projects_title')) ?></h2>
                <p class="text-muted"><?= e(t('clients.projects_hint')) ?></p>
            </div>
            <?php if ($isManager && $isActive): ?>
                <a class="btn btn--sm btn--primary" href="<?= e(url('/obras/criar?cliente=' . $client['id'])) ?>"><?= icon('plus') ?> <?= e(t('clients.new_project')) ?></a>
            <?php endif; ?>
        </header>

        <?php if ($projects === []): ?>
            <div class="empty-state empty-state--sm">
                <h3><?= e(t('clients.no_projects')) ?></h3>
                <p><?= e(t('clients.no_projects_hint')) ?></p>
            </div>
        <?php else: ?>
            <ul class="record-list">
                <?php foreach ($projects as $project): ?>
                    <li class="record-list__item">
                        <div>
                            <a class="cell-strong" href="<?= e(url('/obras/' . $project['id'])) ?>"><?= e($project['title']) ?></a>
                            <p class="cell-sub">
                                <?= e(Labels::projectStatus($project['status'])) ?> ·
                                <?= e(t('dashboard.col_base')) ?> <?= e(money((int) $project['base_budget_cents'])) ?> ·
                                <?= e(t('dashboard.col_updated')) ?> <strong><?= e(money((int) $project['updated_budget_cents'])) ?></strong>
                            </p>
                        </div>
                        <span class="badge badge--<?= e($project['status']) ?>"><?= e(Labels::projectStatus($project['status'])) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <aside class="card" aria-labelledby="contacto-title">
        <header class="card__head">
            <h2 id="contacto-title"><?= e(t('clients.contact_title')) ?></h2>
        </header>
        <dl class="definition-list">
            <div>
                <dt><?= e(t('clients.phone')) ?></dt>
                <dd><?= $client['contact'] !== null ? e($client['contact']) : '<span class="text-muted">' . e(t('common.not_informed')) . '</span>' ?></dd>
            </div>
            <div>
                <dt><?= e(t('common.email')) ?></dt>
                <dd><?= $client['email'] !== null ? e($client['email']) : '<span class="text-muted">' . e(t('common.not_informed')) . '</span>' ?></dd>
            </div>
            <div>
                <dt><?= e(t('clients.notes')) ?></dt>
                <dd><?= $client['notes'] !== null ? nl2br(e($client['notes'])) : '<span class="text-muted">' . e(t('common.no_notes')) . '</span>' ?></dd>
            </div>
        </dl>
    </aside>
</div>
