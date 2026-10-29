<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $user
 * @var string $mode
 * @var array<string, mixed> $project
 * @var array<string, mixed> $change
 * @var array<string, string> $errors
 * @var string $formAction
 */
$isEdit = $mode === 'edit';
$isManager = $user['role'] === 'gestor';
?>
<nav class="breadcrumb" aria-label="<?= e(t('common.location')) ?>">
    <a href="<?= e(url('/obras')) ?>"><?= e(t('projects.title')) ?></a>
    <span aria-hidden="true">/</span>
    <a href="<?= e(url('/obras/' . $project['id'])) ?>"><?= e($project['title']) ?></a>
    <span aria-hidden="true">/</span>
    <span><?= e($isEdit ? t('changes.edit_breadcrumb') : t('changes.new_breadcrumb')) ?></span>
</nav>

<section class="page-head page-head--tight">
    <div>
        <p class="eyebrow"><?= e($isEdit ? t('changes.edit_kicker') : t('changes.new_kicker')) ?></p>
        <h1><?= e($isEdit ? t('changes.edit_title') : t('changes.new_title')) ?></h1>
        <p class="text-muted">
            <?= e(t('changes.context', [
                'project' => $project['title'],
                'budget' => money((int) $project['base_budget_cents']),
            ])) ?>
        </p>
    </div>
</section>

<div class="grid grid--2-1">
    <section class="card form-card">
        <form method="post" action="<?= e(url($formAction)) ?>" class="form" novalidate>
            <?= csrf_field() ?>

            <div class="field">
                <label for="title"><?= e(t('changes.work_title')) ?> <span class="required" aria-hidden="true">*</span></label>
                <input type="text" id="title" name="title" value="<?= e($change['title'] ?? '') ?>" maxlength="150" required
                       placeholder="<?= e(t('changes.title_placeholder')) ?>"
                       class="<?= field_class($errors, 'title') ?>"
                       <?= isset($errors['title']) ? 'aria-invalid="true" aria-describedby="title-error"' : '' ?>>
                <?php if (isset($errors['title'])): ?>
                    <p class="field__error" id="title-error"><?= e($errors['title']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="amount"><?= e(t('changes.amount')) ?> <span class="required" aria-hidden="true">*</span></label>
                <input type="text" id="amount" name="amount" value="<?= e($change['amount'] ?? '') ?>" required
                       inputmode="decimal" placeholder="<?= e(t('changes.amount_placeholder')) ?>"
                       class="<?= field_class($errors, 'amount') ?>"
                       <?= isset($errors['amount']) ? 'aria-invalid="true" aria-describedby="amount-error amount-help"' : 'aria-describedby="amount-help"' ?>>
                <?php if (isset($errors['amount'])): ?>
                    <p class="field__error" id="amount-error"><?= e($errors['amount']) ?></p>
                <?php endif; ?>
                <p class="field__help" id="amount-help"><?= e(t('changes.amount_help')) ?></p>
            </div>

            <div class="field">
                <label for="description"><?= e(t('changes.description')) ?></label>
                <textarea id="description" name="description" rows="5" maxlength="2000"
                          placeholder="<?= e(t('changes.description_placeholder')) ?>"
                          class="<?= field_class($errors, 'description') ?>"><?= e($change['description'] ?? '') ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <p class="field__error"><?= e($errors['description']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <a class="btn btn--ghost" href="<?= e(url('/obras/' . $project['id'])) ?>"><?= e(t('common.cancel')) ?></a>
                <button type="submit" name="action" value="draft" class="btn btn--ghost">
                    <?= e($isEdit ? t('changes.save_draft') : t('changes.save_as_draft')) ?>
                </button>
                <button type="submit" name="action" value="submit" class="btn btn--primary">
                    <?= e(t('changes.save_submit')) ?>
                </button>
            </div>
        </form>
    </section>

    <aside class="card" aria-labelledby="fluxo-title">
        <header class="card__head">
            <h2 id="fluxo-title"><?= e(t('changes.how')) ?></h2>
        </header>

        <ol class="steps">
            <li>
                <span class="steps__dot" aria-hidden="true">1</span>
                <div>
                    <strong><?= e(t('changes.step_draft')) ?></strong>
                    <p><?= e(t('changes.step_draft_text')) ?></p>
                </div>
            </li>
            <li>
                <span class="steps__dot" aria-hidden="true">2</span>
                <div>
                    <strong><?= e(t('changes.step_pending')) ?></strong>
                    <p><?= e(t('changes.step_pending_text')) ?></p>
                </div>
            </li>
            <li>
                <span class="steps__dot" aria-hidden="true">3</span>
                <div>
                    <strong><?= e(t('changes.step_final')) ?></strong>
                    <p><?= e(t('changes.step_final_text')) ?></p>
                </div>
            </li>
        </ol>

        <p class="callout">
            <?= icon('alert') ?>
            <span><?= e($isManager ? t('changes.callout_manager') : t('changes.callout_staff')) ?></span>
        </p>
    </aside>
</div>
