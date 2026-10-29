<?php

declare(strict_types=1);

/**
 * @var array<string, string> $errors
 * @var string|null $error
 * @var string $email
 */
$errors = $errors ?? [];
$error = $error ?? null;
$email = $email ?? '';
?>
<div class="auth-shell">
    <section class="auth-brand" aria-label="Sobre a aplicação">
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
            <p class="eyebrow">Remodelações · Gestão de obra</p>
            <h1>Decisões sobre custos extra, todas no mesmo lugar.</h1>
            <p>
                Registe trabalhos adicionais, submeta propostas e mantenha o orçamento
                da obra sempre atualizado — com o histórico de quem decidiu o quê.
            </p>

            <ul class="auth-points">
                <li><span aria-hidden="true">✓</span> Orçamento atualizado apenas com alterações aprovadas</li>
                <li><span aria-hidden="true">✓</span> Perfis distintos para gestor e colaborador</li>
                <li><span aria-hidden="true">✓</span> Histórico imutável de submissões e decisões</li>
            </ul>
        </div>

        <p class="auth-brand__footer">Projeto académico — Programação Web, 12.º F</p>
    </section>

    <section class="auth-panel" id="conteudo">
        <div class="auth-card">
            <header class="auth-card__header">
                <p class="eyebrow">Área reservada</p>
                <h2>Entrar na conta</h2>
                <p class="text-muted">Usa as credenciais fornecidas pela empresa.</p>
            </header>

            <?php if ($error !== null): ?>
                <div class="alert alert--error" role="alert">
                    <span class="alert__icon" aria-hidden="true"><?= icon('alert') ?></span>
                    <p><?= e($error) ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= e(url('/login')) ?>" class="form" novalidate>
                <?= csrf_field() ?>

                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= e($email) ?>"
                           autocomplete="username" required autofocus
                           class="<?= field_class($errors, 'email') ?>"
                           <?= isset($errors['email']) ? 'aria-invalid="true" aria-describedby="email-error"' : '' ?>>
                    <?php if (isset($errors['email'])): ?>
                        <p class="field__error" id="email-error"><?= e($errors['email']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="password">Palavra-passe</label>
                    <input type="password" id="password" name="password" required
                           autocomplete="current-password"
                           class="<?= field_class($errors, 'password') ?>"
                           <?= isset($errors['password']) ? 'aria-invalid="true" aria-describedby="password-error"' : '' ?>>
                    <?php if (isset($errors['password'])): ?>
                        <p class="field__error" id="password-error"><?= e($errors['password']) ?></p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn--primary btn--block">
                    Entrar
                    <?= icon('arrow') ?>
                </button>
            </form>

            <aside class="demo-box">
                <h3>Contas de demonstração</h3>
                <p>Dados fictícios, apenas para esta instalação local.</p>
                <dl>
                    <div>
                        <dt>Gestor</dt>
                        <dd>gestor@obraclara.pt · <code>Gestor2026!</code></dd>
                    </div>
                    <div>
                        <dt>Colaborador</dt>
                        <dd>colaborador@obraclara.pt · <code>Colab2026!</code></dd>
                    </div>
                </dl>
            </aside>
        </div>

        <p class="auth-panel__note">
            Aplicação local — não introduzas dados reais de clientes.
        </p>
    </section>
</div>
