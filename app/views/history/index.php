<?php

declare(strict_types=1);

use ObraClara\Support\Labels;

/**
 * @var array<string, mixed> $user
 * @var list<array<string, mixed>> $events
 */
?>
<section class="page-head">
    <div>
        <p class="eyebrow"><?= e(t('history.eyebrow')) ?></p>
        <h1><?= e(t('history.title')) ?></h1>
        <p class="text-muted"><?= e(t('history.subtitle')) ?></p>
    </div>
</section>

<section class="card card--flush">
    <?php if ($events === []): ?>
        <div class="empty-state">
            <span class="empty-state__icon"><?= icon('history') ?></span>
            <h2><?= e(t('history.empty')) ?></h2>
            <p><?= e(t('history.empty_hint')) ?></p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col"><?= e(t('history.col_date')) ?></th>
                        <th scope="col"><?= e(t('history.col_action')) ?></th>
                        <th scope="col"><?= e(t('history.col_change')) ?></th>
                        <th scope="col"><?= e(t('history.col_project')) ?></th>
                        <th scope="col" class="text-right"><?= e(t('history.col_value')) ?></th>
                        <th scope="col"><?= e(t('history.col_user')) ?></th>
                        <th scope="col"><?= e(t('history.col_note')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td data-label="<?= e(t('history.col_date')) ?>"><?= e(format_datetime($event['created_at'])) ?></td>
                            <td data-label="<?= e(t('history.col_action')) ?>">
                                <span class="badge <?= e(Labels::eventClass($event['action'])) ?>"><?= e(Labels::eventAction($event['action'])) ?></span>
                                <?php if ($event['from_status'] !== null): ?>
                                    <span class="cell-sub">
                                        <?= e(Labels::changeStatus($event['from_status'])) ?> → <?= e(Labels::changeStatus($event['to_status'])) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td data-label="<?= e(t('history.col_change')) ?>"><?= e($event['change_title']) ?></td>
                            <td data-label="<?= e(t('history.col_project')) ?>">
                                <a href="<?= e(url('/obras/' . $event['project_id'])) ?>"><?= e($event['project_title']) ?></a>
                                <span class="cell-sub"><?= e($event['client_name']) ?></span>
                            </td>
                            <td data-label="<?= e(t('history.col_value')) ?>" class="text-right money"><?= e(money((int) $event['amount_cents'])) ?></td>
                            <td data-label="<?= e(t('history.col_user')) ?>">
                                <?= e($event['user_name']) ?>
                                <span class="cell-sub"><?= e($event['user_role'] === 'gestor' ? t('common.manager') : t('common.staff')) ?></span>
                            </td>
                            <td data-label="<?= e(t('history.col_note')) ?>">
                                <?= $event['note'] !== null ? e($event['note']) : '<span class="text-muted">—</span>' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="table-footnote"><?= e(t('history.last', ['count' => count($events)])) ?></p>
    <?php endif; ?>
</section>
