<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $user
 * @var string $mode
 * @var array<string, mixed> $client
 * @var array<string, string> $errors
 * @var string $formAction
 */
$isEdit = $mode === 'edit';
?>
<nav class="breadcrumb" aria-label="<?= e(t('common.location')) ?>">
    <a href="<?= e(url('/clientes')) ?>"><?= e(t('clients.title')) ?></a>
    <span aria-hidden="true">/</span>
    <span><?= e($isEdit ? t('clients.edit_breadcrumb') : t('clients.new_breadcrumb')) ?></span>
</nav>

<section class="page-head page-head--tight">
    <div>
        <p class="eyebrow"><?= e($isEdit ? t('clients.edit_kicker') : t('clients.new_kicker')) ?></p>
        <h1><?= e($isEdit ? t('clients.edit_title') : t('clients.new_title')) ?></h1>
        <p class="text-muted"><?= e(t('clients.form_help')) ?></p>
    </div>
</section>

<section class="card form-card">
    <form method="post" action="<?= e(url($formAction)) ?>" class="form" novalidate>
        <?= csrf_field() ?>

        <div class="form-grid">
            <div class="field field--full">
                <label for="name"><?= e(t('clients.name')) ?> <span class="required" aria-hidden="true">*</span></label>
                <input type="text" id="name" name="name" value="<?= e($client['name'] ?? '') ?>" maxlength="120" required
                       autocomplete="organization"
                       class="<?= field_class($errors, 'name') ?>"
                       <?= isset($errors['name']) ? 'aria-invalid="true" aria-describedby="name-error"' : '' ?>>
                <?php if (isset($errors['name'])): ?>
                    <p class="field__error" id="name-error"><?= e($errors['name']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="contact"><?= e(t('clients.contact')) ?></label>
                <input type="text" id="contact" name="contact" value="<?= e($client['contact'] ?? '') ?>" maxlength="60"
                       autocomplete="tel" placeholder="<?= e(t('clients.contact_placeholder')) ?>"
                       class="<?= field_class($errors, 'contact') ?>">
                <?php if (isset($errors['contact'])): ?>
                    <p class="field__error"><?= e($errors['contact']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="email"><?= e(t('clients.email')) ?></label>
                <input type="email" id="email" name="email" value="<?= e($client['email'] ?? '') ?>" maxlength="150"
                       autocomplete="email" placeholder="<?= e(t('clients.email_placeholder')) ?>"
                       class="<?= field_class($errors, 'email') ?>">
                <?php if (isset($errors['email'])): ?>
                    <p class="field__error"><?= e($errors['email']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field field--full">
                <label for="notes"><?= e(t('clients.notes')) ?></label>
                <textarea id="notes" name="notes" rows="4" maxlength="1500"
                          placeholder="<?= e(t('clients.notes_placeholder')) ?>"
                          class="<?= field_class($errors, 'notes') ?>"><?= e($client['notes'] ?? '') ?></textarea>
                <?php if (isset($errors['notes'])): ?>
                    <p class="field__error"><?= e($errors['notes']) ?></p>
                <?php endif; ?>
                <p class="field__help"><?= e(t('clients.notes_help')) ?></p>
            </div>
        </div>

        <div class="form-actions">
            <a class="btn btn--ghost" href="<?= e(url($isEdit ? '/clientes/' . $client['id'] : '/clientes')) ?>"><?= e(t('common.cancel')) ?></a>
            <button type="submit" class="btn btn--primary"><?= e($isEdit ? t('common.save_changes') : t('clients.create')) ?></button>
        </div>
    </form>
</section>
