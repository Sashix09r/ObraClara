<?php

declare(strict_types=1);

use ObraClara\Support\Labels;
use ObraClara\Support\Money;

/**
 * @var array<string, mixed> $user
 * @var array<string, mixed> $project
 * @var list<array<string, mixed>> $changes
 * @var array<int, list<array<string, mixed>>> $eventsByChange
 */
$isManager = $user['role'] === 'gestor';
$isArchived = $project['status'] === 'arquivada';

$approvedCount = 0;
$pendingCount = 0;
$draftCount = 0;
foreach ($changes as $change) {
    if ($change['status'] === 'aprovada') {
        $approvedCount++;
    } elseif ($change['status'] === 'pendente') {
        $pendingCount++;
    } elseif ($change['status'] === 'rascunho') {
        $draftCount++;
    }
}

$totalEvents = 0;
foreach ($eventsByChange as $events) {
    $totalEvents += count($events);
}
?>
<nav class="breadcrumb" aria-label="Localização">
    <a href="<?= e(url('/obras')) ?>">Obras</a>
    <span aria-hidden="true">/</span>
    <a href="<?= e(url('/clientes/' . $project['client_id'])) ?>"><?= e($project['client_name']) ?></a>
    <span aria-hidden="true">/</span>
    <span><?= e($project['title']) ?></span>
</nav>

<section class="page-head">
    <div>
        <div class="page-head__meta">
            <span class="badge badge--<?= e($project['status']) ?>"><?= e(Labels::projectStatus($project['status'])) ?></span>
            <?php if ($project['start_date'] !== null): ?>
                <span class="text-muted text-sm">Início <?= e(format_date($project['start_date'])) ?></span>
            <?php endif; ?>
        </div>
        <h1><?= e($project['title']) ?></h1>
        <p class="text-muted">
            Cliente: <a href="<?= e(url('/clientes/' . $project['client_id'])) ?>"><?= e($project['client_name']) ?></a>
        </p>
    </div>
    <div class="page-head__actions">
        <?php if (!$isArchived): ?>
            <a class="btn btn--primary" href="<?= e(url('/obras/' . $project['id'] . '/alteracoes/criar')) ?>">
                <?= icon('plus') ?> Nova alteração
            </a>
        <?php endif; ?>
        <?php if ($isManager): ?>
            <a class="btn btn--ghost" href="<?= e(url('/obras/' . $project['id'] . '/editar')) ?>"><?= icon('edit') ?> Editar obra</a>
        <?php endif; ?>
    </div>
</section>

<div class="budget-grid">
    <article class="card budget-card">
        <p class="budget-card__label">Orçamento base</p>
        <p class="budget-card__value"><?= e(Money::format((int) $project['base_budget_cents'])) ?></p>
        <p class="budget-card__hint">Valor contratado inicialmente</p>
    </article>
    <article class="card budget-card">
        <p class="budget-card__label">Custos aprovados</p>
        <p class="budget-card__value">+ <?= e(Money::format((int) $project['approved_cents'])) ?></p>
        <p class="budget-card__hint"><?= e((string) $approvedCount) ?> alteração(ões) aprovada(s)</p>
    </article>
    <article class="card budget-card budget-card--total">
        <p class="budget-card__label">Orçamento atualizado</p>
        <p class="budget-card__value"><?= e(Money::format((int) $project['updated_budget_cents'])) ?></p>
        <p class="budget-card__hint">Base + alterações aprovadas</p>
    </article>
</div>

<?php if ($project['description'] !== null): ?>
    <section class="card card--quiet">
        <h2 class="section-title">Âmbito da obra</h2>
        <p class="prose"><?= nl2br(e($project['description'])) ?></p>
    </section>
<?php endif; ?>

<section class="card" aria-labelledby="alteracoes-title">
    <header class="card__head">
        <div>
            <h2 id="alteracoes-title">Alterações ao âmbito</h2>
            <p class="text-muted">
                <?= e((string) count($changes)) ?> no total ·
                <?= e((string) $pendingCount) ?> pendente(s) ·
                <?= e((string) $draftCount) ?> rascunho(s)
            </p>
        </div>
        <span class="count-pill"><?= e((string) count($changes)) ?></span>
    </header>

    <?php if ($changes === []): ?>
        <div class="empty-state">
            <span class="empty-state__icon"><?= icon('projects') ?></span>
            <h3>Ainda não existem alterações</h3>
            <p>Registe um trabalho adicional ou uma alteração de material para submeter ao gestor.</p>
            <?php if (!$isArchived): ?>
                <a class="btn btn--primary" href="<?= e(url('/obras/' . $project['id'] . '/alteracoes/criar')) ?>">Criar alteração</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table table--changes">
                <thead>
                    <tr>
                        <th scope="col">Alteração</th>
                        <th scope="col" class="text-right">Valor</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Registo</th>
                        <th scope="col" class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($changes as $change): ?>
                        <?php
                        $canEdit = $change['status'] === 'rascunho'
                            && ($isManager || (int) $change['created_by'] === (int) $user['id']);
                        ?>
                        <tr id="alteracao-<?= e((string) $change['id']) ?>">
                            <td data-label="Alteração">
                                <strong><?= e($change['title']) ?></strong>
                                <?php if ($change['description'] !== null): ?>
                                    <span class="cell-sub"><?= e(mb_strimwidth($change['description'], 0, 110, '…')) ?></span>
                                <?php endif; ?>
                                <?php if ($change['status'] === 'pendente'): ?>
                                    <span class="cell-sub cell-sub--note">Pendente: ainda não conta para o orçamento.</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Valor" class="text-right money money--strong"><?= e(Money::format((int) $change['amount_cents'])) ?></td>
                            <td data-label="Estado">
                                <span class="badge badge--<?= e($change['status']) ?>"><?= e(Labels::changeStatus($change['status'])) ?></span>
                            </td>
                            <td data-label="Registo">
                                <span class="text-sm"><?= e($change['author_name']) ?></span>
                                <span class="cell-sub"><?= e(format_datetime($change['created_at'])) ?></span>
                                <?php if ($change['decided_at'] !== null): ?>
                                    <span class="cell-sub">
                                        Decidida por <?= e($change['decider_name'] ?? '—') ?> em <?= e(format_datetime($change['decided_at'])) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Ações" class="text-right action-cell">
                                <?php if ($change['status'] === 'rascunho' && $canEdit): ?>
                                    <a class="btn btn--sm btn--ghost" href="<?= e(url('/alteracoes/' . $change['id'] . '/editar')) ?>"><?= icon('edit') ?> Editar</a>
                                    <form method="post" action="<?= e(url('/alteracoes/' . $change['id'] . '/submeter')) ?>"
                                          data-confirm="Submeter «<?= e($change['title']) ?>» ao gestor? Depois de submetida, a alteração deixa de poder ser editada.">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn--sm btn--primary">Submeter</button>
                                    </form>
                                <?php elseif ($change['status'] === 'pendente' && $isManager): ?>
                                    <a class="btn btn--sm btn--primary" href="<?= e(url('/alteracoes/' . $change['id'] . '/decidir')) ?>">Decidir</a>
                                <?php elseif ($change['status'] === 'pendente'): ?>
                                    <span class="text-muted text-sm">Aguarda gestor</span>
                                <?php else: ?>
                                    <span class="text-muted text-sm">Sem ações</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($change['decided_at'] !== null && $change['decision_note'] !== null): ?>
                            <tr class="row-note">
                                <td colspan="5">
                                    <strong>Observação da decisão:</strong> <?= e($change['decision_note']) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="table-footnote">
            Apenas alterações aprovadas entram no orçamento atualizado. Rascunhos e pendentes não alteram o valor.
        </p>
    <?php endif; ?>
</section>

<section class="card" aria-labelledby="historico-obra">
    <header class="card__head">
        <div>
            <h2 id="historico-obra">Histórico da obra</h2>
            <p class="text-muted">Registo imutável de criação, submissão e decisão, com autor e data.</p>
        </div>
        <span class="count-pill"><?= e((string) $totalEvents) ?></span>
    </header>

    <?php if ($totalEvents === 0): ?>
        <div class="empty-state empty-state--sm">
            <p>Ainda não existem eventos nesta obra.</p>
        </div>
    <?php else: ?>
        <ol class="event-list">
            <?php foreach ($changes as $change): ?>
                <?php foreach ($eventsByChange[(int) $change['id']] ?? [] as $event): ?>
                    <li class="event-list__item">
                        <span class="badge <?= e(Labels::eventClass($event['action'])) ?>"><?= e(Labels::eventAction($event['action'])) ?></span>
                        <div>
                            <p class="event-list__title"><?= e($event['change_title']) ?></p>
                            <p class="event-list__meta">
                                <?= e($event['user_name']) ?>
                                <?php if ($event['from_status'] !== null): ?>
                                    · <?= e(Labels::changeStatus($event['from_status'])) ?> → <?= e(Labels::changeStatus($event['to_status'])) ?>
                                <?php endif; ?>
                                · <?= e(format_datetime($event['created_at'])) ?>
                            </p>
                            <?php if ($event['note'] !== null): ?>
                                <p class="event-list__note"><?= e($event['note']) ?></p>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</section>

<?php if ($isManager): ?>
    <section class="card danger-zone">
        <div>
            <h2 class="section-title"><?= $isArchived ? 'Reativar obra' : 'Arquivar obra' ?></h2>
            <p class="text-muted">
                <?php if ($isArchived): ?>
                    A obra está arquivada. Reativar volta a colocá-la no estado «Planeada», com todo o histórico.
                <?php else: ?>
                    Arquivar mantém os dados e o histórico, mas impede novas alterações. Só é possível sem alterações pendentes.
                <?php endif; ?>
            </p>
        </div>
        <?php if ($isArchived): ?>
            <form method="post" action="<?= e(url('/obras/' . $project['id'] . '/reativar')) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn--primary">Reativar obra</button>
            </form>
        <?php else: ?>
            <form method="post" action="<?= e(url('/obras/' . $project['id'] . '/arquivar')) ?>"
                  data-confirm="Arquivar esta obra? Deixa de aceitar novas alterações até ser reativada.">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn--danger-ghost"><?= icon('archive') ?> Arquivar obra</button>
            </form>
        <?php endif; ?>
    </section>
<?php endif; ?>
