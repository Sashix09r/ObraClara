<?php

declare(strict_types=1);

use ObraClara\Support\Labels;
use ObraClara\Support\Money;

/**
 * @var array<string, mixed> $user
 * @var list<array<string, mixed>> $events
 */
?>
<section class="page-head">
    <div>
        <p class="eyebrow">Auditoria</p>
        <h1>Histórico de eventos</h1>
        <p class="text-muted">Criação, edição, submissão e decisão de alterações — com autor, data e transição de estado.</p>
    </div>
</section>

<section class="card card--flush">
    <?php if ($events === []): ?>
        <div class="empty-state">
            <span class="empty-state__icon"><?= icon('history') ?></span>
            <h2>Ainda não existem eventos</h2>
            <p>Os eventos aparecem aqui assim que forem criadas alterações.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Data</th>
                        <th scope="col">Ação</th>
                        <th scope="col">Alteração</th>
                        <th scope="col">Obra / cliente</th>
                        <th scope="col" class="text-right">Valor</th>
                        <th scope="col">Utilizador</th>
                        <th scope="col">Observação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td data-label="Data"><?= e(format_datetime($event['created_at'])) ?></td>
                            <td data-label="Ação">
                                <span class="badge <?= e(Labels::eventClass($event['action'])) ?>"><?= e(Labels::eventAction($event['action'])) ?></span>
                                <?php if ($event['from_status'] !== null): ?>
                                    <span class="cell-sub">
                                        <?= e(Labels::changeStatus($event['from_status'])) ?> → <?= e(Labels::changeStatus($event['to_status'])) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Alteração"><?= e($event['change_title']) ?></td>
                            <td data-label="Obra">
                                <a href="<?= e(url('/obras/' . $event['project_id'])) ?>"><?= e($event['project_title']) ?></a>
                                <span class="cell-sub"><?= e($event['client_name']) ?></span>
                            </td>
                            <td data-label="Valor" class="text-right money"><?= e(Money::format((int) $event['amount_cents'])) ?></td>
                            <td data-label="Utilizador">
                                <?= e($event['user_name']) ?>
                                <span class="cell-sub"><?= $event['user_role'] === 'gestor' ? 'Gestor' : 'Colaborador' ?></span>
                            </td>
                            <td data-label="Observação">
                                <?= $event['note'] !== null ? e($event['note']) : '<span class="text-muted">—</span>' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="table-footnote">Últimos <?= e((string) count($events)) ?> eventos, do mais recente para o mais antigo.</p>
    <?php endif; ?>
</section>
