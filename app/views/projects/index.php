<?php

declare(strict_types=1);

use ObraClara\Support\Labels;
use ObraClara\Support\Money;

/**
 * @var array<string, mixed> $user
 * @var list<array<string, mixed>> $projects
 * @var string $query
 * @var string $status
 */
$isManager = $user['role'] === 'gestor';
$statuses = ['' => 'Todas (exceto arquivadas)', 'planeada' => 'Planeadas', 'em_curso' => 'Em curso', 'concluida' => 'Concluídas', 'arquivada' => 'Arquivadas'];
?>
<section class="page-head">
    <div>
        <p class="eyebrow">Portefólio</p>
        <h1>Obras</h1>
        <p class="text-muted">Cada obra mostra o orçamento base, os custos aprovados e o total atualizado.</p>
    </div>
    <?php if ($isManager): ?>
        <div class="page-head__actions">
            <a class="btn btn--primary" href="<?= e(url('/obras/criar')) ?>"><?= icon('plus') ?> Nova obra</a>
        </div>
    <?php endif; ?>
</section>

<form class="toolbar" method="get" action="<?= e(url('/obras')) ?>" role="search">
    <div class="search-field">
        <?= icon('search') ?>
        <label class="sr-only" for="q">Pesquisar obras</label>
        <input type="search" id="q" name="q" value="<?= e($query) ?>" placeholder="Pesquisar por obra, cliente ou descrição">
    </div>

    <div class="select-field">
        <label class="sr-only" for="estado">Estado</label>
        <select id="estado" name="estado">
            <?php foreach ($statuses as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <button type="submit" class="btn btn--ghost">Filtrar</button>
    <?php if ($query !== '' || $status !== ''): ?>
        <a class="btn btn--link" href="<?= e(url('/obras')) ?>">Limpar</a>
    <?php endif; ?>
</form>

<?php if ($projects === []): ?>
    <section class="card">
        <div class="empty-state">
            <span class="empty-state__icon"><?= icon('projects') ?></span>
            <?php if ($query !== '' || $status !== ''): ?>
                <h2>Sem obras para este filtro</h2>
                <p>Experimenta outra pesquisa ou remove o filtro de estado.</p>
                <a class="btn btn--ghost" href="<?= e(url('/obras')) ?>">Limpar filtros</a>
            <?php else: ?>
                <h2>Ainda não existem obras</h2>
                <p>Cria uma obra associada a um cliente para começares a controlar o orçamento.</p>
                <?php if ($isManager): ?>
                    <a class="btn btn--primary" href="<?= e(url('/obras/criar')) ?>">Criar obra</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
<?php else: ?>
    <section class="card card--flush">
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
                        <th scope="col" class="text-right"><span class="sr-only">Ações</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td data-label="Obra">
                                <a class="cell-strong" href="<?= e(url('/obras/' . $project['id'])) ?>"><?= e($project['title']) ?></a>
                                <?php if ($project['start_date'] !== null): ?>
                                    <span class="cell-sub">Início <?= e(format_date($project['start_date'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Cliente"><?= e($project['client_name']) ?></td>
                            <td data-label="Estado">
                                <span class="badge badge--<?= e($project['status']) ?>"><?= e(Labels::projectStatus($project['status'])) ?></span>
                            </td>
                            <td data-label="Base" class="text-right money"><?= e(Money::format((int) $project['base_budget_cents'])) ?></td>
                            <td data-label="Aprovado" class="text-right money"><?= e(Money::format((int) $project['approved_cents'])) ?></td>
                            <td data-label="Atualizado" class="text-right money money--strong"><?= e(Money::format((int) $project['updated_budget_cents'])) ?></td>
                            <td data-label="Ação" class="text-right">
                                <a class="btn btn--sm btn--ghost" href="<?= e(url('/obras/' . $project['id'])) ?>">Abrir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>
