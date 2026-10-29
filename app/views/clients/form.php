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
<nav class="breadcrumb" aria-label="Localização">
    <a href="<?= e(url('/clientes')) ?>">Clientes</a>
    <span aria-hidden="true">/</span>
    <span><?= $isEdit ? 'Editar' : 'Novo' ?></span>
</nav>

<section class="page-head page-head--tight">
    <div>
        <p class="eyebrow"><?= $isEdit ? 'Atualizar contacto' : 'Novo registo' ?></p>
        <h1><?= $isEdit ? 'Editar cliente' : 'Novo cliente' ?></h1>
        <p class="text-muted">O nome é obrigatório. Os restantes dados ajudam no contacto diário.</p>
    </div>
</section>

<section class="card form-card">
    <form method="post" action="<?= e(url($formAction)) ?>" class="form" novalidate>
        <?= csrf_field() ?>

        <div class="form-grid">
            <div class="field field--full">
                <label for="name">Nome do cliente <span class="required" aria-hidden="true">*</span></label>
                <input type="text" id="name" name="name" value="<?= e($client['name'] ?? '') ?>" maxlength="120" required
                       autocomplete="organization"
                       class="<?= field_class($errors, 'name') ?>"
                       <?= isset($errors['name']) ? 'aria-invalid="true" aria-describedby="name-error"' : '' ?>>
                <?php if (isset($errors['name'])): ?>
                    <p class="field__error" id="name-error"><?= e($errors['name']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="contact">Contacto telefónico</label>
                <input type="text" id="contact" name="contact" value="<?= e($client['contact'] ?? '') ?>" maxlength="60"
                       autocomplete="tel" placeholder="ex.: 912 345 678"
                       class="<?= field_class($errors, 'contact') ?>">
                <?php if (isset($errors['contact'])): ?>
                    <p class="field__error"><?= e($errors['contact']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($client['email'] ?? '') ?>" maxlength="150"
                       autocomplete="email" placeholder="ex.: cliente@exemplo.pt"
                       class="<?= field_class($errors, 'email') ?>">
                <?php if (isset($errors['email'])): ?>
                    <p class="field__error"><?= e($errors['email']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field field--full">
                <label for="notes">Notas internas</label>
                <textarea id="notes" name="notes" rows="4" maxlength="1500"
                          placeholder="Preferências de contacto, morada da obra, observações..."
                          class="<?= field_class($errors, 'notes') ?>"><?= e($client['notes'] ?? '') ?></textarea>
                <?php if (isset($errors['notes'])): ?>
                    <p class="field__error"><?= e($errors['notes']) ?></p>
                <?php endif; ?>
                <p class="field__help">Máximo 1500 caracteres. Não guardes dados sensíveis desnecessários.</p>
            </div>
        </div>

        <div class="form-actions">
            <a class="btn btn--ghost" href="<?= e(url($isEdit ? '/clientes/' . $client['id'] : '/clientes')) ?>">Cancelar</a>
            <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Guardar alterações' : 'Criar cliente' ?></button>
        </div>
    </form>
</section>
