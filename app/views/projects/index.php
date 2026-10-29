<?php

declare(strict_types=1);

use ObraClara\Support\Labels;

/**
 * @var array<string, mixed> $user
 * @var list<array<string, mixed>> $projects
 * @var string $query
 * @var string $status
 */
$isManager = $user['role'] === 'gestor';
$statuses = [
    '' => t('projects.all_states'),
    'planeada' => t('projects.planned_plural'),
    'em_curso' => t('projects.active_plural'),
    'concluida' => t('projects.completed_plural'),
    'arquivada' => t('projects.archived_plural'),
];
?>
<section class="page-head">
    <div>
        <p class="eyebrow"><?= e(t('projects.eyebrow')) ?></p>
        <h1><?= e(t('projects.title')) ?></h1>
        <p class="text-muted"><?= e(t('projects.subtitle')) ?></p>
    </div>
    <?php if ($isManager): ?>
        <div class="page-head__actions">
            <a class="btn btn--primary" href="<?= e(url('/obras/criar')) ?>"><?= icon('plus') ?> <?= e(t('projects.new')) ?></a>
        </div>
    <?php endif; ?>
</section>

<form class="toolbar" method="get" action="<?= e(url('/obras')) ?>" role="search">
    <div class="search-field">
        <?= icon('search') ?>
        <label class="sr-only" for="q"><?= e(t('projects.search_placeholder')) ?></label>
        <input type="search" id="q" name="q" value="<?= e($query) ?>" placeholder="<?= e(t('projects.search_placeholder')) ?>">
    </div>

    <div class="select-field">
        <label class="sr-only" for="estado"><?= e(t('projects.state')) ?></label>
        <select id="estado" name="estado">
            <?php foreach ($statuses as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <button type="submit" class="btn btn--ghost"><?= e(t('common.filter')) ?></button>
    <?php if ($query !== '' || $status !== ''): ?>
        <a class="btn btn--link" href="<?= e(url('/obras')) ?>"><?= e(t('common.clear')) ?></a>
    <?php endif; ?>
</form>

<?php if ($projects === []): ?>
    <section class="card">
        <div class="empty-state">
            <span class="empty-state__icon"><?= icon('projects') ?></span>
            <?php if ($query !== '' || $status !== ''): ?>
                <h2><?= e(t('projects.no_filter')) ?></h2>
                <p><?= e(t('projects.no_filter_hint')) ?></p>
                <a class="btn btn--ghost" href="<?= e(url('/obras')) ?>"><?= e(t('projects.clear_filters')) ?></a>
            <?php else: ?>
                <h2><?= e(t('projects.empty')) ?></h2>
                <p><?= e(t('projects.empty_hint')) ?></p>
                <?php if ($isManager): ?>
                    <a class="btn btn--primary" href="<?= e(url('/obras/criar')) ?>"><?= e(t('projects.create')) ?></a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
<?php else: ?>
    <section class="card card--flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col"><?= e(t('projects.col_project')) ?></th>
                        <th scope="col"><?= e(t('projects.col_client')) ?></th>
                        <th scope="col"><?= e(t('projects.col_state')) ?></th>
                        <th scope="col" class="text-right"><?= e(t('projects.col_base')) ?></th>
                        <th scope="col" class="text-right"><?= e(t('projects.col_approved')) ?></th>
                        <th scope="col" class="text-right"><?= e(t('projects.col_updated')) ?></th>
                        <th scope="col" class="text-right"><span class="sr-only"><?= e(t('common.actions')) ?></span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td data-label="<?= e(t('projects.col_project')) ?>">
                                <a class="cell-strong" href="<?= e(url('/obras/' . $project['id'])) ?>"><?= e($project['title']) ?></a>
                                <?php if ($project['start_date'] !== null): ?>
                                    <span class="cell-sub"><?= e(t('projects.start', ['date' => format_date($project['start_date'])])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td data-label="<?= e(t('projects.col_client')) ?>"><?= e($project['client_name']) ?></td>
                            <td data-label="<?= e(t('projects.col_state')) ?>">
                                <span class="badge badge--<?= e($project['status']) ?>"><?= e(Labels::projectStatus($project['status'])) ?></span>
                            </td>
                            <td data-label="<?= e(t('projects.col_base')) ?>" class="text-right money"><?= e(money((int) $project['base_budget_cents'])) ?></td>
                            <td data-label="<?= e(t('projects.col_approved')) ?>" class="text-right money"><?= e(money((int) $project['approved_cents'])) ?></td>
                            <td data-label="<?= e(t('projects.col_updated')) ?>" class="text-right money money--strong"><?= e(money((int) $project['updated_budget_cents'])) ?></td>
                            <td data-label="<?= e(t('common.action')) ?>" class="text-right">
                                <a class="btn btn--sm btn--ghost" href="<?= e(url('/obras/' . $project['id'])) ?>"><?= e(t('common.open')) ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>
