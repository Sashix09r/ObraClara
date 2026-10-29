<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $user
 * @var list<array<string, mixed>> $clients
 * @var string $query
 * @var bool $includeArchived
 */
$isManager = $user['role'] === 'gestor';
?>
<section class="page-head">
    <div>
        <p class="eyebrow"><?= e(t('clients.eyebrow')) ?></p>
        <h1><?= e(t('clients.title')) ?></h1>
        <p class="text-muted"><?= e(t('clients.subtitle')) ?></p>
    </div>
    <?php if ($isManager): ?>
        <div class="page-head__actions">
            <a class="btn btn--primary" href="<?= e(url('/clientes/criar')) ?>"><?= icon('plus') ?> <?= e(t('clients.new')) ?></a>
        </div>
    <?php endif; ?>
</section>

<form class="toolbar" method="get" action="<?= e(url('/clientes')) ?>" role="search">
    <div class="search-field">
        <?= icon('search') ?>
        <label class="sr-only" for="q"><?= e(t('clients.search_placeholder')) ?></label>
        <input type="search" id="q" name="q" value="<?= e($query) ?>" placeholder="<?= e(t('clients.search_placeholder')) ?>">
    </div>
    <label class="checkbox">
        <input type="checkbox" name="arquivados" value="1" <?= $includeArchived ? 'checked' : '' ?>>
        <span><?= e(t('clients.show_archived')) ?></span>
    </label>
    <button type="submit" class="btn btn--ghost"><?= e(t('common.filter')) ?></button>
    <?php if ($query !== '' || $includeArchived): ?>
        <a class="btn btn--link" href="<?= e(url('/clientes')) ?>"><?= e(t('common.clear')) ?></a>
    <?php endif; ?>
</form>

<?php if ($clients === []): ?>
    <section class="card">
        <div class="empty-state">
            <span class="empty-state__icon"><?= icon('clients') ?></span>
            <?php if ($query !== ''): ?>
                <h2><?= e(t('clients.no_results', ['q' => $query])) ?></h2>
                <p><?= e(t('clients.no_results_hint')) ?></p>
                <a class="btn btn--ghost" href="<?= e(url('/clientes')) ?>"><?= e(t('clients.clear_search')) ?></a>
            <?php else: ?>
                <h2><?= e(t('clients.empty')) ?></h2>
                <p><?= e(t('clients.empty_hint')) ?></p>
                <?php if ($isManager): ?>
                    <a class="btn btn--primary" href="<?= e(url('/clientes/criar')) ?>"><?= e(t('clients.create')) ?></a>
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
                        <th scope="col"><?= e(t('clients.col_client')) ?></th>
                        <th scope="col"><?= e(t('clients.col_contact')) ?></th>
                        <th scope="col"><?= e(t('clients.col_email')) ?></th>
                        <th scope="col" class="text-center"><?= e(t('clients.col_projects')) ?></th>
                        <th scope="col"><?= e(t('clients.col_state')) ?></th>
                        <th scope="col" class="text-right"><?= e(t('common.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td data-label="<?= e(t('clients.col_client')) ?>">
                                <a class="cell-strong" href="<?= e(url('/clientes/' . $client['id'])) ?>"><?= e($client['name']) ?></a>
                            </td>
                            <td data-label="<?= e(t('clients.col_contact')) ?>"><?= $client['contact'] !== null ? e($client['contact']) : '<span class="text-muted">—</span>' ?></td>
                            <td data-label="<?= e(t('clients.col_email')) ?>"><?= $client['email'] !== null ? e($client['email']) : '<span class="text-muted">—</span>' ?></td>
                            <td data-label="<?= e(t('clients.col_projects')) ?>" class="text-center"><?= e((string) $client['active_projects']) ?></td>
                            <td data-label="<?= e(t('clients.col_state')) ?>">
                                <?php if ((int) $client['active'] === 1): ?>
                                    <span class="badge badge--active"><?= e(t('common.active')) ?></span>
                                <?php else: ?>
                                    <span class="badge badge--archived"><?= e(t('common.archived')) ?></span>
                                <?php endif; ?>
                            </td>
                            <td data-label="<?= e(t('common.actions')) ?>" class="text-right action-cell">
                                <a class="btn btn--sm btn--ghost" href="<?= e(url('/clientes/' . $client['id'])) ?>"><?= e(t('common.view')) ?></a>
                                <?php if ($isManager): ?>
                                    <a class="btn btn--sm btn--ghost" href="<?= e(url('/clientes/' . $client['id'] . '/editar')) ?>"><?= e(t('common.edit')) ?></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>
