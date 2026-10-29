<?php

declare(strict_types=1);

use ObraClara\Support\Labels;

/**
 * @var array<string, mixed> $user
 * @var array<string, mixed> $stats
 */
$isManager = $user['role'] === 'gestor';
$firstName = explode(' ', (string) $user['name'])[0];
$hour = (int) date('G');
$greeting = $hour < 12 ? t('dashboard.greeting_morning') : ($hour < 20 ? t('dashboard.greeting_afternoon') : t('dashboard.greeting_evening'));
?>
<section class="page-head">
    <div>
        <p class="eyebrow"><?= e(t('dashboard.eyebrow')) ?></p>
        <h1><?= e($greeting) ?>, <?= e($firstName) ?></h1>
        <p class="text-muted"><?= e(t('dashboard.subtitle')) ?></p>
    </div>
    <?php if ($isManager): ?>
        <div class="page-head__actions">
            <a class="btn btn--ghost" href="<?= e(url('/clientes/criar')) ?>"><?= e(t('dashboard.new_client')) ?></a>
            <a class="btn btn--primary" href="<?= e(url('/obras/criar')) ?>">
                <?= icon('plus') ?> <?= e(t('dashboard.new_project')) ?>
            </a>
        </div>
    <?php endif; ?>
</section>

<div class="kpi-grid">
    <article class="card kpi" data-tour-id="dashboard-kpis"
             data-tour-title="<?= e(t('tour.dashboard_title')) ?>"
             data-tour-text="<?= e(t('tour.dashboard_text')) ?>">
        <span class="kpi__icon"><?= icon('projects') ?></span>
        <p class="kpi__label"><?= e(t('dashboard.active_projects')) ?></p>
        <p class="kpi__value"><?= e((string) $stats['active_projects']) ?></p>
        <p class="kpi__hint"><?= e(t('dashboard.active_clients', ['count' => (string) $stats['active_clients']])) ?></p>
    </article>

    <article class="card kpi<?= $stats['pending_changes'] > 0 ? ' kpi--attention' : '' ?>">
        <span class="kpi__icon"><?= icon('clock') ?></span>
        <p class="kpi__label"><?= e(t('dashboard.pending')) ?></p>
        <p class="kpi__value"><?= e((string) $stats['pending_changes']) ?></p>
        <p class="kpi__hint"><?= e(t('dashboard.drafts', ['count' => (string) $stats['draft_changes']])) ?></p>
    </article>

    <article class="card kpi">
        <span class="kpi__icon"><?= icon('check') ?></span>
        <p class="kpi__label"><?= e(t('dashboard.approved_costs')) ?></p>
        <p class="kpi__value kpi__value--money"><?= e(money((int) $stats['approved_extra'])) ?></p>
        <p class="kpi__hint"><?= e(t('dashboard.approved_hint')) ?></p>
    </article>

    <article class="card kpi kpi--total">
        <span class="kpi__icon"><?= icon('money') ?></span>
        <p class="kpi__label"><?= e(t('dashboard.forecast')) ?></p>
        <p class="kpi__value kpi__value--money"><?= e(money((int) $stats['forecast_total'])) ?></p>
        <p class="kpi__hint"><?= e(t('dashboard.forecast_hint')) ?></p>
    </article>
</div>

<div class="grid grid--2-1">
    <section class="card" aria-labelledby="pendentes-title" data-tour-id="dashboard-pending">
        <header class="card__head">
            <div>
                <h2 id="pendentes-title"><?= e(t('dashboard.pending_title')) ?></h2>
                <p class="text-muted"><?= e(t('dashboard.pending_desc')) ?></p>
            </div>
            <span class="count-pill"><?= e((string) count($stats['pending'])) ?></span>
        </header>

        <?php if ($stats['pending'] === []): ?>
            <div class="empty-state">
                <span class="empty-state__icon"><?= icon('check') ?></span>
                <h3><?= e(t('dashboard.nothing_pending')) ?></h3>
                <p><?= e(t('dashboard.nothing_pending_text')) ?></p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col"><?= e(t('dashboard.col_change')) ?></th>
                            <th scope="col"><?= e(t('dashboard.col_project')) ?></th>
                            <th scope="col" class="text-right"><?= e(t('dashboard.col_value')) ?></th>
                            <th scope="col"><?= e(t('dashboard.col_submitted')) ?></th>
                            <th scope="col"><span class="sr-only"><?= e(t('common.actions')) ?></span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['pending'] as $change): ?>
                            <tr>
                                <td data-label="<?= e(t('dashboard.col_change')) ?>">
                                    <strong><?= e($change['title']) ?></strong>
                                    <span class="cell-sub"><?= e(t('common.by')) ?> <?= e($change['author_name']) ?></span>
                                </td>
                                <td data-label="<?= e(t('dashboard.col_project')) ?>">
                                    <a href="<?= e(url('/obras/' . $change['project_id'])) ?>"><?= e($change['project_title']) ?></a>
                                    <span class="cell-sub"><?= e($change['client_name']) ?></span>
                                </td>
                                <td data-label="<?= e(t('dashboard.col_value')) ?>" class="text-right money"><?= e(money((int) $change['amount_cents'])) ?></td>
                                <td data-label="<?= e(t('dashboard.col_submitted')) ?>"><?= e(format_datetime($change['submitted_at'])) ?></td>
                                <td data-label="<?= e(t('common.action')) ?>" class="text-right">
                                    <?php if ($isManager): ?>
                                        <a class="btn btn--sm btn--primary" href="<?= e(url('/alteracoes/' . $change['id'] . '/decidir')) ?>"><?= e(t('dashboard.decide')) ?></a>
                                    <?php else: ?>
                                        <span class="text-muted text-sm"><?= e(t('dashboard.waiting_manager')) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <aside class="card" aria-labelledby="atividade-title">
        <header class="card__head">
            <h2 id="atividade-title"><?= e(t('dashboard.activity')) ?></h2>
        </header>

        <?php if ($stats['events'] === []): ?>
            <div class="empty-state empty-state--sm">
                <p><?= e(t('dashboard.no_events')) ?></p>
            </div>
        <?php else: ?>
            <ul class="timeline">
                <?php foreach ($stats['events'] as $event): ?>
                    <li class="timeline__item">
                        <span class="badge <?= e(Labels::eventClass($event['action'])) ?>"><?= e(Labels::eventAction($event['action'])) ?></span>
                        <p class="timeline__title"><?= e($event['change_title']) ?></p>
                        <p class="timeline__meta">
                            <?= e($event['user_name']) ?> · <?= e(format_datetime($event['created_at'])) ?>
                        </p>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a class="link-arrow" href="<?= e(url('/historico')) ?>"><?= e(t('dashboard.view_history')) ?> <?= icon('arrow') ?></a>
        <?php endif; ?>
    </aside>
</div>

<section class="card" aria-labelledby="obras-title">
    <header class="card__head">
        <div>
            <h2 id="obras-title"><?= e(t('dashboard.projects_title')) ?></h2>
            <p class="text-muted"><?= e(t('dashboard.projects_desc')) ?></p>
        </div>
        <a class="link-arrow" href="<?= e(url('/obras')) ?>"><?= e(t('dashboard.all_projects')) ?> <?= icon('arrow') ?></a>
    </header>

    <?php if ($stats['projects'] === []): ?>
        <div class="empty-state">
            <span class="empty-state__icon"><?= icon('projects') ?></span>
            <h3><?= e(t('dashboard.no_projects')) ?></h3>
            <p><?= e(t('dashboard.no_projects_text')) ?></p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col"><?= e(t('dashboard.col_project_name')) ?></th>
                        <th scope="col"><?= e(t('dashboard.col_client')) ?></th>
                        <th scope="col"><?= e(t('dashboard.col_state')) ?></th>
                        <th scope="col" class="text-right"><?= e(t('dashboard.col_base')) ?></th>
                        <th scope="col" class="text-right"><?= e(t('dashboard.col_approved')) ?></th>
                        <th scope="col" class="text-right"><?= e(t('dashboard.col_updated')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['projects'] as $project): ?>
                        <tr>
                            <td data-label="<?= e(t('dashboard.col_project_name')) ?>">
                                <a class="cell-strong" href="<?= e(url('/obras/' . $project['id'])) ?>"><?= e($project['title']) ?></a>
                            </td>
                            <td data-label="<?= e(t('dashboard.col_client')) ?>"><?= e($project['client_name']) ?></td>
                            <td data-label="<?= e(t('dashboard.col_state')) ?>">
                                <span class="badge badge--<?= e($project['status']) ?>"><?= e(Labels::projectStatus($project['status'])) ?></span>
                            </td>
                            <td data-label="<?= e(t('dashboard.col_base')) ?>" class="text-right money"><?= e(money((int) $project['base_budget_cents'])) ?></td>
                            <td data-label="<?= e(t('dashboard.col_approved')) ?>" class="text-right money"><?= e(money((int) $project['approved_cents'])) ?></td>
                            <td data-label="<?= e(t('dashboard.col_updated')) ?>" class="text-right money money--strong"><?= e(money((int) $project['updated_budget_cents'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
