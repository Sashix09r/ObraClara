<?php

declare(strict_types=1);

use ObraClara\Support\Labels;

/**
 * @var array<string, mixed> $user
 * @var string $mode
 * @var array<string, mixed> $project
 * @var list<array<string, mixed>> $clients
 * @var array<string, string> $errors
 * @var string $formAction
 */
$isEdit = $mode === 'edit';
$statuses = ['planeada', 'em_curso', 'concluida'];
?>
<nav class="breadcrumb" aria-label="<?= e(t('common.location')) ?>">
    <a href="<?= e(url('/obras')) ?>"><?= e(t('projects.title')) ?></a>
    <span aria-hidden="true">/</span>
    <span><?= e($isEdit ? t('projects.edit_breadcrumb') : t('projects.new_breadcrumb')) ?></span>
</nav>

<section class="page-head page-head--tight">
    <div>
        <p class="eyebrow"><?= e($isEdit ? t('projects.edit_kicker') : t('projects.new_kicker')) ?></p>
        <h1><?= e($isEdit ? t('projects.edit_title') : t('projects.new_title')) ?></h1>
        <p class="text-muted"><?= e(t('projects.form_help')) ?></p>
    </div>
</section>

<section class="card form-card">
    <form method="post" action="<?= e(url($formAction)) ?>" class="form" novalidate>
        <?= csrf_field() ?>

        <div class="form-grid">
            <div class="field field--full">
                <label for="title"><?= e(t('projects.project_title')) ?> <span class="required" aria-hidden="true">*</span></label>
                <input type="text" id="title" name="title" value="<?= e($project['title'] ?? '') ?>" maxlength="150" required
                       placeholder="<?= e(t('projects.title_placeholder')) ?>"
                       class="<?= field_class($errors, 'title') ?>"
                       <?= isset($errors['title']) ? 'aria-invalid="true" aria-describedby="title-error"' : '' ?>>
                <?php if (isset($errors['title'])): ?>
                    <p class="field__error" id="title-error"><?= e($errors['title']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="client_id"><?= e(t('projects.client')) ?> <span class="required" aria-hidden="true">*</span></label>
                <select id="client_id" name="client_id" required class="<?= field_class($errors, 'client_id') ?>">
                    <option value=""><?= e(t('projects.choose_client')) ?></option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= e((string) $client['id']) ?>"
                            <?= (string) ($project['client_id'] ?? '') === (string) $client['id'] ? 'selected' : '' ?>>
                            <?= e($client['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['client_id'])): ?>
                    <p class="field__error"><?= e($errors['client_id']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="base_budget"><?= e(t('projects.base_budget')) ?> <span class="required" aria-hidden="true">*</span></label>
                <input type="text" id="base_budget" name="base_budget" value="<?= e($project['base_budget'] ?? '') ?>" required
                       inputmode="decimal" placeholder="<?= e(t('projects.budget_placeholder')) ?>"
                       class="<?= field_class($errors, 'base_budget') ?>"
                       <?= isset($errors['base_budget']) ? 'aria-invalid="true" aria-describedby="budget-error budget-help"' : 'aria-describedby="budget-help"' ?>>
                <?php if (isset($errors['base_budget'])): ?>
                    <p class="field__error" id="budget-error"><?= e($errors['base_budget']) ?></p>
                <?php endif; ?>
                <p class="field__help" id="budget-help"><?= e(t('projects.budget_help')) ?></p>
            </div>

            <div class="field">
                <label for="start_date"><?= e(t('projects.start_date')) ?></label>
                <input type="date" id="start_date" name="start_date" value="<?= e($project['start_date'] ?? '') ?>"
                       class="<?= field_class($errors, 'start_date') ?>">
                <?php if (isset($errors['start_date'])): ?>
                    <p class="field__error"><?= e($errors['start_date']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="status"><?= e(t('projects.state')) ?></label>
                <select id="status" name="status">
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= e($status) ?>" <?= ($project['status'] ?? 'planeada') === $status ? 'selected' : '' ?>>
                            <?= e(Labels::projectStatus($status)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="field__help"><?= e(t('projects.state_help')) ?></p>
            </div>

            <div class="field field--full">
                <label for="description"><?= e(t('projects.description')) ?></label>
                <textarea id="description" name="description" rows="4" maxlength="2000"
                          placeholder="<?= e(t('projects.description_placeholder')) ?>"
                          class="<?= field_class($errors, 'description') ?>"><?= e($project['description'] ?? '') ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <p class="field__error"><?= e($errors['description']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <a class="btn btn--ghost" href="<?= e(url($isEdit ? '/obras/' . $project['id'] : '/obras')) ?>"><?= e(t('common.cancel')) ?></a>
            <button type="submit" class="btn btn--primary"><?= e($isEdit ? t('common.save_changes') : t('projects.create')) ?></button>
        </div>
    </form>
</section>
