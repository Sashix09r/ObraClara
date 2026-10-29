<?php

declare(strict_types=1);

use ObraClara\Support\Labels;
use ObraClara\Support\Money;

/**
 * @var array<string, mixed> $user
 * @var array<string, mixed> $client
 * @var list<array<string, mixed>> $projects
 */
$isManager = $user['role'] === 'gestor';
$isActive = (int) $client['active'] === 1;
?>
<nav class="breadcrumb" aria-label="Localização">
    <a href="<?= e(url('/clientes')) ?>">Clientes</a>
    <span aria-hidden="true">/</span>
    <span><?= e($client['name']) ?></span>
</nav>

<section class="page-head">
    <div>
        <div class="page-head__meta">
            <?php if ($isActive): ?>
                <span class="badge badge--active">Ativo</span>
            <?php else: ?>
                <span class="badge badge--archived">Arquivado</span>
            <?php endif; ?>
            <span class="text-muted text-sm">Cliente desde <?= e(format_date($client['created_at'])) ?></span>
        </div>
        <h1><?= e($client['name']) ?></h1>
        <p class="text-muted"><?= e((string) count($projects)) ?> obra(s) associada(s)</p>
    </div>
    <?php if ($isManager): ?>
        <div class="page-head__actions">
            <a class="btn btn--ghost" href="<?= e(url('/clientes/' . $client['id'] . '/editar')) ?>"><?= icon('edit') ?> Editar</a>
            <?php if ($isActive): ?>
                <form method="post" action="<?= e(url('/clientes/' . $client['id'] . '/arquivar')) ?>"
                      data-confirm="Arquivar este cliente? O histórico e as obras mantêm-se, e o cliente deixa de poder receber novas obras.">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn--danger-ghost"><?= icon('archive') ?> Arquivar</button>
                </form>
            <?php else: ?>
                <form method="post" action="<?= e(url('/clientes/' . $client['id'] . '/reativar')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn--primary">Reativar cliente</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<div class="grid grid--2-1">
    <section class="card" aria-labelledby="obras-cliente">
        <header class="card__head">
            <div>
                <h2 id="obras-cliente">Obras do cliente</h2>
                <p class="text-muted">Inclui obras arquivadas, para preservar a leitura do histórico.</p>
            </div>
            <?php if ($isManager && $isActive): ?>
                <a class="btn btn--sm btn--primary" href="<?= e(url('/obras/criar?cliente=' . $client['id'])) ?>"><?= icon('plus') ?> Nova obra</a>
            <?php endif; ?>
        </header>

        <?php if ($projects === []): ?>
            <div class="empty-state empty-state--sm">
                <h3>Sem obras associadas</h3>
                <p>Cria uma obra para começar a registar alterações de custo.</p>
            </div>
        <?php else: ?>
            <ul class="record-list">
                <?php foreach ($projects as $project): ?>
                    <li class="record-list__item">
                        <div>
                            <a class="cell-strong" href="<?= e(url('/obras/' . $project['id'])) ?>"><?= e($project['title']) ?></a>
                            <p class="cell-sub">
                                <?= e(Labels::projectStatus($project['status'])) ?> ·
                                Base <?= e(Money::format((int) $project['base_budget_cents'])) ?> ·
                                Atualizado <strong><?= e(Money::format((int) $project['updated_budget_cents'])) ?></strong>
                            </p>
                        </div>
                        <span class="badge badge--<?= e($project['status']) ?>"><?= e(Labels::projectStatus($project['status'])) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <aside class="card" aria-labelledby="contacto-title">
        <header class="card__head">
            <h2 id="contacto-title">Contacto</h2>
        </header>
        <dl class="definition-list">
            <div>
                <dt>Telefone</dt>
                <dd><?= $client['contact'] !== null ? e($client['contact']) : '<span class="text-muted">Não indicado</span>' ?></dd>
            </div>
            <div>
                <dt>Email</dt>
                <dd><?= $client['email'] !== null ? e($client['email']) : '<span class="text-muted">Não indicado</span>' ?></dd>
            </div>
            <div>
                <dt>Notas</dt>
                <dd><?= $client['notes'] !== null ? nl2br(e($client['notes'])) : '<span class="text-muted">Sem notas</span>' ?></dd>
            </div>
        </dl>
    </aside>
</div>
