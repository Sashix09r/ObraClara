<?php

declare(strict_types=1);

/**
 * @var array<string, string> $errors
 * @var string|null $error
 * @var string $token
 * @var string $email
 * @var string $name
 */
$errors = $errors ?? [];
$error = $error ?? null;
$token = $token ?? '';
$email = $email ?? '';
$name = $name ?? '';
$languageQuery = $_GET;
$languageUrl = static function (string $locale) use ($languageQuery): string {
    $languageQuery['lang'] = $locale;

    return url('/registar') . '?' . http_build_query($languageQuery);
};
?>
<div class="auth-shell">
    <section class="auth-brand" aria-label="<?= e(t('app.name')) ?>">
        <div class="auth-brand__top">
            <span class="brand__mark brand__mark--light" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round">
                    <path d="M4 20V9.5L12 4l8 5.5V20"/>
                    <path d="M9.5 20v-5.5h5V20"/>
                </svg>
            </span>
            <span class="auth-brand__name">ObraClara</span>
        </div>

        <div class="auth-brand__body">
            <p class="eyebrow"><?= e(t('auth.kicker')) ?></p>
            <h1><?= e(t('auth.headline')) ?></h1>
            <p><?= e(t('auth.intro')) ?></p>
            <ul class="auth-points">
                <li><span aria-hidden="true">✓</span> <?= e(t('auth.point_budget')) ?></li>
                <li><span aria-hidden="true">✓</span> <?= e(t('auth.point_roles')) ?></li>
                <li><span aria-hidden="true">✓</span> <?= e(t('auth.point_history')) ?></li>
            </ul>
        </div>
        <p class="auth-brand__footer"><?= e(t('auth.footer')) ?></p>
    </section>

    <section class="auth-panel" id="conteudo">
        <div class="auth-panel__top">
            <nav class="lang-switch lang-switch--auth" aria-label="<?= e(t('layout.language')) ?>">
                <a class="<?= current_locale() === 'pt' ? 'is-active' : '' ?>" href="<?= e($languageUrl('pt')) ?>"
                   hreflang="pt" lang="pt">PT</a>
                <span aria-hidden="true">|</span>
                <a class="<?= current_locale() === 'en' ? 'is-active' : '' ?>" href="<?= e($languageUrl('en')) ?>"
                   hreflang="en" lang="en">EN</a>
            </nav>
        </div>

        <div class="auth-card">
            <header class="auth-card__header">
                <p class="eyebrow"><?= e(t('auth.register_kicker')) ?></p>
                <h2><?= e(t('auth.register_title')) ?></h2>
                <p class="text-muted"><?= e(t('auth.register_help')) ?></p>
            </header>

            <?php if ($error !== null): ?>
                <div class="alert alert--error" role="alert">
                    <span class="alert__icon" aria-hidden="true"><?= icon('alert') ?></span>
                    <p><?= e($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($errors['token'])): ?>
                <div class="alert alert--error" role="alert">
                    <span class="alert__icon" aria-hidden="true"><?= icon('alert') ?></span>
                    <p><?= e($errors['token']) ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= e(url('/registar')) ?>" class="form" novalidate>
                <?= csrf_field() ?>

                <div class="field">
                    <label for="token"><?= e(t('auth.invite_code')) ?> <span class="required" aria-hidden="true">*</span></label>
                    <input type="text" id="token" name="token" value="<?= e($token) ?>" required
                           autocomplete="off" spellcheck="false" class="mono <?= field_class($errors, 'token') ?>">
                    <p class="field__help"><?= e(t('auth.invite_code_help')) ?></p>
                </div>

                <div class="field">
                    <label for="email"><?= e(t('common.email')) ?> <span class="required" aria-hidden="true">*</span></label>
                    <input type="email" id="email" name="email" value="<?= e($email) ?>" required
                           autocomplete="email" class="<?= field_class($errors, 'email') ?>"
                           <?= isset($errors['email']) ? 'aria-invalid="true" aria-describedby="email-error"' : '' ?>>
                    <?php if (isset($errors['email'])): ?>
                        <p class="field__error" id="email-error"><?= e($errors['email']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="name"><?= e(t('auth.register_name')) ?> <span class="required" aria-hidden="true">*</span></label>
                    <input type="text" id="name" name="name" value="<?= e($name) ?>" maxlength="120" required
                           autocomplete="name" class="<?= field_class($errors, 'name') ?>"
                           <?= isset($errors['name']) ? 'aria-invalid="true" aria-describedby="name-error"' : '' ?>>
                    <?php if (isset($errors['name'])): ?>
                        <p class="field__error" id="name-error"><?= e($errors['name']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="password"><?= e(t('auth.register_password')) ?> <span class="required" aria-hidden="true">*</span></label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" required
                               autocomplete="new-password" class="<?= field_class($errors, 'password') ?>"
                               aria-describedby="password-help<?= isset($errors['password']) ? ' password-error' : '' ?>">
                        <button type="button" class="password-toggle" data-password-toggle
                                aria-controls="password" aria-pressed="false"
                                data-label-show="<?= e(t('common.show_password')) ?>"
                                data-label-hide="<?= e(t('common.hide_password')) ?>"
                                aria-label="<?= e(t('common.show_password')) ?>"><?= icon('eye') ?></button>
                    </div>
                    <p class="field__help" id="password-help"><?= e(t('auth.register_password_help')) ?></p>
                    <?php if (isset($errors['password'])): ?>
                        <p class="field__error" id="password-error"><?= e($errors['password']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="password_confirm"><?= e(t('auth.register_confirm')) ?> <span class="required" aria-hidden="true">*</span></label>
                    <div class="password-field">
                        <input type="password" id="password_confirm" name="password_confirm" required
                               autocomplete="new-password" class="<?= field_class($errors, 'password_confirm') ?>">
                        <button type="button" class="password-toggle" data-password-toggle
                                aria-controls="password_confirm" aria-pressed="false"
                                data-label-show="<?= e(t('common.show_password')) ?>"
                                data-label-hide="<?= e(t('common.hide_password')) ?>"
                                aria-label="<?= e(t('common.show_password')) ?>"><?= icon('eye') ?></button>
                    </div>
                    <?php if (isset($errors['password_confirm'])): ?>
                        <p class="field__error"><?= e($errors['password_confirm']) ?></p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn--primary btn--block">
                    <?= e(t('auth.register_submit')) ?> <?= icon('arrow') ?>
                </button>
            </form>

            <div class="auth-separator" role="presentation"><span><?= e(t('auth.have_account')) ?></span></div>
            <a class="btn btn--ghost btn--block" href="<?= e(url('/login')) ?>"><?= e(t('auth.back_login')) ?></a>
        </div>
        <p class="auth-panel__note"><?= e(t('auth.local_note')) ?></p>
    </section>
</div>
