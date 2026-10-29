<?php

declare(strict_types=1);

use ObraClara\Support\Labels;
use ObraClara\Support\Money;

/**
 * @var array<string, mixed> $user
 * @var array<string, mixed> $change
 * @var array<string, mixed> $project
 * @var list<array<string, mixed>> $history
 * @var array<string, string> $errors
 * @var string $decision
 * @var string $note
 */
$afterApproval = (int) $project['updated_budget_cents'] + (int) $change['amount_cents'];
?>
<nav class="breadcrumb" aria-label="Localização">
    <a href="<?= e(url('/obras')) ?>">Obras</a>
    <span aria-hidden="true">/</span>
    <a href="<?= e(url('/obras/' . $project['id'])) ?>"><?= e($project['title']) ?></a>
    <span aria-hidden="true">/</span>
    <span>Decidir alteração</span>
</nav>

<section class="page-head page-head--tight">
    <div>
        <p class="eyebrow">Decisão do gestor</p>
        <h1>Decidir alteração</h1>
        <p class="text-muted">A decisão é definitiva e fica registada no histórico com o teu nome e a data.</p>
    </div>
</section>

<div class="grid grid--2-1">
    <section class="card" aria-labelledby="pedido-title">
        <header class="card__head">
            <div>
                <h2 id="pedido-title"><?= e($change['title']) ?></h2>
                <p class="text-muted">
                    Pedida por <?= e($change['author_name']) ?> ·
                    submetida em <?= e(format_datetime($change['submitted_at'])) ?>
                </p>
            </div>
            <span class="badge badge--<?= e($change['status']) ?>"><?= e(Labels::changeStatus($change['status'])) ?></span>
        </header>

        <div class="detail-grid">
            <div>
                <p class="detail-label">Custo adicional pedido</p>
                <p class="detail-value money money--strong"><?= e(Money::format((int) $change['amount_cents'])) ?></p>
            </div>
            <div>
                <p class="detail-label">Obra</p>
                <p class="detail-value"><a href="<?= e(url('/obras/' . $project['id'])) ?>"><?= e($project['title']) ?></a></p>
                <p class="cell-sub"><?= e($project['client_name']) ?></p>
            </div>
        </div>

        <?php if ($change['description'] !== null): ?>
            <div class="detail-block">
                <p class="detail-label">Descrição do trabalho</p>
                <p class="prose"><?= nl2br(e($change['description'])) ?></p>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= e(url('/alteracoes/' . $change['id'] . '/decidir')) ?>" class="form decide-form" novalidate>
            <?= csrf_field() ?>

            <fieldset class="field field--group">
                <legend>Decisão <span class="required" aria-hidden="true">*</span></legend>

                <?php if (isset($errors['decision'])): ?>
                    <p class="field__error"><?= e($errors['decision']) ?></p>
                <?php endif; ?>

                <div class="choice-grid">
                    <label class="choice <?= $decision === 'aprovar' ? 'is-selected' : '' ?>">
                        <input type="radio" name="decision" value="aprovar" <?= $decision === 'aprovar' ? 'checked' : '' ?>>
                        <span class="choice__title"><?= icon('check') ?> Aprovar</span>
                        <span class="choice__hint">O custo passa a contar para o orçamento atualizado.</span>
                    </label>

                    <label class="choice <?= $decision === 'rejeitar' ? 'is-selected' : '' ?>">
                        <input type="radio" name="decision" value="rejeitar" <?= $decision === 'rejeitar' ? 'checked' : '' ?>>
                        <span class="choice__title"><?= icon('close') ?> Rejeitar</span>
                        <span class="choice__hint">O orçamento da obra mantém-se igual.</span>
                    </label>
                </div>
            </fieldset>

            <div class="field">
                <label for="note">Observação da decisão</label>
                <textarea id="note" name="note" rows="3" maxlength="800"
                          placeholder="ex.: Aprovado pelo cliente por email em 02/11/2026."
                          class="<?= field_class($errors, 'note') ?>"
                          <?= isset($errors['note']) ? 'aria-invalid="true" aria-describedby="note-error"' : '' ?>><?= e($note) ?></textarea>
                <?php if (isset($errors['note'])): ?>
                    <p class="field__error" id="note-error"><?= e($errors['note']) ?></p>
                <?php endif; ?>
                <p class="field__help">Obrigatória ao rejeitar. Fica guardada no histórico.</p>
            </div>

            <div class="form-actions">
                <a class="btn btn--ghost" href="<?= e(url('/obras/' . $project['id'])) ?>">Cancelar</a>
                <button type="submit" class="btn btn--primary">Registar decisão</button>
            </div>
        </form>
    </section>

    <aside class="stack">
        <section class="card budget-card budget-card--total">
            <p class="budget-card__label">Impacto no orçamento</p>
            <dl class="budget-breakdown">
                <div><dt>Orçamento base</dt><dd><?= e(Money::format((int) $project['base_budget_cents'])) ?></dd></div>
                <div><dt>Custos já aprovados</dt><dd><?= e(Money::format((int) $project['approved_cents'])) ?></dd></div>
                <div class="budget-breakdown__total">
                    <dt>Atualizado hoje</dt><dd><?= e(Money::format((int) $project['updated_budget_cents'])) ?></dd>
                </div>
                <div class="budget-breakdown__future">
                    <dt>Se aprovar</dt><dd><?= e(Money::format($afterApproval)) ?></dd>
                </div>
            </dl>
        </section>

        <section class="card">
            <header class="card__head">
                <h2>Histórico da alteração</h2>
            </header>
            <?php if ($history === []): ?>
                <p class="text-muted text-sm">Sem eventos registados.</p>
            <?php else: ?>
                <ol class="event-list event-list--compact">
                    <?php foreach ($history as $event): ?>
                        <li class="event-list__item">
                            <span class="badge <?= e(Labels::eventClass($event['action'])) ?>"><?= e(Labels::eventAction($event['action'])) ?></span>
                            <div>
                                <p class="event-list__meta">
                                    <?= e($event['user_name']) ?> · <?= e(format_datetime($event['created_at'])) ?>
                                </p>
                                <?php if ($event['from_status'] !== null): ?>
                                    <p class="event-list__note">
                                        <?= e(Labels::changeStatus($event['from_status'])) ?> → <?= e(Labels::changeStatus($event['to_status'])) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </section>
    </aside>
</div>
