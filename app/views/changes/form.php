<?php

declare(strict_types=1);

use ObraClara\Support\Money;

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
<nav class="breadcrumb" aria-label="Localização">
    <a href="<?= e(url('/obras')) ?>">Obras</a>
    <span aria-hidden="true">/</span>
    <a href="<?= e(url('/obras/' . $project['id'])) ?>"><?= e($project['title']) ?></a>
    <span aria-hidden="true">/</span>
    <span><?= $isEdit ? 'Editar rascunho' : 'Nova alteração' ?></span>
</nav>

<section class="page-head page-head--tight">
    <div>
        <p class="eyebrow"><?= $isEdit ? 'Rascunho' : 'Trabalho adicional' ?></p>
        <h1><?= $isEdit ? 'Editar rascunho' : 'Nova alteração' ?></h1>
        <p class="text-muted">
            Obra: <strong><?= e($project['title']) ?></strong> ·
            orçamento base <?= e(Money::format((int) $project['base_budget_cents'])) ?>
        </p>
    </div>
</section>

<div class="grid grid--2-1">
    <section class="card form-card">
        <form method="post" action="<?= e(url($formAction)) ?>" class="form" novalidate>
            <?= csrf_field() ?>

            <div class="field">
                <label for="title">Trabalho adicional <span class="required" aria-hidden="true">*</span></label>
                <input type="text" id="title" name="title" value="<?= e($change['title'] ?? '') ?>" maxlength="150" required
                       placeholder="ex.: Substituição do pavimento da sala"
                       class="<?= field_class($errors, 'title') ?>"
                       <?= isset($errors['title']) ? 'aria-invalid="true" aria-describedby="title-error"' : '' ?>>
                <?php if (isset($errors['title'])): ?>
                    <p class="field__error" id="title-error"><?= e($errors['title']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="amount">Custo adicional (€) <span class="required" aria-hidden="true">*</span></label>
                <input type="text" id="amount" name="amount" value="<?= e($change['amount'] ?? '') ?>" required
                       inputmode="decimal" placeholder="ex.: 650,00"
                       class="<?= field_class($errors, 'amount') ?>"
                       <?= isset($errors['amount']) ? 'aria-invalid="true" aria-describedby="amount-error amount-help"' : 'aria-describedby="amount-help"' ?>>
                <?php if (isset($errors['amount'])): ?>
                    <p class="field__error" id="amount-error"><?= e($errors['amount']) ?></p>
                <?php endif; ?>
                <p class="field__help" id="amount-help">Valor superior a zero, com até duas casas decimais. Ex.: 650,00.</p>
            </div>

            <div class="field">
                <label for="description">Descrição do trabalho</label>
                <textarea id="description" name="description" rows="5" maxlength="2000"
                          placeholder="Materiais, mão de obra, motivo da alteração…"
                          class="<?= field_class($errors, 'description') ?>"><?= e($change['description'] ?? '') ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <p class="field__error"><?= e($errors['description']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <a class="btn btn--ghost" href="<?= e(url('/obras/' . $project['id'])) ?>">Cancelar</a>
                <button type="submit" name="action" value="draft" class="btn btn--ghost">
                    <?= $isEdit ? 'Guardar rascunho' : 'Guardar como rascunho' ?>
                </button>
                <button type="submit" name="action" value="submit" class="btn btn--primary">
                    Guardar e submeter
                </button>
            </div>
        </form>
    </section>

    <aside class="card" aria-labelledby="fluxo-title">
        <header class="card__head">
            <h2 id="fluxo-title">Como funciona</h2>
        </header>

        <ol class="steps">
            <li>
                <span class="steps__dot" aria-hidden="true">1</span>
                <div>
                    <strong>Rascunho</strong>
                    <p>Podes editar e corrigir. Não entra no orçamento.</p>
                </div>
            </li>
            <li>
                <span class="steps__dot" aria-hidden="true">2</span>
                <div>
                    <strong>Pendente</strong>
                    <p>Submetida ao gestor; fica bloqueada para edição. Ainda não conta para o orçamento.</p>
                </div>
            </li>
            <li>
                <span class="steps__dot" aria-hidden="true">3</span>
                <div>
                    <strong>Aprovada ou rejeitada</strong>
                    <p>Decisão exclusiva do gestor, registada no histórico. Só a aprovação soma o valor à obra.</p>
                </div>
            </li>
        </ol>

        <p class="callout">
            <?= icon('alert') ?>
            <span><?= $isManager
                ? 'Como gestor, podes editar rascunhos de qualquer autor, mas a decisão é sempre registada com o teu nome.'
                : 'Só o gestor pode aprovar ou rejeitar. Podes criar e submeter as tuas propostas.' ?></span>
        </p>
    </aside>
</div>
