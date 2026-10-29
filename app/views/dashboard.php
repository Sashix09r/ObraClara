<?php

declare(strict_types=1);

use ObraClara\Support\Labels;
use ObraClara\Support\Money;

/**
 * @var array<string, mixed> $user
 * @var array<string, mixed> $stats
 */
$isManager = $user['role'] === 'gestor';
$firstName = explode(' ', (string) $user['name'])[0];
$hour = (int) date('G');
$greeting = $hour < 12 ? 'Bom dia' : ($hour < 20 ? 'Boa tarde' : 'Boa noite');
?>
<section class="page-head">
    <div>
        <p class="eyebrow">Visão geral</p>
        <h1><?= e($greeting) ?>, <?= e($firstName) ?></h1>
        <p class="text-muted">Estado atual das obras e das alterações à espera de decisão.</p>
    </div>
    <?php if ($isManager): ?>
        <div class="page-head__actions">
            <a class="btn btn--ghost" href="<?= e(url('/clientes/criar')) ?>">Novo cliente</a>
            <a class="btn btn--primary" href="<?= e(url('/obras/criar')) ?>">
                <?= icon('plus') ?> Nova obra
            </a>
        </div>
    <?php endif; ?>
</section>

<div class="kpi-grid">
    <article class="card kpi">
        <span class="kpi__icon"><?= icon('projects') ?></span>
        <p class="kpi__label">Obras ativas</p>
        <p class="kpi__value"><?= e((string) $stats['active_projects']) ?></p>
        <p class="kpi__hint"><?= e((string) $stats['active_clients']) ?> clientes ativos</p>
    </article>

    <article class="card kpi<?= $stats['pending_changes'] > 0 ? ' kpi--attention' : '' ?>">
        <span class="kpi__icon"><?= icon('clock') ?></span>
        <p class="kpi__label">Alterações pendentes</p>
        <p class="kpi__value"><?= e((string) $stats['pending_changes']) ?></p>
        <p class="kpi__hint"><?= e((string) $stats['draft_changes']) ?> em rascunho</p>
    </article>

    <article class="card kpi">
        <span class="kpi__icon"><?= icon('check') ?></span>
        <p class="kpi__label">Custos aprovados</p>
        <p class="kpi__value kpi__value--money"><?= e(Money::format((int) $stats['approved_extra'])) ?></p>
        <p class="kpi__hint">soma das alterações aprovadas</p>
    </article>

    <article class="card kpi kpi--total">
        <span class="kpi__icon"><?= icon('money') ?></span>
        <p class="kpi__label">Orçamento previsto</p>
        <p class="kpi__value kpi__value--money"><?= e(Money::format((int) $stats['forecast_total'])) ?></p>
        <p class="kpi__hint">obras não arquivadas</p>
    </article>
</div>

<div class="grid grid--2-1">
    <section class="card" aria-labelledby="pendentes-title">
        <header class="card__head">
            <div>
                <h2 id="pendentes-title">Alterações por decidir</h2>
                <p class="text-muted">Só o gestor aprova ou rejeita. Enquanto pendentes, não alteram o orçamento.</p>
            </div>
            <span class="count-pill"><?= e((string) count($stats['pending'])) ?></span>
        </header>

        <?php if ($stats['pending'] === []): ?>
            <div class="empty-state">
                <span class="empty-state__icon"><?= icon('check') ?></span>
                <h3>Nada pendente</h3>
                <p>Não existem alterações à espera de decisão neste momento.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Alteração</th>
                            <th scope="col">Obra / cliente</th>
                            <th scope="col" class="text-right">Valor</th>
                            <th scope="col">Submetida</th>
                            <th scope="col"><span class="sr-only">Ações</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['pending'] as $change): ?>
                            <tr>
                                <td data-label="Alteração">
                                    <strong><?= e($change['title']) ?></strong>
                                    <span class="cell-sub">por <?= e($change['author_name']) ?></span>
                                </td>
                                <td data-label="Obra">
                                    <a href="<?= e(url('/obras/' . $change['project_id'])) ?>"><?= e($change['project_title']) ?></a>
                                    <span class="cell-sub"><?= e($change['client_name']) ?></span>
                                </td>
                                <td data-label="Valor" class="text-right money"><?= e(Money::format((int) $change['amount_cents'])) ?></td>
                                <td data-label="Submetida"><?= e(format_datetime($change['submitted_at'])) ?></td>
                                <td data-label="Ação" class="text-right">
                                    <?php if ($isManager): ?>
                                        <a class="btn btn--sm btn--primary" href="<?= e(url('/alteracoes/' . $change['id'] . '/decidir')) ?>">Decidir</a>
                                    <?php else: ?>
                                        <span class="text-muted text-sm">Aguarda gestor</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <aside class="card" aria-labelledby="atividade-title">
        <header class="card__head">
            <h2 id="atividade-title">Atividade recente</h2>
        </header>

        <?php if ($stats['events'] === []): ?>
            <div class="empty-state empty-state--sm">
                <p>Ainda não existem eventos registados.</p>
            </div>
        <?php else: ?>
            <ul class="timeline">
                <?php foreach ($stats['events'] as $event): ?>
                    <li class="timeline__item">
                        <span class="badge <?= e(Labels::eventClass($event['action'])) ?>"><?= e(Labels::eventAction($event['action'])) ?></span>
                        <p class="timeline__title"><?= e($event['change_title']) ?></p>
                        <p class="timeline__meta">
                            <?= e($event['user_name']) ?> · <?= e(format_datetime($event['created_at'])) ?>
                        </p>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a class="link-arrow" href="<?= e(url('/historico')) ?>">Ver histórico completo <?= icon('arrow') ?></a>
        <?php endif; ?>
    </aside>
</div>

<section class="card" aria-labelledby="obras-title">
    <header class="card__head">
        <div>
            <h2 id="obras-title">Obras em acompanhamento</h2>
            <p class="text-muted">Orçamento atualizado = base + alterações aprovadas.</p>
        </div>
        <a class="link-arrow" href="<?= e(url('/obras')) ?>">Todas as obras <?= icon('arrow') ?></a>
    </header>

    <?php if ($stats['projects'] === []): ?>
        <div class="empty-state">
            <span class="empty-state__icon"><?= icon('projects') ?></span>
            <h3>Ainda não há obras</h3>
            <p>Cria o primeiro cliente e a primeira obra para começares a registar alterações.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Obra</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Estado</th>
                        <th scope="col" class="text-right">Base</th>
                        <th scope="col" class="text-right">Aprovado</th>
                        <th scope="col" class="text-right">Atualizado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['projects'] as $project): ?>
                        <tr>
                            <td data-label="Obra">
                                <a class="cell-strong" href="<?= e(url('/obras/' . $project['id'])) ?>"><?= e($project['title']) ?></a>
                            </td>
                            <td data-label="Cliente"><?= e($project['client_name']) ?></td>
                            <td data-label="Estado">
                                <span class="badge badge--<?= e($project['status']) ?>"><?= e(Labels::projectStatus($project['status'])) ?></span>
                            </td>
                            <td data-label="Base" class="text-right money"><?= e(Money::format((int) $project['base_budget_cents'])) ?></td>
                            <td data-label="Aprovado" class="text-right money"><?= e(Money::format((int) $project['approved_cents'])) ?></td>
                            <td data-label="Atualizado" class="text-right money money--strong"><?= e(Money::format((int) $project['updated_budget_cents'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
