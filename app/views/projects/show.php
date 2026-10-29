<?php

declare(strict_types=1);

use ObraClara\Support\Labels;

/**
 * @var array<string, mixed> $user
 * @var array<string, mixed> $project
 * @var list<array<string, mixed>> $changes
 * @var array<int, list<array<string, mixed>>> $eventsByChange
 */
$isManager = $user['role'] === 'gestor';
$isArchived = $project['status'] === 'arquivada';

$approvedCount = 0;
$pendingCount = 0;
$draftCount = 0;
foreach ($changes as $change) {
    if ($change['status'] === 'aprovada') {
        $approvedCount++;
    } elseif ($change['status'] === 'pendente') {
        $pendingCount++;
    } elseif ($change['status'] === 'rascunho') {
        $draftCount++;
    }
}

$totalEvents = 0;
foreach ($eventsByChange as $events) {
    $totalEvents += count($events);
}
?>
<nav class="breadcrumb" aria-label="<?= e(t('common.location')) ?>">
    <a href="<?= e(url('/obras')) ?>"><?= e(t('projects.show.breadcrumb')) ?></a>
    <span aria-hidden="true">/</span>
    <a href="<?= e(url('/clientes/' . $project['client_id'])) ?>"><?= e($project['client_name']) ?></a>
    <span aria-hidden="true">/</span>
    <span><?= e($project['title']) ?></span>
</nav>

<section class="page-head" data-tour-id="project-header"
         data-tour-title="<?= e(t('tour.project_header_title')) ?>"
         data-tour-text="<?= e(t('tour.project_header_text')) ?>">
    <div>
        <div class="page-head__meta">
            <span class="badge badge--<?= e($project['status']) ?>"><?= e(Labels::projectStatus($project['status'])) ?></span>
            <?php if ($project['start_date'] !== null): ?>
                <span class="text-muted text-sm"><?= e(t('projects.show.start', ['date' => format_date($project['start_date'])])) ?></span>
            <?php endif; ?>
        </div>
        <h1><?= e($project['title']) ?></h1>
        <p class="text-muted">
            <?= e(t('projects.show.client')) ?> <a href="<?= e(url('/clientes/' . $project['client_id'])) ?>"><?= e($project['client_name']) ?></a>
        </p>
    </div>
    <div class="page-head__actions">
        <?php if (!$isArchived): ?>
            <a class="btn btn--primary" href="<?= e(url('/obras/' . $project['id'] . '/alteracoes/criar')) ?>"
               data-tour-id="project-new"
               data-tour-title="<?= e(t('tour.project_new_title')) ?>"
               data-tour-text="<?= e(t('tour.project_new_text')) ?>">
                <?= icon('plus') ?> <?= e(t('projects.show.new_change')) ?>
            </a>
        <?php endif; ?>
        <?php if ($isManager): ?>
            <a class="btn btn--ghost" href="<?= e(url('/obras/' . $project['id'] . '/editar')) ?>"><?= icon('edit') ?> <?= e(t('projects.show.edit')) ?></a>
        <?php endif; ?>
        <a class="btn btn--ghost" href="<?= e(url('/obras/' . $project['id'] . '/pdf')) ?>"
           data-tour-id="project-export" title="<?= e(t('projects.show.export_help')) ?>"
           data-tour-title="<?= e(t('tour.project_export_title')) ?>"
           data-tour-text="<?= e(t('tour.project_export_text')) ?>">
            <?= icon('pdf') ?> <?= e(t('projects.show.export_pdf')) ?>
        </a>
    </div>
</section>

<div class="budget-grid" data-tour-id="project-budgets"
     data-tour-title="<?= e(t('tour.project_budgets_title')) ?>"
     data-tour-text="<?= e(t('tour.project_budgets_text')) ?>">
    <article class="card budget-card">
        <p class="budget-card__label"><?= e(t('projects.show.base')) ?></p>
        <p class="budget-card__value"><?= e(money((int) $project['base_budget_cents'])) ?></p>
        <p class="budget-card__hint"><?= e(t('projects.show.base_hint')) ?></p>
    </article>
    <article class="card budget-card">
        <p class="budget-card__label"><?= e(t('projects.show.approved')) ?></p>
        <p class="budget-card__value">+ <?= e(money((int) $project['approved_cents'])) ?></p>
        <p class="budget-card__hint"><?= e(t('projects.show.approved_count', ['count' => $approvedCount])) ?></p>
    </article>
    <article class="card budget-card budget-card--total">
        <p class="budget-card__label"><?= e(t('projects.show.total')) ?></p>
        <p class="budget-card__value"><?= e(money((int) $project['updated_budget_cents'])) ?></p>
        <p class="budget-card__hint"><?= e(t('projects.show.total_hint')) ?></p>
    </article>
</div>

<?php if ($project['description'] !== null): ?>
    <section class="card card--quiet">
        <h2 class="section-title"><?= e(t('projects.show.scope')) ?></h2>
        <p class="prose"><?= nl2br(e($project['description'])) ?></p>
    </section>
<?php endif; ?>

<section class="card" aria-labelledby="alteracoes-title" data-tour-id="project-changes"
         data-tour-title="<?= e(t('tour.project_changes_title')) ?>"
         data-tour-text="<?= e(t('tour.project_changes_text')) ?>">
    <header class="card__head">
        <div>
            <h2 id="alteracoes-title"><?= e(t('projects.show.changes')) ?></h2>
            <p class="text-muted">
                <?= e(t('projects.show.changes_summary', [
                    'total' => count($changes),
                    'pending' => $pendingCount,
                    'drafts' => $draftCount,
                ])) ?>
            </p>
        </div>
        <span class="count-pill"><?= e((string) count($changes)) ?></span>
    </header>

    <?php if ($changes === []): ?>
        <div class="empty-state">
            <span class="empty-state__icon"><?= icon('projects') ?></span>
            <h3><?= e(t('projects.show.no_changes')) ?></h3>
            <p><?= e(t('projects.show.no_changes_hint')) ?></p>
            <?php if (!$isArchived): ?>
                <a class="btn btn--primary" href="<?= e(url('/obras/' . $project['id'] . '/alteracoes/criar')) ?>"><?= e(t('projects.show.create_change')) ?></a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table table--changes">
                <thead>
                    <tr>
                        <th scope="col"><?= e(t('projects.show.col_change')) ?></th>
                        <th scope="col" class="text-right"><?= e(t('projects.show.col_value')) ?></th>
                        <th scope="col"><?= e(t('projects.show.col_state')) ?></th>
                        <th scope="col"><?= e(t('projects.show.col_record')) ?></th>
                        <th scope="col" class="text-right"><?= e(t('projects.show.col_actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($changes as $change): ?>
                        <?php
                        $canEdit = $change['status'] === 'rascunho'
                            && ($isManager || (int) $change['created_by'] === (int) $user['id']);
                        ?>
                        <tr id="alteracao-<?= e((string) $change['id']) ?>">
                            <td data-label="<?= e(t('projects.show.col_change')) ?>">
                                <strong><?= e($change['title']) ?></strong>
                                <?php if ($change['description'] !== null): ?>
                                    <span class="cell-sub"><?= e(mb_strimwidth($change['description'], 0, 110, '…')) ?></span>
                                <?php endif; ?>
                                <?php if ($change['status'] === 'pendente'): ?>
                                    <span class="cell-sub cell-sub--note"><?= e(t('projects.show.pending_note')) ?></span>
                                <?php endif; ?>
                            </td>
                            <td data-label="<?= e(t('projects.show.col_value')) ?>" class="text-right money money--strong"><?= e(money((int) $change['amount_cents'])) ?></td>
                            <td data-label="<?= e(t('projects.show.col_state')) ?>">
                                <span class="badge badge--<?= e($change['status']) ?>"><?= e(Labels::changeStatus($change['status'])) ?></span>
                            </td>
                            <td data-label="<?= e(t('projects.show.col_record')) ?>">
                                <span class="text-sm"><?= e($change['author_name']) ?></span>
                                <span class="cell-sub"><?= e(format_datetime($change['created_at'])) ?></span>
                                <?php if ($change['decided_at'] !== null): ?>
                                    <span class="cell-sub">
                                        <?= e(t('projects.show.decided_by', [
                                            'name' => $change['decider_name'] ?? '—',
                                            'date' => format_datetime($change['decided_at']),
                                        ])) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td data-label="<?= e(t('projects.show.col_actions')) ?>" class="text-right action-cell">
                                <?php if ($change['status'] === 'rascunho' && $canEdit): ?>
                                    <a class="btn btn--sm btn--ghost" href="<?= e(url('/alteracoes/' . $change['id'] . '/editar')) ?>"><?= icon('edit') ?> <?= e(t('common.edit')) ?></a>
                                    <form method="post" action="<?= e(url('/alteracoes/' . $change['id'] . '/submeter')) ?>"
                                          data-confirm="<?= e(t('projects.show.submit_confirm', ['title' => $change['title']])) ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn--sm btn--primary"><?= e(t('projects.show.submit')) ?></button>
                                    </form>
                                <?php elseif ($change['status'] === 'pendente' && $isManager): ?>
                                    <a class="btn btn--sm btn--primary" href="<?= e(url('/alteracoes/' . $change['id'] . '/decidir')) ?>"
                                       data-tour-id="project-decide"
                                       data-tour-title="<?= e(t('tour.project_changes_title')) ?>"
                                       data-tour-text="<?= e(t('tour.project_changes_text')) ?>"><?= e(t('projects.show.decide')) ?></a>
                                <?php elseif ($change['status'] === 'pendente'): ?>
                                    <span class="text-muted text-sm"><?= e(t('projects.show.waiting')) ?></span>
                                <?php else: ?>
                                    <span class="text-muted text-sm"><?= e(t('common.no_actions')) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($change['decided_at'] !== null && $change['decision_note'] !== null): ?>
                            <tr class="row-note">
                                <td colspan="5">
                                    <strong><?= e(t('projects.show.observation')) ?></strong> <?= e($change['decision_note']) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="table-footnote"><?= e(t('projects.show.table_note')) ?></p>
    <?php endif; ?>
</section>

<section class="card" aria-labelledby="historico-obra" data-tour-id="project-history"
         data-tour-title="<?= e(t('tour.project_history_title')) ?>"
         data-tour-text="<?= e(t('tour.project_history_text')) ?>">
    <header class="card__head">
        <div>
            <h2 id="historico-obra"><?= e(t('projects.show.history')) ?></h2>
            <p class="text-muted"><?= e(t('projects.show.history_hint')) ?></p>
        </div>
        <span class="count-pill"><?= e((string) $totalEvents) ?></span>
    </header>

    <?php if ($totalEvents === 0): ?>
        <div class="empty-state empty-state--sm">
            <p><?= e(t('projects.show.no_history')) ?></p>
        </div>
    <?php else: ?>
        <ol class="event-list">
            <?php foreach ($changes as $change): ?>
                <?php foreach ($eventsByChange[(int) $change['id']] ?? [] as $event): ?>
                    <li class="event-list__item">
                        <span class="badge <?= e(Labels::eventClass($event['action'])) ?>"><?= e(Labels::eventAction($event['action'])) ?></span>
                        <div>
                            <p class="event-list__title"><?= e($event['change_title']) ?></p>
                            <p class="event-list__meta">
                                <?= e($event['user_name']) ?>
                                <?php if ($event['from_status'] !== null): ?>
                                    · <?= e(Labels::changeStatus($event['from_status'])) ?> → <?= e(Labels::changeStatus($event['to_status'])) ?>
                                <?php endif; ?>
                                · <?= e(format_datetime($event['created_at'])) ?>
                            </p>
                            <?php if ($event['note'] !== null): ?>
                                <p class="event-list__note"><?= e($event['note']) ?></p>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</section>

<?php if ($isManager): ?>
    <section class="card danger-zone">
        <div>
            <h2 class="section-title"><?= e($isArchived ? t('projects.show.reactivate_title') : t('projects.show.archive_title')) ?></h2>
            <p class="text-muted">
                <?= e($isArchived ? t('projects.show.reactivate_text') : t('projects.show.archive_text')) ?>
            </p>
        </div>
        <?php if ($isArchived): ?>
            <form method="post" action="<?= e(url('/obras/' . $project['id'] . '/reativar')) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn--primary"><?= e(t('projects.show.reactivate')) ?></button>
            </form>
        <?php else: ?>
            <form method="post" action="<?= e(url('/obras/' . $project['id'] . '/arquivar')) ?>"
                  data-confirm="<?= e(t('projects.show.archive_confirm')) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn--danger-ghost"><?= icon('archive') ?> <?= e(t('projects.show.archive')) ?></button>
            </form>
        <?php endif; ?>
    </section>
<?php endif; ?>
