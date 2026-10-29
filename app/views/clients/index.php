<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $user
 * @var list<array<string, mixed>> $clients
 * @var string $query
 * @var bool $includeArchived
 */
$isManager = $user['role'] === 'gestor';
?>
<section class="page-head">
    <div>
        <p class="eyebrow">Carteira</p>
        <h1>Clientes</h1>
        <p class="text-muted">Contactos e obras associadas. Arquivar preserva sempre o histórico.</p>
    </div>
    <?php if ($isManager): ?>
        <div class="page-head__actions">
            <a class="btn btn--primary" href="<?= e(url('/clientes/criar')) ?>"><?= icon('plus') ?> Novo cliente</a>
        </div>
    <?php endif; ?>
</section>

<form class="toolbar" method="get" action="<?= e(url('/clientes')) ?>" role="search">
    <div class="search-field">
        <?= icon('search') ?>
        <label class="sr-only" for="q">Pesquisar clientes</label>
        <input type="search" id="q" name="q" value="<?= e($query) ?>" placeholder="Pesquisar por nome, email ou contacto">
    </div>
    <label class="checkbox">
        <input type="checkbox" name="arquivados" value="1" <?= $includeArchived ? 'checked' : '' ?>>
        <span>Mostrar arquivados</span>
    </label>
    <button type="submit" class="btn btn--ghost">Filtrar</button>
    <?php if ($query !== '' || $includeArchived): ?>
        <a class="btn btn--link" href="<?= e(url('/clientes')) ?>">Limpar</a>
    <?php endif; ?>
</form>

<?php if ($clients === []): ?>
    <section class="card">
        <div class="empty-state">
            <span class="empty-state__icon"><?= icon('clients') ?></span>
            <?php if ($query !== ''): ?>
                <h2>Sem resultados para «<?= e($query) ?>»</h2>
                <p>Confirma a escrita ou pesquisa por outro nome, email ou contacto.</p>
                <a class="btn btn--ghost" href="<?= e(url('/clientes')) ?>">Limpar pesquisa</a>
            <?php else: ?>
                <h2>Ainda não existem clientes</h2>
                <p>Começa por registar o primeiro cliente para depois criar as obras.</p>
                <?php if ($isManager): ?>
                    <a class="btn btn--primary" href="<?= e(url('/clientes/criar')) ?>">Criar cliente</a>
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
                        <th scope="col">Cliente</th>
                        <th scope="col">Contacto</th>
                        <th scope="col">Email</th>
                        <th scope="col" class="text-center">Obras ativas</th>
                        <th scope="col">Estado</th>
                        <th scope="col" class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td data-label="Cliente">
                                <a class="cell-strong" href="<?= e(url('/clientes/' . $client['id'])) ?>"><?= e($client['name']) ?></a>
                            </td>
                            <td data-label="Contacto"><?= $client['contact'] !== null ? e($client['contact']) : '<span class="text-muted">—</span>' ?></td>
                            <td data-label="Email"><?= $client['email'] !== null ? e($client['email']) : '<span class="text-muted">—</span>' ?></td>
                            <td data-label="Obras ativas" class="text-center"><?= e((string) $client['active_projects']) ?></td>
                            <td data-label="Estado">
                                <?php if ((int) $client['active'] === 1): ?>
                                    <span class="badge badge--active">Ativo</span>
                                <?php else: ?>
                                    <span class="badge badge--archived">Arquivado</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Ações" class="text-right action-cell">
                                <a class="btn btn--sm btn--ghost" href="<?= e(url('/clientes/' . $client['id'])) ?>">Ver</a>
                                <?php if ($isManager): ?>
                                    <a class="btn btn--sm btn--ghost" href="<?= e(url('/clientes/' . $client['id'] . '/editar')) ?>">Editar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>
