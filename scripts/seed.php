<?php

declare(strict_types=1);

/**
 * Dados fictícios de demonstração da ObraClara V2.
 *
 * Uso:
 *   php scripts/seed.php --reset   (recria a base de dados de demonstração)
 *
 * O --reset só elimina o ficheiro SQLite configurado em config/config.php
 * (nunca os dados de outro projeto). Sem --reset o script recusa correr
 * sobre uma base que já tenha utilizadores.
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use ObraClara\Support\Database;
use ObraClara\Support\Migrator;

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDERR, "ERRO: a extensão pdo_sqlite não está ativa no PHP.\n");
    exit(1);
}

$reset = in_array('--reset', $argv, true);
$path = Database::path();

if (is_file($path)) {
    $pdo = Database::pdo();
    $hasUsers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;

    if ($hasUsers && !$reset) {
        fwrite(STDERR, "A base de dados já contém dados. Usa --reset para recriar a demonstração.\n");
        exit(1);
    }

    if ($reset) {
        // Proteção: só apaga um ficheiro SQLite cujo nome contenha "obraclara".
        if (!str_contains(basename($path), 'obraclara')) {
            fwrite(STDERR, "Recusado: o ficheiro não parece ser a base de demonstração da ObraClara.\n");
            exit(1);
        }

        // No Windows o ficheiro fica bloqueado enquanto existir uma referência
        // à ligação PDO, por isso é necessário libertar as duas referências.
        unset($pdo);
        Database::setPdo(null);
        gc_collect_cycles();

        if (!unlink($path)) {
            fwrite(STDERR, "Não foi possível remover {$path}. Fecha outras ligações e tenta de novo.\n");
            exit(1);
        }

        echo "Base anterior removida: {$path}\n";
    }
}

$pdo = Database::pdo();
$schema = file_get_contents(OBRACLARA_ROOT . '/database/schema.sql');
if ($schema === false) {
    fwrite(STDERR, "database/schema.sql não encontrado.\n");
    exit(1);
}
$pdo->exec($schema);

// O schema já inclui as colunas e tabelas atuais, mas o registo de migrações
// tem de ficar preenchido para que uma atualização futura saiba o que já correu.
Migrator::run($pdo);

$nowStamp = '2026-10-29 09:00:00';

/* --------------------------------------------------------------------------
 * Utilizadores (credenciais fictícias, apenas para demonstração local)
 * -------------------------------------------------------------------------- */

$insertUser = $pdo->prepare(
    'INSERT INTO users (name, email, password_hash, role, active, created_at)
     VALUES (:name, :email, :hash, :role, 1, :created_at)'
);

$users = [
    'gestor' => [
        'name'     => 'Ana Ferreira',
        'email'    => 'gestor@obraclara.pt',
        'password' => 'Gestor2026!',
        'role'     => 'gestor',
    ],
    'colaborador' => [
        'name'     => 'Rui Marques',
        'email'    => 'colaborador@obraclara.pt',
        'password' => 'Colab2026!',
        'role'     => 'colaborador',
    ],
];

$userIds = [];
foreach ($users as $key => $user) {
    $insertUser->execute([
        'name'       => $user['name'],
        'email'      => $user['email'],
        'hash'       => password_hash($user['password'], PASSWORD_DEFAULT),
        'role'       => $user['role'],
        'created_at' => '2026-06-01 09:00:00',
    ]);
    $userIds[$key] = (int) $pdo->lastInsertId();
}

/* --------------------------------------------------------------------------
 * Clientes
 * -------------------------------------------------------------------------- */

$insertClient = $pdo->prepare(
    'INSERT INTO clients (name, contact, email, notes, active, created_at, updated_at)
     VALUES (:name, :contact, :email, :notes, :active, :created_at, :updated_at)'
);

$clients = [
    [
        'name' => 'Maria Sousa', 'contact' => '912 345 678', 'email' => 'maria.sousa@exemplo.pt',
        'notes' => 'Remodelação integral de habitação. Contacto preferencial por telefone.',
        'active' => 1, 'created_at' => '2026-06-12 10:20:00',
    ],
    [
        'name' => 'João e Rita Almeida', 'contact' => '934 210 887', 'email' => 'joao.almeida@exemplo.pt',
        'notes' => 'Apartamento T2 na Avenida do Parque. Obra executada com moradores ausentes.',
        'active' => 1, 'created_at' => '2026-06-28 15:05:00',
    ],
    [
        'name' => 'Condomínio Jardins do Lima', 'contact' => '218 445 220', 'email' => 'administracao@jardinslima.exemplo.pt',
        'notes' => 'Contacto: administração do condomínio. Faturas em nome do condomínio.',
        'active' => 1, 'created_at' => '2026-07-03 11:40:00',
    ],
    [
        'name' => 'Café Central', 'contact' => '926 778 110', 'email' => 'gerencia@cafecentral.exemplo.pt',
        'notes' => 'Obra fora do horário de funcionamento (após as 19h).',
        'active' => 1, 'created_at' => '2026-09-14 09:15:00',
    ],
    [
        'name' => 'Sofia Matos', 'contact' => '961 004 233', 'email' => 'sofia.matos@exemplo.pt',
        'notes' => 'Moradia em Sintra. Pintura exterior marcada para outubro.',
        'active' => 1, 'created_at' => '2026-09-25 16:30:00',
    ],
    [
        'name' => 'Fernando Rocha', 'contact' => '917 552 981', 'email' => 'fernando.rocha@exemplo.pt',
        'notes' => 'Cliente histórico. Processo concluído em 2024; mantido apenas para arquivo.',
        'active' => 0, 'created_at' => '2026-02-10 14:00:00',
    ],
];

$clientIds = [];
foreach ($clients as $client) {
    $insertClient->execute([
        'name'       => $client['name'],
        'contact'    => $client['contact'],
        'email'      => $client['email'],
        'notes'      => $client['notes'],
        'active'     => $client['active'],
        'created_at' => $client['created_at'],
        'updated_at' => $client['created_at'],
    ]);
    $clientIds[] = (int) $pdo->lastInsertId();
}

/* --------------------------------------------------------------------------
 * Obras
 * -------------------------------------------------------------------------- */

$insertProject = $pdo->prepare(
    'INSERT INTO projects
        (client_id, title, description, base_budget_cents, start_date, status, created_by, created_at, updated_at)
     VALUES
        (:client_id, :title, :description, :base_budget_cents, :start_date, :status, :created_by, :created_at, :updated_at)'
);

$projects = [
    [
        'client' => 0,
        'title' => 'Remodelação T3 — Rua das Flores',
        'description' => 'Remodelação integral de apartamento T3 com 118 m²: cozinha, duas casas de banho, pavimentos e pintura. Prazo previsto de 10 semanas.',
        'budget' => 800000,
        'start' => '2026-09-15',
        'status' => 'em_curso',
        'created_at' => '2026-08-20 10:00:00',
    ],
    [
        'client' => 1,
        'title' => 'Remodelação de cozinha e sala',
        'description' => 'Substituição de mobiliário de cozinha, bancada, pavimento flutuante e nova iluminação na sala.',
        'budget' => 1250000,
        'start' => '2026-08-04',
        'status' => 'em_curso',
        'created_at' => '2026-07-15 09:30:00',
    ],
    [
        'client' => 2,
        'title' => 'Impermeabilização de cobertura',
        'description' => 'Reparação e impermeabilização da cobertura do bloco B, incluindo rufos e caleiras.',
        'budget' => 940000,
        'start' => '2026-07-20',
        'status' => 'concluida',
        'created_at' => '2026-07-01 14:10:00',
    ],
    [
        'client' => 3,
        'title' => 'Renovação do WC social',
        'description' => 'Renovação do quarto de banho social do café: azulejos, louças, torneira e espelho.',
        'budget' => 320000,
        'start' => '2026-10-05',
        'status' => 'em_curso',
        'created_at' => '2026-09-20 11:25:00',
    ],
    [
        'client' => 4,
        'title' => 'Pintura exterior de moradia',
        'description' => 'Preparação de superfícies e pintura exterior de moradia unifamiliar, incluindo beirados.',
        'budget' => 460000,
        'start' => '2026-10-12',
        'status' => 'planeada',
        'created_at' => '2026-10-02 16:45:00',
    ],
    [
        'client' => 0,
        'title' => 'Ampliação de garagem',
        'description' => 'Ampliação da garagem em 18 m², com nova laje e porta seccional.',
        'budget' => 1500000,
        'start' => '2026-11-02',
        'status' => 'planeada',
        'created_at' => '2026-10-18 10:05:00',
    ],
    [
        'client' => 5,
        'title' => 'Substituição de janelas',
        'description' => 'Processo concluído e arquivado. Substituição de cinco janelas por caixilharia PVC.',
        'budget' => 270000,
        'start' => '2026-03-02',
        'status' => 'arquivada',
        'created_at' => '2026-02-18 12:00:00',
    ],
];

$projectIds = [];
foreach ($projects as $project) {
    $insertProject->execute([
        'client_id'         => $clientIds[$project['client']],
        'title'             => $project['title'],
        'description'       => $project['description'],
        'base_budget_cents' => $project['budget'],
        'start_date'        => $project['start'],
        'status'            => $project['status'],
        'created_by'        => $userIds['gestor'],
        'created_at'        => $project['created_at'],
        'updated_at'        => $project['created_at'],
    ]);
    $projectIds[] = (int) $pdo->lastInsertId();
}

/* --------------------------------------------------------------------------
 * Alterações e histórico
 *
 * O cenário principal (obra 0 = 8 000 €) tem uma única alteração de 650 €
 * pendente. Antes da aprovação o orçamento é 8 000 €; depois é 8 650 €.
 * -------------------------------------------------------------------------- */

$insertChange = $pdo->prepare(
    'INSERT INTO changes
        (project_id, title, description, amount_cents, status, created_by,
         submitted_at, decided_at, decided_by, decision_note, created_at, updated_at)
     VALUES
        (:project_id, :title, :description, :amount_cents, :status, :created_by,
         :submitted_at, :decided_at, :decided_by, :decision_note, :created_at, :updated_at)'
);

$insertEvent = $pdo->prepare(
    'INSERT INTO change_events (change_id, user_id, action, from_status, to_status, note, created_at)
     VALUES (:change_id, :user_id, :action, :from_status, :to_status, :note, :created_at)'
);

/**
 * @param array<string, mixed> $change
 */
function seedChange(PDO $pdo, array $change, array $userIds, array $projectIds): void
{
    global $insertChange, $insertEvent;

    $authorId = $userIds[$change['author']];
    $deciderId = isset($change['decider']) ? $userIds[$change['decider']] : null;

    $insertChange->execute([
        'project_id'    => $projectIds[$change['project']],
        'title'         => $change['title'],
        'description'   => $change['description'],
        'amount_cents'  => $change['amount'],
        'status'        => $change['status'],
        'created_by'    => $authorId,
        'submitted_at'  => $change['submitted_at'] ?? null,
        'decided_at'    => $change['decided_at'] ?? null,
        'decided_by'    => $deciderId,
        'decision_note' => $change['decision_note'] ?? null,
        'created_at'    => $change['created_at'],
        'updated_at'    => $change['decided_at'] ?? $change['submitted_at'] ?? $change['created_at'],
    ]);

    $changeId = (int) $pdo->lastInsertId();

    $insertEvent->execute([
        'change_id'   => $changeId,
        'user_id'     => $authorId,
        'action'      => 'criada',
        'from_status' => null,
        'to_status'   => 'rascunho',
        'note'        => null,
        'created_at'  => $change['created_at'],
    ]);

    if (in_array($change['status'], ['pendente', 'aprovada', 'rejeitada'], true)) {
        $insertEvent->execute([
            'change_id'   => $changeId,
            'user_id'     => $authorId,
            'action'      => 'submetida',
            'from_status' => 'rascunho',
            'to_status'   => 'pendente',
            'note'        => null,
            'created_at'  => $change['submitted_at'],
        ]);
    }

    if (in_array($change['status'], ['aprovada', 'rejeitada'], true)) {
        $insertEvent->execute([
            'change_id'   => $changeId,
            'user_id'     => $deciderId,
            'action'      => $change['status'] === 'aprovada' ? 'aprovada' : 'rejeitada',
            'from_status' => 'pendente',
            'to_status'   => $change['status'],
            'note'        => $change['decision_note'] ?? null,
            'created_at'  => $change['decided_at'],
        ]);
    }
}

$changes = [
    // Cenário obrigatório: 8 000 € + 650 € pendente.
    [
        'project' => 0,
        'author' => 'colaborador',
        'title' => 'Substituição do pavimento da sala',
        'description' => 'O cliente pediu pavimento vinílico em vez do laminado previsto. A diferença inclui material e mão de obra adicional.',
        'amount' => 65000,
        'status' => 'pendente',
        'submitted_at' => '2026-10-27 15:40:00',
        'created_at' => '2026-10-26 11:10:00',
    ],
    // Cozinha e sala: duas aprovações (1 450 € + 380 €) e uma rejeição.
    [
        'project' => 1,
        'author' => 'colaborador',
        'title' => 'Bancada em quartzo',
        'description' => 'Upgrade de bancada laminada para quartzo, conforme escolha dos clientes.',
        'amount' => 145000,
        'status' => 'aprovada',
        'submitted_at' => '2026-08-12 10:00:00',
        'created_at' => '2026-08-10 09:20:00',
        'decided_at' => '2026-08-12 17:30:00',
        'decider' => 'gestor',
        'decision_note' => 'Aprovado pelo cliente por email em 12/08/2026.',
    ],
    [
        'project' => 1,
        'author' => 'gestor',
        'title' => 'Iluminação LED embutida',
        'description' => 'Substituição dos focos existentes por iluminação LED embutida na sala e cozinha.',
        'amount' => 38000,
        'status' => 'aprovada',
        'submitted_at' => '2026-08-26 14:15:00',
        'created_at' => '2026-08-26 10:00:00',
        'decided_at' => '2026-08-27 09:10:00',
        'decider' => 'gestor',
        'decision_note' => 'Trabalho aprovado; incluído no plano de iluminação.',
    ],
    [
        'project' => 1,
        'author' => 'colaborador',
        'title' => 'Substituição de eletrodomésticos',
        'description' => 'Pedido de substituição do forno e placa por modelos de gama superior.',
        'amount' => 90000,
        'status' => 'rejeitada',
        'submitted_at' => '2026-09-02 11:30:00',
        'created_at' => '2026-09-01 16:20:00',
        'decided_at' => '2026-09-03 10:05:00',
        'decider' => 'gestor',
        'decision_note' => 'Fora do âmbito contratado: os eletrodomésticos são fornecidos pelo cliente.',
    ],
    // Cobertura: aprovada e concluída.
    [
        'project' => 2,
        'author' => 'colaborador',
        'title' => 'Reparação de rufos',
        'description' => 'Substituição de dois rufos degradados junto à chaminé.',
        'amount' => 60000,
        'status' => 'aprovada',
        'submitted_at' => '2026-07-28 09:40:00',
        'created_at' => '2026-07-27 15:05:00',
        'decided_at' => '2026-07-28 12:20:00',
        'decider' => 'gestor',
        'decision_note' => 'Aprovado pela administração do condomínio.',
    ],
    // WC do café: pendente.
    [
        'project' => 3,
        'author' => 'colaborador',
        'title' => 'Espelho e torneira',
        'description' => 'Troca do espelho e da torneira por modelos mais recentes, a pedido da gerência.',
        'amount' => 27500,
        'status' => 'pendente',
        'submitted_at' => '2026-10-28 18:05:00',
        'created_at' => '2026-10-28 16:30:00',
    ],
    // Pintura: rascunho (não conta para orçamento).
    [
        'project' => 4,
        'author' => 'colaborador',
        'title' => 'Andaime adicional',
        'description' => 'Aluguer de andaime extra para a fachada lateral. Ainda a confirmar com o fornecedor.',
        'amount' => 32000,
        'status' => 'rascunho',
        'created_at' => '2026-10-28 10:15:00',
    ],
];

foreach ($changes as $change) {
    seedChange($pdo, $change, $userIds, $projectIds);
}

/* --------------------------------------------------------------------------
 * Resumo
 * -------------------------------------------------------------------------- */

$count = static fn (string $table): int => (int) $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();

echo "Dados de demonstração criados em: {$path}\n";
echo "Utilizadores: {$count('users')}, clientes: {$count('clients')}, obras: {$count('projects')}, "
    . "alterações: {$count('changes')}, eventos: {$count('change_events')}\n\n";

echo "Contas fictícias (apenas local):\n";
echo "  Gestor      gestor@obraclara.pt / Gestor2026!\n";
echo "  Colaborador colaborador@obraclara.pt / Colab2026!\n\n";

$scenario = $pdo->prepare(
    "SELECT p.title, p.base_budget_cents,
            COALESCE(SUM(CASE WHEN ch.status = 'aprovada' THEN ch.amount_cents END), 0) AS approved
       FROM projects p
       LEFT JOIN changes ch ON ch.project_id = p.id
      WHERE p.title LIKE 'Remodelação T3%'
      GROUP BY p.id"
);
$scenario->execute();
$row = $scenario->fetch();
if ($row) {
    $base = (int) $row['base_budget_cents'];
    $approved = (int) $row['approved'];
    echo "Cenário de demonstração: {$row['title']} — base "
        . number_format($base / 100, 2, ',', ' ') . ' €, aprovado '
        . number_format($approved / 100, 2, ',', ' ') . ' €, atualizado '
        . number_format(($base + $approved) / 100, 2, ',', ' ') . " €\n";
}
