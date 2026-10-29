<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $user
 * @var array<string, string> $errors
 * @var array<string, string> $passwordErrors
 */
$isManager = $user['role'] === 'gestor';
$errors = $errors ?? [];
$passwordErrors = $passwordErrors ?? [];
?>
<nav class="breadcrumb" aria-label="<?= e(t('common.location')) ?>">
    <a href="<?= e(url('/')) ?>"><?= e(t('nav.dashboard')) ?></a>
    <span aria-hidden="true">/</span>
    <span><?= e(t('profile.title')) ?></span>
</nav>

<section class="page-head">
    <div>
        <p class="eyebrow"><?= e(t('profile.eyebrow')) ?></p>
        <h1><?= e(t('profile.title')) ?></h1>
        <p class="text-muted"><?= e(t('profile.subtitle')) ?></p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--ghost" href="<?= e(url('/')) ?>"><?= icon('back') ?> <?= e(t('profile.back_dashboard')) ?></a>
    </div>
</section>

<div class="profile-grid">
    <section class="card" aria-labelledby="perfil-dados" data-tour-id="profile-info"
         data-tour-title="<?= e(t('tour.profile_title')) ?>"
         data-tour-text="<?= e(t('tour.profile_text')) ?>">
        <header class="card__head">
            <div>
                <h2 id="perfil-dados"><?= e(t('profile.personal')) ?></h2>
                <p class="text-muted"><?= e(t('profile.personal_help')) ?></p>
            </div>
            <span class="avatar avatar--large" aria-hidden="true"><?= e(initials((string) $user['name'])) ?></span>
        </header>

        <form method="post" action="<?= e(url('/perfil')) ?>" class="form" novalidate>
            <?= csrf_field() ?>

            <div class="form-grid">
                <div class="field field--full">
                    <label for="name"><?= e(t('profile.name')) ?> <span class="required" aria-hidden="true">*</span></label>
                    <input type="text" id="name" name="name" value="<?= e($user['name']) ?>" maxlength="120" required
                           autocomplete="name" class="<?= field_class($errors, 'name') ?>"
                           <?= isset($errors['name']) ? 'aria-invalid="true" aria-describedby="name-error"' : '' ?>>
                    <?php if (isset($errors['name'])): ?>
                        <p class="field__error" id="name-error"><?= e($errors['name']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field field--full">
                    <label for="email"><?= e(t('profile.email')) ?> <span class="required" aria-hidden="true">*</span></label>
                    <input type="email" id="email" name="email" value="<?= e($user['email']) ?>" maxlength="150" required
                           autocomplete="email" class="<?= field_class($errors, 'email') ?>"
                           <?= isset($errors['email']) ? 'aria-invalid="true" aria-describedby="email-error"' : '' ?>>
                    <p class="field__help"><?= e(t('profile.email_help')) ?></p>
                    <?php if (isset($errors['email'])): ?>
                        <p class="field__error" id="email-error"><?= e($errors['email']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary"><?= e(t('profile.save')) ?></button>
            </div>
        </form>

        <dl class="definition-list definition-list--inline">
            <div>
                <dt><?= e(t('profile.role')) ?></dt>
                <dd><span class="badge <?= $isManager ? 'badge--role-manager' : 'badge--role-staff' ?>"><?= e($isManager ? t('common.manager') : t('common.staff')) ?></span></dd>
            </div>
            <div>
                <dt><?= e(t('profile.state')) ?></dt>
                <dd><span class="badge badge--active"><?= e(t('common.active')) ?></span></dd>
            </div>
            <div>
                <dt><?= e(t('profile.since_label')) ?></dt>
                <dd><?= e(t('profile.since', ['date' => format_date((string) $user['created_at'])])) ?></dd>
            </div>
        </dl>
        <p class="field__help"><?= e(t('profile.role_help')) ?></p>
    </section>

    <div class="stack">
        <section class="card" aria-labelledby="perfil-seguranca" data-tour-id="profile-security"
         data-tour-title="<?= e(t('tour.profile_security_title')) ?>"
         data-tour-text="<?= e(t('tour.profile_security_text')) ?>">
            <header class="card__head">
                <div>
                    <h2 id="perfil-seguranca"><?= e(t('profile.security')) ?></h2>
                    <p class="text-muted"><?= e(t('profile.password_help')) ?></p>
                </div>
                <span class="kpi__icon"><?= icon('lock') ?></span>
            </header>

            <form method="post" action="<?= e(url('/perfil/palavra-passe')) ?>" class="form" novalidate>
                <?= csrf_field() ?>

                <div class="field">
                    <label for="current_password"><?= e(t('profile.current_password')) ?> <span class="required" aria-hidden="true">*</span></label>
                    <div class="password-field">
                        <input type="password" id="current_password" name="current_password" required
                               autocomplete="current-password" class="<?= field_class($passwordErrors, 'current_password') ?>">
                        <button type="button" class="password-toggle" data-password-toggle
                                aria-controls="current_password" aria-pressed="false"
                                data-label-show="<?= e(t('common.show_password')) ?>"
                                data-label-hide="<?= e(t('common.hide_password')) ?>"
                                aria-label="<?= e(t('common.show_password')) ?>"><?= icon('eye') ?></button>
                    </div>
                    <?php if (isset($passwordErrors['current_password'])): ?>
                        <p class="field__error"><?= e($passwordErrors['current_password']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="new_password"><?= e(t('profile.new_password')) ?> <span class="required" aria-hidden="true">*</span></label>
                    <div class="password-field">
                        <input type="password" id="new_password" name="password" required
                               autocomplete="new-password" class="<?= field_class($passwordErrors, 'password') ?>"
                               aria-describedby="new-password-help">
                        <button type="button" class="password-toggle" data-password-toggle
                                aria-controls="new_password" aria-pressed="false"
                                data-label-show="<?= e(t('common.show_password')) ?>"
                                data-label-hide="<?= e(t('common.hide_password')) ?>"
                                aria-label="<?= e(t('common.show_password')) ?>"><?= icon('eye') ?></button>
                    </div>
                    <p class="field__help" id="new-password-help"><?= e(t('profile.password_rules')) ?></p>
                    <?php if (isset($passwordErrors['password'])): ?>
                        <p class="field__error"><?= e($passwordErrors['password']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="password_confirm"><?= e(t('profile.confirm_password')) ?> <span class="required" aria-hidden="true">*</span></label>
                    <div class="password-field">
                        <input type="password" id="password_confirm" name="password_confirm" required
                               autocomplete="new-password" class="<?= field_class($passwordErrors, 'password_confirm') ?>">
                        <button type="button" class="password-toggle" data-password-toggle
                                aria-controls="password_confirm" aria-pressed="false"
                                data-label-show="<?= e(t('common.show_password')) ?>"
                                data-label-hide="<?= e(t('common.hide_password')) ?>"
                                aria-label="<?= e(t('common.show_password')) ?>"><?= icon('eye') ?></button>
                    </div>
                    <?php if (isset($passwordErrors['password_confirm'])): ?>
                        <p class="field__error"><?= e($passwordErrors['password_confirm']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn--primary"><?= e(t('profile.change_password')) ?></button>
                </div>
            </form>
        </section>

        <section class="card" aria-labelledby="perfil-preferencias">
            <header class="card__head">
                <div>
                    <h2 id="perfil-preferencias"><?= e(t('profile.preferences')) ?></h2>
                    <p class="text-muted"><?= e(t('profile.language_help')) ?></p>
                </div>
                <span class="kpi__icon"><?= icon('globe') ?></span>
            </header>

            <form method="post" action="<?= e(url('/perfil/idioma')) ?>" class="form form--inline">
                <?= csrf_field() ?>
                <input type="hidden" name="redirect" value="/perfil">
                <div class="field">
                    <label for="locale"><?= e(t('profile.language')) ?></label>
                    <select id="locale" name="locale">
                        <option value="pt" <?= current_locale() === 'pt' ? 'selected' : '' ?>>Português (PT)</option>
                        <option value="en" <?= current_locale() === 'en' ? 'selected' : '' ?>>English (EN)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn--ghost"><?= e(t('profile.save_language')) ?></button>
            </form>

            <div class="preference-row" data-tour-id="profile-tour"
                 data-tour-title="<?= e(t('profile.tour')) ?>"
                 data-tour-text="<?= e(t('profile.tour_help')) ?>">
                <div>
                    <strong><?= e(t('profile.tour')) ?></strong>
                    <p class="text-muted text-sm"><?= e(t('profile.tour_help')) ?></p>
                </div>
                <a class="btn btn--ghost btn--sm" href="<?= e(url('/?tour=1')) ?>"><?= icon('help') ?> <?= e(t('profile.restart_tour')) ?></a>
            </div>
        </section>
    </div>
</div>
