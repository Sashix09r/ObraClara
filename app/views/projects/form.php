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
<nav class="breadcrumb" aria-label="Localização">
    <a href="<?= e(url('/obras')) ?>">Obras</a>
    <span aria-hidden="true">/</span>
    <span><?= $isEdit ? 'Editar' : 'Nova' ?></span>
</nav>

<section class="page-head page-head--tight">
    <div>
        <p class="eyebrow"><?= $isEdit ? 'Atualizar obra' : 'Novo registo' ?></p>
        <h1><?= $isEdit ? 'Editar obra' : 'Nova obra' ?></h1>
        <p class="text-muted">O orçamento base é o valor contratado antes de qualquer trabalho adicional.</p>
    </div>
</section>

<section class="card form-card">
    <form method="post" action="<?= e(url($formAction)) ?>" class="form" novalidate>
        <?= csrf_field() ?>

        <div class="form-grid">
            <div class="field field--full">
                <label for="title">Título da obra <span class="required" aria-hidden="true">*</span></label>
                <input type="text" id="title" name="title" value="<?= e($project['title'] ?? '') ?>" maxlength="150" required
                       placeholder="ex.: Remodelação de cozinha — Rua das Flores"
                       class="<?= field_class($errors, 'title') ?>"
                       <?= isset($errors['title']) ? 'aria-invalid="true" aria-describedby="title-error"' : '' ?>>
                <?php if (isset($errors['title'])): ?>
                    <p class="field__error" id="title-error"><?= e($errors['title']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="client_id">Cliente <span class="required" aria-hidden="true">*</span></label>
                <select id="client_id" name="client_id" required class="<?= field_class($errors, 'client_id') ?>">
                    <option value="">Escolher cliente…</option>
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
                <label for="base_budget">Orçamento base (€) <span class="required" aria-hidden="true">*</span></label>
                <input type="text" id="base_budget" name="base_budget" value="<?= e($project['base_budget'] ?? '') ?>" required
                       inputmode="decimal" placeholder="ex.: 8 000,00"
                       class="<?= field_class($errors, 'base_budget') ?>"
                       <?= isset($errors['base_budget']) ? 'aria-invalid="true" aria-describedby="budget-error budget-help"' : 'aria-describedby="budget-help"' ?>>
                <?php if (isset($errors['base_budget'])): ?>
                    <p class="field__error" id="budget-error"><?= e($errors['base_budget']) ?></p>
                <?php endif; ?>
                <p class="field__help" id="budget-help">Aceita 8000, 8.000,00 ou 8000.00. Guardado em cêntimos.</p>
            </div>

            <div class="field">
                <label for="start_date">Data de início</label>
                <input type="date" id="start_date" name="start_date" value="<?= e($project['start_date'] ?? '') ?>"
                       class="<?= field_class($errors, 'start_date') ?>">
                <?php if (isset($errors['start_date'])): ?>
                    <p class="field__error"><?= e($errors['start_date']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="status">Estado</label>
                <select id="status" name="status">
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= e($status) ?>" <?= ($project['status'] ?? 'planeada') === $status ? 'selected' : '' ?>>
                            <?= e(Labels::projectStatus($status)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="field__help">Para arquivar usa a ação própria na página da obra.</p>
            </div>

            <div class="field field--full">
                <label for="description">Descrição / âmbito</label>
                <textarea id="description" name="description" rows="4" maxlength="2000"
                          placeholder="Trabalhos previstos, local, observações relevantes…"
                          class="<?= field_class($errors, 'description') ?>"><?= e($project['description'] ?? '') ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <p class="field__error"><?= e($errors['description']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <a class="btn btn--ghost" href="<?= e(url($isEdit ? '/obras/' . $project['id'] : '/obras')) ?>">Cancelar</a>
            <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Guardar alterações' : 'Criar obra' ?></button>
        </div>
    </form>
</section>
