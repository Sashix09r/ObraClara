<?php

declare(strict_types=1);

/**
 * Testes automatizados da ObraClara V2 — sem dependências externas.
 *
 * Uso: php tests/run.php
 *
 * Cada teste recebe uma base de dados SQLite NOVA e descartável em tmp/,
 * pelo que os dados de demonstração nunca são tocados.
 */

use ObraClara\Repositories\AdminEventRepository;
use ObraClara\Repositories\ClientRepository;
use ObraClara\Repositories\ChangeRepository;
use ObraClara\Repositories\EventRepository;
use ObraClara\Repositories\InviteRepository;
use ObraClara\Repositories\ProjectRepository;
use ObraClara\Repositories\UserRepository;
use ObraClara\Services\AccountService;
use ObraClara\Services\ChangeService;
use ObraClara\Services\ClientService;
use ObraClara\Services\DashboardService;
use ObraClara\Services\ProjectPdfService;
use ObraClara\Services\ProjectService;
use ObraClara\Services\TeamService;
use ObraClara\Support\Lang;
use ObraClara\Support\Migrator;
use ObraClara\Support\AuthorizationException;
use ObraClara\Support\BusinessRuleException;
use ObraClara\Support\Csrf;
use ObraClara\Support\Database;
use ObraClara\Support\Money;
use ObraClara\Support\NotFoundException;
use ObraClara\Support\ValidationException;

$testDatabase = dirname(__DIR__) . '/tmp/test-obraclara.sqlite';
putenv('OBRACLARA_DB=' . $testDatabase);

require dirname(__DIR__) . '/app/bootstrap.php';

/* --------------------------------------------------------------------------
 * Mini framework de testes
 * -------------------------------------------------------------------------- */

final class AssertionFailed extends RuntimeException
{
}

function assert_same(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new AssertionFailed(
            ($message !== '' ? $message . ': ' : '')
            . 'esperado ' . var_export($expected, true) . ', obtido ' . var_export($actual, true)
        );
    }
}

function assert_true(bool $condition, string $message = 'condição falsa'): void
{
    if (!$condition) {
        throw new AssertionFailed($message);
    }
}

function assert_contains(string $needle, string $haystack, string $message = ''): void
{
    if (!str_contains($haystack, $needle)) {
        throw new AssertionFailed(($message !== '' ? $message . ': ' : '') . 'não contém «' . $needle . '»');
    }
}

/** @param class-string<Throwable> $expected */
function assert_throws(string $expected, callable $callback, string $message = ''): Throwable
{
    try {
        $callback();
    } catch (Throwable $exception) {
        if (!$exception instanceof $expected) {
            throw new AssertionFailed(
                ($message !== '' ? $message . ': ' : '')
                . 'esperava ' . $expected . ', obteve ' . $exception::class
                . ' (' . $exception->getMessage() . ')'
            );
        }

        return $exception;
    }

    throw new AssertionFailed(($message !== '' ? $message . ': ' : '') . 'esperava exceção ' . $expected);
}

/**
 * Base de dados descartável com fixtures mínimos.
 *
 * @return array{gestor: array, colaborador: array, outro: array, pdo: PDO, projects: array, clients: array}
 */
function fresh_database(): array
{
    $path = Database::path();
    Database::setPdo(null);

    foreach ([$path, $path . '-wal', $path . '-shm'] as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }

    Database::init($path);
    $pdo = Database::pdo();
    $schema = file_get_contents(OBRACLARA_ROOT . '/database/schema.sql');
    $pdo->exec((string) $schema);

    $insertUser = $pdo->prepare(
        'INSERT INTO users (name, email, password_hash, role, active, created_at)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $insertUser->execute(['Ana Ferreira', 'gestor@obraclara.pt', password_hash('Gestor2026!', PASSWORD_DEFAULT), 'gestor', 1, now()]);
    $gestorId = (int) $pdo->lastInsertId();

    $insertUser->execute(['Rui Marques', 'colaborador@obraclara.pt', password_hash('Colab2026!', PASSWORD_DEFAULT), 'colaborador', 1, now()]);
    $colabId = (int) $pdo->lastInsertId();

    $insertUser->execute(['Inês Costa', 'ines@obraclara.pt', password_hash('Ines2026!', PASSWORD_DEFAULT), 'colaborador', 1, now()]);
    $outroId = (int) $pdo->lastInsertId();

    $insertUser->execute(['Conta Inativa', 'inativa@obraclara.pt', password_hash('Inativa2026!', PASSWORD_DEFAULT), 'colaborador', 0, now()]);

    $insertClient = $pdo->prepare(
        'INSERT INTO clients (name, contact, email, notes, active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $insertClient->execute(['Maria Sousa', '912 345 678', 'maria.sousa@exemplo.pt', null, 1, now(), now()]);
    $client1 = (int) $pdo->lastInsertId();
    $insertClient->execute(['João Almeida', '934 210 887', 'joao@exemplo.pt', null, 1, now(), now()]);
    $client2 = (int) $pdo->lastInsertId();
    $insertClient->execute(['Cliente Arquivado', null, null, null, 0, now(), now()]);
    $client3 = (int) $pdo->lastInsertId();

    $insertProject = $pdo->prepare(
        'INSERT INTO projects (client_id, title, description, base_budget_cents, start_date, status, created_by, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $insertProject->execute([$client1, 'Remodelação T3 — Rua das Flores', null, 800000, '2026-09-15', 'em_curso', $gestorId, now(), now()]);
    $project1 = (int) $pdo->lastInsertId();
    $insertProject->execute([$client2, 'Renovação do WC', null, 320000, '2026-10-05', 'em_curso', $gestorId, now(), now()]);
    $project2 = (int) $pdo->lastInsertId();
    $insertProject->execute([$client1, 'Ampliação de garagem', null, 500000, '2026-11-02', 'planeada', $gestorId, now(), now()]);
    $project3 = (int) $pdo->lastInsertId();
    $insertProject->execute([$client1, 'Obra arquivada', null, 100000, null, 'arquivada', $gestorId, now(), now()]);
    $project4 = (int) $pdo->lastInsertId();

    return [
        'pdo'         => $pdo,
        'gestor'      => UserRepository::find($gestorId),
        'colaborador' => UserRepository::find($colabId),
        'outro'       => UserRepository::find($outroId),
        'projects'    => [$project1, $project2, $project3, $project4],
        'clients'     => [$client1, $client2, $client3],
    ];
}

/** @var list<array{id: string, description: string, state: string, detail: string}> */
$results = [];

function test(string $id, string $description, callable $callback): void
{
    global $results;

    try {
        $callback();
        $results[] = ['id' => $id, 'description' => $description, 'state' => 'PASSOU', 'detail' => ''];
        echo "  ✓ {$id} {$description}\n";
    } catch (AssertionFailed $exception) {
        $results[] = ['id' => $id, 'description' => $description, 'state' => 'FALHOU', 'detail' => $exception->getMessage()];
        echo "  ✗ {$id} {$description}\n      -> {$exception->getMessage()}\n";
    } catch (Throwable $exception) {
        $results[] = [
            'id' => $id,
            'description' => $description,
            'state' => 'FALHOU',
            'detail' => $exception::class . ': ' . $exception->getMessage(),
        ];
        echo "  ✗ {$id} {$description}\n      -> " . $exception::class . ': ' . $exception->getMessage() . "\n";
    }
}

echo "ObraClara V2 — testes automatizados\n";
echo 'Base descartável: ' . $testDatabase . "\n\n";

/* --------------------------------------------------------------------------
 * T01–T04 — Dinheiro e escape
 * -------------------------------------------------------------------------- */

test('T01', 'Conversão de valores monetários para cêntimos', function (): void {
    assert_same(65000, Money::parse('650'), '650');
    assert_same(65000, Money::parse('650,00'), '650,00');
    assert_same(65000, Money::parse('650.00'), '650.00');
    assert_same(800000, Money::parse('8 000,00'), '8 000,00');
    assert_same(800000, Money::parse('8.000,00'), '8.000,00');
    assert_same(123456, Money::parse('1.234,56'), '1.234,56');
    assert_same(123456, Money::parse('1,234.56'), '1,234.56');
    assert_same(123400, Money::parse('1.234'), '1.234 como milhares');
    assert_same(1230, Money::parse('12.30'), '12.30 decimal');
    assert_same(0, Money::parse('0', true), 'zero permitido no orçamento base');
    assert_same(65050, Money::parse('650,5'), 'uma casa decimal');
});

test('T02', 'Rejeição de valores inválidos', function (): void {
    foreach (['', 'abc', '-5', '10,999', '1,2,3', '12.3.4', '€', '9999999999,99', '650,00 € extra'] as $value) {
        assert_throws(InvalidArgumentException::class, fn () => Money::parse($value), 'valor «' . $value . '»');
    }
    assert_throws(InvalidArgumentException::class, fn () => Money::parse('0'), 'zero não é custo válido');
});

test('T03', 'Formatação em euros pt-PT', function (): void {
    assert_same('8 000,00 €', Money::format(800000));
    assert_same('8 650,00 €', Money::format(865000));
    assert_same('650,05 €', Money::format(65005));
    assert_same('0,00 €', Money::format(0));
});

test('T04', 'Escape de HTML contra XSS', function (): void {
    $payload = '<script>alert("x")</script>';
    assert_same('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', e($payload));
    assert_contains('&lt;img', e('<img src=x onerror=alert(1)>'));
    assert_same('João &amp; Maria', e('João & Maria'));
});

/* --------------------------------------------------------------------------
 * T05–T07 — Autenticação e permissões
 * -------------------------------------------------------------------------- */

test('T05', 'Autenticação com hash de palavra-passe', function (): void {
    $f = fresh_database();

    $gestor = \ObraClara\Support\Auth::verifyCredentials('gestor@obraclara.pt', 'Gestor2026!');
    assert_true($gestor !== null, 'gestor autenticado');
    assert_same('gestor', $gestor['role']);
    assert_true(password_verify('Gestor2026!', (string) $gestor['password_hash']), 'hash válido');

    assert_same(null, \ObraClara\Support\Auth::verifyCredentials('gestor@obraclara.pt', 'errada'), 'password errada');
    assert_same(null, \ObraClara\Support\Auth::verifyCredentials('naoexiste@obraclara.pt', 'Gestor2026!'), 'email desconhecido');
    assert_same(null, \ObraClara\Support\Auth::verifyCredentials('inativa@obraclara.pt', 'Inativa2026!'), 'conta inativa');

    // O hash nunca é guardado em texto simples.
    assert_true((string) $f['gestor']['password_hash'] !== 'Gestor2026!', 'hash não é texto simples');
});

test('T06', 'CSRF aceita apenas o token da sessão', function (): void {
    $_SESSION = ['csrf_token' => str_repeat('a', 64)];
    assert_true(Csrf::check(str_repeat('a', 64)), 'token correto');
    assert_true(!Csrf::check(str_repeat('b', 64)), 'token errado');
    assert_true(!Csrf::check(null), 'token ausente');
    assert_true(!Csrf::check(''), 'token vazio');
    Csrf::rotate();
    assert_true(!Csrf::check(str_repeat('a', 64)), 'token antigo invalidado após rotação');
});

test('T07', 'Colaborador autenticado não pode decidir alteração', function (): void {
    $f = fresh_database();
    $changeId = ChangeService::create($f['projects'][0], [
        'title' => 'Pavimento vinílico',
        'description' => 'Diferença de material',
        'amount' => '650,00',
    ], $f['colaborador'], true);

    assert_throws(
        AuthorizationException::class,
        fn () => ChangeService::decide($changeId, $f['colaborador'], 'aprovar', null),
        'colaborador tenta aprovar'
    );

    $after = ChangeRepository::find($changeId);
    assert_same('pendente', $after['status'], 'estado mantém-se pendente');
    assert_same(800000, ProjectRepository::find($f['projects'][0])['updated_budget_cents'], 'orçamento inalterado');
});

/* --------------------------------------------------------------------------
 * T08–T10 — Clientes
 * -------------------------------------------------------------------------- */

test('T08', 'Validação e criação de cliente', function (): void {
    fresh_database();

    assert_throws(ValidationException::class, fn () => ClientService::save(null, ['name' => '']), 'nome vazio');
    assert_throws(ValidationException::class, fn () => ClientService::save(null, ['name' => 'Ok', 'email' => 'invalido']), 'email inválido');

    $id = ClientService::save(null, [
        'name' => 'Cliente Novo',
        'contact' => '910 000 000',
        'email' => 'novo@exemplo.pt',
        'notes' => 'Nota de teste',
    ]);

    $client = ClientRepository::find($id);
    assert_same('Cliente Novo', $client['name']);
    assert_same(1, (int) $client['active']);
});

test('T09', 'Pesquisa de clientes com e sem resultados', function (): void {
    fresh_database();

    $found = ClientRepository::search('Sousa');
    assert_same(1, count($found), 'um resultado para Sousa');
    assert_same('Maria Sousa', $found[0]['name']);

    assert_same([], ClientRepository::search('Cliente Inexistente'), 'pesquisa sem resultados');
    assert_same(2, count(ClientRepository::search('', false)), 'ativos excluem arquivados');
    assert_same(3, count(ClientRepository::search('', true)), 'inclui arquivados');
});

test('T10', 'Arquivo de cliente respeita obras ativas', function (): void {
    $f = fresh_database();

    assert_throws(
        BusinessRuleException::class,
        fn () => ClientService::archive($f['clients'][0]),
        'cliente com obras ativas'
    );

    $novo = ClientService::save(null, ['name' => 'Sem Obras']);
    ClientService::archive($novo);
    assert_same(0, (int) ClientRepository::find($novo)['active'], 'cliente sem obras arquivado');
    ClientService::reactivate($novo);
    assert_same(1, (int) ClientRepository::find($novo)['active'], 'cliente reativado');
});

/* --------------------------------------------------------------------------
 * T11–T13 — Obras e orçamento base
 * -------------------------------------------------------------------------- */

test('T11', 'Obra exige cliente ativo existente', function (): void {
    $f = fresh_database();

    assert_throws(
        ValidationException::class,
        fn () => ProjectService::save(null, ['title' => 'Sem cliente', 'base_budget' => '1000'], 1),
        'cliente ausente'
    );
    assert_throws(
        ValidationException::class,
        fn () => ProjectService::save(null, ['client_id' => 9999, 'title' => 'Cliente inexistente', 'base_budget' => '1000'], 1),
        'cliente inexistente'
    );
    assert_throws(
        ValidationException::class,
        fn () => ProjectService::save(null, ['client_id' => $f['clients'][2], 'title' => 'Cliente arquivado', 'base_budget' => '1000'], 1),
        'cliente arquivado'
    );

    $id = ProjectService::save(null, [
        'client_id' => $f['clients'][0],
        'title' => 'Nova obra válida',
        'base_budget' => '8 000,00',
        'start_date' => '2026-11-10',
        'status' => 'planeada',
    ], (int) $f['gestor']['id']);

    $project = ProjectRepository::find($id);
    assert_same(800000, (int) $project['base_budget_cents']);
    assert_same(800000, (int) $project['updated_budget_cents']);
});

test('T12', 'Orçamento atualizado ignora rascunhos e pendentes', function (): void {
    $f = fresh_database();
    $projectId = $f['projects'][0];

    $draft = ChangeService::create($projectId, [
        'title' => 'Rascunho de teste',
        'amount' => '200,00',
    ], $f['colaborador']);
    assert_same(800000, ProjectRepository::find($projectId)['updated_budget_cents'], 'rascunho não soma');

    ChangeService::submit($draft, $f['colaborador']);
    assert_same('pendente', ChangeRepository::find($draft)['status'], 'estado pendente');
    assert_same(800000, ProjectRepository::find($projectId)['updated_budget_cents'], 'pendente não soma');
});

test('T13', 'Valores de alteração inválidos são recusados', function (): void {
    $f = fresh_database();

    assert_throws(ValidationException::class, fn () => ChangeService::create($f['projects'][0], [
        'title' => 'Negativo', 'amount' => '-50',
    ], $f['colaborador']), 'negativo');

    assert_throws(ValidationException::class, fn () => ChangeService::create($f['projects'][0], [
        'title' => 'Zero', 'amount' => '0,00',
    ], $f['colaborador']), 'zero');

    assert_throws(ValidationException::class, fn () => ChangeService::create($f['projects'][0], [
        'title' => '', 'amount' => '100',
    ], $f['colaborador']), 'título vazio');

    assert_same(0, ChangeRepository::countByStatus('rascunho'), 'nada foi criado');
});

/* --------------------------------------------------------------------------
 * T14–T17 — Cenário de aceitação e histórico
 * -------------------------------------------------------------------------- */

test('T14', 'Cenário 8 000 € + 650 € aprovada = 8 650 € com histórico', function (): void {
    $f = fresh_database();
    $projectId = $f['projects'][0];

    $changeId = ChangeService::create($projectId, [
        'title' => 'Substituição do pavimento da sala',
        'description' => 'Pavimento vinílico em vez de laminado',
        'amount' => '650,00',
    ], $f['colaborador'], true);

    assert_same(800000, ProjectRepository::find($projectId)['updated_budget_cents'], 'antes de aprovar: 8 000 €');

    $status = ChangeService::decide($changeId, $f['gestor'], 'aprovar', 'Aprovado pelo cliente.');
    assert_same('aprovada', $status);

    $project = ProjectRepository::find($projectId);
    assert_same(65000, $project['approved_cents'], 'aprovado 650 €');
    assert_same(865000, $project['updated_budget_cents'], 'atualizado 8 650 €');
    assert_same('8 650,00 €', Money::format((int) $project['updated_budget_cents']));

    $change = ChangeRepository::find($changeId);
    assert_same((int) $f['gestor']['id'], (int) $change['decided_by'], 'decisor registado');
    assert_true($change['decided_at'] !== null, 'data da decisão registada');
    assert_same('Aprovado pelo cliente.', $change['decision_note']);

    $events = EventRepository::forChange($changeId);
    assert_same(3, count($events), 'três eventos');
    assert_same('criada', $events[0]['action']);
    assert_same('submetida', $events[1]['action']);
    assert_same('aprovada', $events[2]['action']);
    assert_same((int) $f['gestor']['id'], (int) $events[2]['user_id'], 'autor da aprovação');
    assert_same('pendente', $events[2]['from_status']);
    assert_same('aprovada', $events[2]['to_status']);
    assert_true($events[2]['created_at'] !== null, 'data do evento');
});

test('T15', 'Rejeição mantém o orçamento e exige observação', function (): void {
    $f = fresh_database();
    $projectId = $f['projects'][0];

    $changeId = ChangeService::create($projectId, [
        'title' => 'Trabalho recusado', 'amount' => '650,00',
    ], $f['colaborador'], true);

    assert_throws(ValidationException::class, fn () => ChangeService::decide($changeId, $f['gestor'], 'rejeitar', ''), 'sem motivo');

    ChangeService::decide($changeId, $f['gestor'], 'rejeitar', 'Cliente recusou o custo.');

    $project = ProjectRepository::find($projectId);
    assert_same(0, $project['approved_cents'], 'nada aprovado');
    assert_same(800000, $project['updated_budget_cents'], 'orçamento continua 8 000 €');
    assert_same('rejeitada', ChangeRepository::find($changeId)['status']);

    $events = EventRepository::forChange($changeId);
    assert_same('rejeitada', $events[2]['action'], 'evento de rejeição');
});

test('T16', 'Várias aprovações somam corretamente', function (): void {
    $f = fresh_database();
    $projectId = $f['projects'][0];

    $a = ChangeService::create($projectId, ['title' => 'Trabalho A', 'amount' => '650,00'], $f['colaborador'], true);
    $b = ChangeService::create($projectId, ['title' => 'Trabalho B', 'amount' => '350,50'], $f['colaborador'], true);
    $c = ChangeService::create($projectId, ['title' => 'Trabalho C', 'amount' => '999,99'], $f['colaborador'], true);

    ChangeService::decide($a, $f['gestor'], 'aprovar', null);
    ChangeService::decide($b, $f['gestor'], 'aprovar', null);
    ChangeService::decide($c, $f['gestor'], 'rejeitar', 'Sem verba.');

    $project = ProjectRepository::find($projectId);
    assert_same(100050, $project['approved_cents'], '650,00 + 350,50 = 1 000,50');
    assert_same(900050, $project['updated_budget_cents'], '8 000 + 1 000,50 = 9 000,50');
});

test('T17', 'Transições inválidas e imutabilidade após decisão', function (): void {
    $f = fresh_database();
    $projectId = $f['projects'][0];

    $changeId = ChangeService::create($projectId, [
        'title' => 'Pavimento', 'amount' => '650,00',
    ], $f['colaborador'], true);

    ChangeService::decide($changeId, $f['gestor'], 'aprovar', null);

    assert_throws(BusinessRuleException::class, fn () => ChangeService::decide($changeId, $f['gestor'], 'rejeitar', 'Dupla'), 'decidir duas vezes');
    assert_throws(BusinessRuleException::class, fn () => ChangeService::submit($changeId, $f['gestor']), 'submeter decidida');
    assert_throws(BusinessRuleException::class, fn () => ChangeService::update($changeId, [
        'title' => 'Alterado', 'amount' => '1,00',
    ], $f['gestor']), 'editar aprovada');

    assert_same(865000, ProjectRepository::find($projectId)['updated_budget_cents'], 'valor não mudou');

    // Defesa na base de dados: triggers impedem alteração direta de valores e histórico.
    $pdo = Database::pdo();
    assert_throws(PDOException::class, fn () => $pdo->exec("UPDATE changes SET amount_cents = 1 WHERE id = {$changeId}"), 'trigger de imutabilidade');
    assert_throws(PDOException::class, fn () => $pdo->exec("UPDATE change_events SET note = 'reescrito' WHERE change_id = {$changeId}"), 'histórico não pode ser editado');
    assert_throws(PDOException::class, fn () => $pdo->exec("DELETE FROM change_events WHERE change_id = {$changeId}"), 'histórico não pode ser apagado');
});

/* --------------------------------------------------------------------------
 * T18–T21 — Permissões de edição, arquivo e integridade
 * -------------------------------------------------------------------------- */

test('T18', 'Rascunho só é editável pelo autor ou gestor', function (): void {
    $f = fresh_database();
    $changeId = ChangeService::create($f['projects'][0], [
        'title' => 'Rascunho do Rui', 'amount' => '100,00',
    ], $f['colaborador']);

    assert_throws(
        AuthorizationException::class,
        fn () => ChangeService::update($changeId, ['title' => 'Rascunho do Rui', 'amount' => '120,00'], $f['outro']),
        'outro colaborador'
    );

    ChangeService::update($changeId, ['title' => 'Rascunho do Rui', 'amount' => '120,00'], $f['gestor']);
    assert_same(12000, (int) ChangeRepository::find($changeId)['amount_cents'], 'gestor editou');
    assert_same(2, count(EventRepository::forChange($changeId)), 'evento de edição registado');

    // Após submissão, deixa de ser editável.
    ChangeService::submit($changeId, $f['colaborador']);
    assert_throws(BusinessRuleException::class, fn () => ChangeService::update($changeId, [
        'title' => 'Tarde demais', 'amount' => '200,00',
    ], $f['gestor']), 'pendente imutável');
});

test('T19', 'Arquivo de obra e efeitos nas alterações', function (): void {
    $f = fresh_database();
    $projectId = $f['projects'][0];

    $pending = ChangeService::create($projectId, ['title' => 'Pendente', 'amount' => '100,00'], $f['colaborador'], true);

    assert_throws(BusinessRuleException::class, fn () => ProjectService::archive($projectId), 'com alteração pendente');
    assert_throws(BusinessRuleException::class, fn () => ProjectService::reactivate($projectId), 'obra não arquivada');

    ChangeService::decide($pending, $f['gestor'], 'rejeitar', 'Sem verba.');
    ProjectService::archive($projectId);
    assert_same('arquivada', ProjectRepository::find($projectId)['status']);

    assert_throws(BusinessRuleException::class, fn () => ChangeService::create($projectId, [
        'title' => 'Nova', 'amount' => '50,00',
    ], $f['colaborador']), 'obra arquivada não aceita alterações');

    ProjectService::reactivate($projectId);
    assert_same('planeada', ProjectRepository::find($projectId)['status']);
});

test('T20', 'IDs inexistentes e integridade referencial', function (): void {
    $f = fresh_database();

    assert_throws(NotFoundException::class, fn () => ProjectRepository::find(9999), 'obra inexistente');
    assert_throws(NotFoundException::class, fn () => ChangeRepository::find(9999), 'alteração inexistente');
    assert_throws(NotFoundException::class, fn () => ChangeService::create(9999, [
        'title' => 'Órfã', 'amount' => '10,00',
    ], $f['colaborador']), 'alteração sem obra');

    assert_throws(PDOException::class, fn () => $f['pdo']->exec(
        'INSERT INTO projects (client_id, title, description, base_budget_cents, status, created_at, updated_at)
         VALUES (9999, "Órfã", NULL, 100, "planeada", "2026-01-01", "2026-01-01")'
    ), 'FK de cliente');

    assert_throws(PDOException::class, fn () => $f['pdo']->exec(
        'INSERT INTO changes (project_id, title, description, amount_cents, status, created_by, created_at, updated_at)
         VALUES (1, "Negativa", NULL, -100, "rascunho", 1, "2026-01-01", "2026-01-01")'
    ), 'CHECK de valor positivo');
});

test('T21', 'Pesquisa de obras por texto e estado', function (): void {
    $f = fresh_database();

    assert_same(1, count(ProjectRepository::search('Flores')), 'pesquisa por título');
    assert_same(2, count(ProjectRepository::search('Maria')), 'pesquisa por cliente');
    assert_same([], ProjectRepository::search('zzz-sem-resultados'), 'sem resultados');
    assert_same(1, count(ProjectRepository::search('', 'arquivada')), 'filtro arquivadas');
    assert_same(3, count(ProjectRepository::search('', '')), 'exclui arquivadas por omissão');
    assert_same(1, count(ProjectRepository::search('', 'planeada')), 'filtro planeadas');
});

/* --------------------------------------------------------------------------
 * T22–T24 — Persistência e dashboard
 * -------------------------------------------------------------------------- */

test('T22', 'Persistência após reabrir a base de dados', function (): void {
    $f = fresh_database();
    $projectId = $f['projects'][0];
    $path = Database::path();

    $changeId = ChangeService::create($projectId, ['title' => 'Pavimento', 'amount' => '650,00'], $f['colaborador'], true);
    ChangeService::decide($changeId, $f['gestor'], 'aprovar', 'Confirmado.');

    // Simula fechar e reabrir a aplicação: nova ligação PDO ao mesmo ficheiro.
    Database::setPdo(null);
    Database::init($path);

    $project = ProjectRepository::find($projectId);
    assert_same(865000, $project['updated_budget_cents'], 'orçamento persistido');
    assert_same('aprovada', ChangeRepository::find($changeId)['status'], 'estado persistido');
    assert_same(3, count(EventRepository::forChange($changeId)), 'histórico persistido');
});

test('T23', 'Indicadores do dashboard vêm da base de dados', function (): void {
    $f = fresh_database();

    ChangeService::create($f['projects'][0], ['title' => 'Pendente A', 'amount' => '650,00'], $f['colaborador'], true);
    ChangeService::create($f['projects'][1], ['title' => 'Rascunho B', 'amount' => '99,00'], $f['colaborador']);

    $stats = DashboardService::stats();
    assert_same(3, $stats['active_projects'], 'três obras ativas');
    assert_same(1, $stats['pending_changes'], 'uma pendente');
    assert_same(1, $stats['draft_changes'], 'um rascunho');
    assert_same(1620000, $stats['forecast_total'], 'orçamento previsto 16 200 €');
    assert_same(0, $stats['approved_extra'], 'sem custos aprovados');
});

test('T24', 'Histórico global inclui autor, obra e transição', function (): void {
    $f = fresh_database();

    $changeId = ChangeService::create($f['projects'][1], ['title' => 'Espelho', 'amount' => '275,00'], $f['colaborador'], true);
    ChangeService::decide($changeId, $f['gestor'], 'aprovar', 'Aprovado pela gerência.');

    $events = EventRepository::recent(50);
    $approved = null;
    foreach ($events as $event) {
        if ($event['action'] === 'aprovada') {
            $approved = $event;
        }
    }

    assert_true($approved !== null, 'evento aprovado presente');
    $decider = UserRepository::find((int) $f['gestor']['id']);
    $deciderName = $decider['name'] ?? null;
    assert_same($deciderName, $approved['user_name'], 'nome do decisor');
    assert_same('Renovação do WC', $approved['project_title']);
    assert_same('Aprovado pela gerência.', $approved['note']);
});

test('T25', 'Acentuação portuguesa preservada na base de dados e no HTML', function (): void {
    $f = fresh_database();

    $clientId = ClientService::save(null, [
        'name' => 'Conceição Águas',
        'notes' => 'Acentuação: ção, ã, é, õ.',
    ]);
    $client = ClientRepository::find($clientId);
    assert_same('Conceição Águas', $client['name']);
    assert_same('Acentuação: ção, ã, é, õ.', $client['notes']);
    assert_contains('Conceição', e($client['name']));

    $projectId = ProjectService::save(null, [
        'client_id' => $f['clients'][1],
        'title' => 'Remodelação de sótão e cave',
        'base_budget' => '12 500,00',
        'status' => 'planeada',
    ], (int) $f['gestor']['id']);
    assert_same('Remodelação de sótão e cave', ProjectRepository::find($projectId)['title']);
});

/* --------------------------------------------------------------------------
 * T26–T37 — Contas, convites, perfil, PDF, traduções, tutorial e migrações
 * -------------------------------------------------------------------------- */

/** Cria um convite e devolve o token em claro, apenas para o teste. */
function invite_token(array $actor, string $email, string $role = 'colaborador'): array
{
    return TeamService::createInvite($actor, [
        'email' => $email,
        'role' => $role,
        'expiry' => 7,
    ]);
}

test('T26', 'Convite guarda apenas hash e só pode ser usado uma vez', function (): void {
    $f = fresh_database();
    $invite = invite_token($f['gestor'], 'Nova.Pessoa@Exemplo.pt');

    assert_same(64, strlen($invite['token']), 'token com 256 bits em hexadecimal');
    $row = InviteRepository::find($invite['id']);
    assert_true($row !== null, 'convite guardado');
    assert_same(hash('sha256', $invite['token']), $row['token_hash'], 'hash SHA-256');
    assert_true(!str_contains(json_encode($row), $invite['token']), 'token em claro não está na BD');
    assert_same('nova.pessoa@exemplo.pt', $row['email'], 'email normalizado');

    $newUser = TeamService::register([
        'token' => $invite['token'],
        'email' => 'nova.pessoa@exemplo.pt',
        'name' => 'Nova Pessoa',
        'password' => 'Nova2026!',
        'password_confirm' => 'Nova2026!',
    ]);

    assert_same('colaborador', $newUser['role'], 'perfil vem do convite');
    assert_true(password_verify('Nova2026!', (string) $newUser['password_hash']), 'password com hash');
    $used = InviteRepository::find($invite['id']);
    assert_same((int) $newUser['id'], (int) $used['used_by'], 'convite associado ao novo utilizador');
    assert_true($used['used_at'] !== null, 'convite marcado como utilizado');

    assert_throws(ValidationException::class, fn () => TeamService::register([
        'token' => $invite['token'],
        'email' => 'outra@exemplo.pt',
        'name' => 'Outra Pessoa',
        'password' => 'Outra2026!',
        'password_confirm' => 'Outra2026!',
    ]), 'segunda utilização recusada');

    $audit = json_encode(AdminEventRepository::forTarget('invite', $invite['id']));
    assert_true(!str_contains((string) $audit, $invite['token']), 'auditoria não guarda o token');
});

test('T27', 'Convite inválido não cria conta', function (): void {
    fresh_database();
    assert_throws(ValidationException::class, fn () => TeamService::register([
        'token' => str_repeat('a', 64),
        'email' => 'x@exemplo.pt',
        'name' => 'Pessoa X',
        'password' => 'Pessoa2026!',
        'password_confirm' => 'Pessoa2026!',
    ]), 'token inexistente');
    assert_same(null, UserRepository::findByEmail('x@exemplo.pt'), 'nada criado');
});

test('T27b', 'Convite revogado e expirado não criam conta', function (): void {
    $f = fresh_database();

    $revoked = invite_token($f['gestor'], 'revogado@exemplo.pt');
    TeamService::revokeInvite($f['gestor'], $revoked['id']);
    assert_throws(ValidationException::class, fn () => TeamService::register([
        'token' => $revoked['token'], 'email' => 'revogado@exemplo.pt', 'name' => 'Revogado',
        'password' => 'Revogado2026!', 'password_confirm' => 'Revogado2026!',
    ]), 'convite revogado');

    $expired = invite_token($f['gestor'], 'expirado@exemplo.pt');
    $f['pdo']->prepare('UPDATE invites SET expires_at = :past WHERE id = :id')
        ->execute(['past' => '2020-01-01 00:00:00', 'id' => $expired['id']]);
    assert_throws(ValidationException::class, fn () => TeamService::register([
        'token' => $expired['token'], 'email' => 'expirado@exemplo.pt', 'name' => 'Expirado',
        'password' => 'Expirado2026!', 'password_confirm' => 'Expirado2026!',
    ]), 'convite expirado');

    assert_same(null, UserRepository::findByEmail('revogado@exemplo.pt'), 'revogado não criado');
    assert_same(null, UserRepository::findByEmail('expirado@exemplo.pt'), 'expirado não criado');
});

test('T28', 'Registo valida email do convite e palavras-passe', function (): void {
    $f = fresh_database();
    $invite = invite_token($f['gestor'], 'convidado@exemplo.pt');

    assert_throws(ValidationException::class, fn () => TeamService::register([
        'token' => $invite['token'], 'email' => 'outro@exemplo.pt', 'name' => 'Outro',
        'password' => 'Outro2026!', 'password_confirm' => 'Outro2026!',
    ]), 'email diferente do convite');

    assert_throws(ValidationException::class, fn () => TeamService::register([
        'token' => $invite['token'], 'email' => 'convidado@exemplo.pt', 'name' => 'Convidado',
        'password' => 'fraca', 'password_confirm' => 'fraca',
    ]), 'palavra-passe fraca');

    assert_throws(ValidationException::class, fn () => TeamService::register([
        'token' => $invite['token'], 'email' => 'convidado@exemplo.pt', 'name' => 'Convidado',
        'password' => 'Convidado2026!', 'password_confirm' => 'Diferente2026!',
    ]), 'confirmação diferente');

    assert_same(null, UserRepository::findByEmail('convidado@exemplo.pt'), 'nada criado');
    assert_same(0, (int) InviteRepository::find($invite['id'])['used_by'], 'convite não consumido');
});

test('T29', 'Perfil do convite não pode ser elevado pelo formulário', function (): void {
    $f = fresh_database();
    $invite = invite_token($f['gestor'], 'colab.novo@exemplo.pt', 'colaborador');

    $user = TeamService::register([
        'token' => $invite['token'],
        'email' => 'colab.novo@exemplo.pt',
        'name' => 'Colab Novo',
        'password' => 'Colab2026!',
        'password_confirm' => 'Colab2026!',
        'role' => 'gestor',
    ]);

    assert_same('colaborador', $user['role'], 'role do formulário é ignorado');

    $managerInvite = invite_token($f['gestor'], 'gestor.novo@exemplo.pt', 'gestor');
    $manager = TeamService::register([
        'token' => $managerInvite['token'],
        'email' => 'gestor.novo@exemplo.pt',
        'name' => 'Gestor Novo',
        'password' => 'Gestor2026!',
        'password_confirm' => 'Gestor2026!',
    ]);
    assert_same('gestor', $manager['role'], 'convite de gestor cria gestor');
});

test('T30', 'Gestão de equipa protege o último gestor e contas próprias', function (): void {
    $f = fresh_database();
    $gestorId = (int) $f['gestor']['id'];

    assert_throws(AuthorizationException::class, fn () => TeamService::setRole($f['gestor'], $gestorId, 'colaborador'), 'auto-despromoção');
    assert_throws(AuthorizationException::class, fn () => TeamService::setActive($f['gestor'], $gestorId, false), 'auto-desativação');
    assert_same('gestor', UserRepository::find($gestorId)['role'], 'perfil mantém-se');
    assert_same(1, (int) UserRepository::find($gestorId)['active'], 'conta mantém-se ativa');
    assert_same(1, UserRepository::activeManagerCount(), 'continua a haver um gestor');

    $colabId = (int) $f['colaborador']['id'];
    TeamService::setActive($f['gestor'], $colabId, false);
    assert_same(0, (int) UserRepository::find($colabId)['active'], 'colaborador desativado');
    assert_same(null, \ObraClara\Support\Auth::verifyCredentials('colaborador@obraclara.pt', 'Colab2026!'), 'login bloqueado');

    $_SESSION['user_id'] = $colabId;
    assert_same(null, \ObraClara\Support\Auth::user(), 'sessão rejeitada após desativação');
    assert_true(!isset($_SESSION['user_id']), 'sessão limpa');
});

test('T31', 'Perfil valida email único e regista auditoria', function (): void {
    $f = fresh_database();
    $updated = AccountService::updateProfile($f['colaborador'], [
        'name' => 'Rui Marques Atualizado',
        'email' => 'RUI.NOVO@EXEMPLO.PT',
    ]);

    assert_same('Rui Marques Atualizado', $updated['name']);
    assert_same('rui.novo@exemplo.pt', $updated['email'], 'email normalizado');
    assert_throws(ValidationException::class, fn () => AccountService::updateProfile($updated, [
        'name' => 'Outro Nome', 'email' => 'gestor@obraclara.pt',
    ]), 'email duplicado');
    assert_throws(ValidationException::class, fn () => AccountService::updateProfile($updated, [
        'name' => 'A', 'email' => 'rui.novo@exemplo.pt',
    ]), 'nome curto');

    $events = AdminEventRepository::forTarget('user', (int) $updated['id']);
    assert_true($events !== [], 'auditoria do perfil');
    assert_same('perfil_alterado', $events[0]['action']);
});

test('T32', 'Alteração de palavra-passe exige atual e confirmação', function (): void {
    $f = fresh_database();
    $user = $f['colaborador'];

    assert_throws(ValidationException::class, fn () => AccountService::changePassword($user, [
        'current_password' => 'errada', 'password' => 'Nova2026!', 'password_confirm' => 'Nova2026!',
    ]), 'palavra-passe atual errada');
    assert_throws(ValidationException::class, fn () => AccountService::changePassword($user, [
        'current_password' => 'Colab2026!', 'password' => 'curta', 'password_confirm' => 'curta',
    ]), 'palavra-passe fraca');
    assert_throws(ValidationException::class, fn () => AccountService::changePassword($user, [
        'current_password' => 'Colab2026!', 'password' => 'Nova2026!', 'password_confirm' => 'Outra2026!',
    ]), 'confirmação diferente');

    AccountService::changePassword($user, [
        'current_password' => 'Colab2026!', 'password' => 'Nova2026!', 'password_confirm' => 'Nova2026!',
    ]);

    assert_same(null, \ObraClara\Support\Auth::verifyCredentials('colaborador@obraclara.pt', 'Colab2026!'), 'antiga recusada');
    assert_true(\ObraClara\Support\Auth::verifyCredentials('colaborador@obraclara.pt', 'Nova2026!') !== null, 'nova aceite');

    $fresh = UserRepository::find((int) $user['id']);
    assert_throws(ValidationException::class, fn () => AccountService::changePassword($fresh, [
        'current_password' => 'Nova2026!', 'password' => 'Nova2026!', 'password_confirm' => 'Nova2026!',
    ]), 'reutilização da mesma palavra-passe');
});

test('T33', 'PDF real contém valores, acentos e várias páginas quando necessário', function (): void {
    $f = fresh_database();
    $changeId = ChangeService::create($f['projects'][0], [
        'title' => 'Pavimento vinílico — sala e hall',
        'description' => 'Substituição do laminado por vinílico, com regularização do betão e rodapé novo. Acentuação: ção, ã, é, €.',
        'amount' => '650,00',
    ], $f['colaborador'], true);
    ChangeService::decide($changeId, $f['gestor'], 'aprovar', 'Aprovado pelo cliente.');

    $project = ProjectRepository::find($f['projects'][0]);
    $pdf = ProjectPdfService::generate($project, ChangeRepository::forProject($f['projects'][0]));

    assert_true(str_starts_with($pdf, '%PDF-1.4'), 'cabeçalho PDF');
    assert_true(str_contains($pdf, '%%EOF'), 'fim PDF');
    assert_true(str_contains($pdf, '/Type /Page'), 'objeto de página');
    $needle = iconv('UTF-8', 'Windows-1252//TRANSLIT', '8 650,00 €');
    assert_true(str_contains($pdf, (string) $needle), 'orçamento atualizado no PDF');
    assert_true(str_contains($pdf, 'Pavimento vin') || str_contains($pdf, 'Pavimento'), 'alteração no PDF');

    for ($i = 0; $i < 9; $i++) {
        ChangeService::create($f['projects'][1], [
            'title' => 'Trabalho adicional ' . $i,
            'description' => str_repeat('Descrição extensa com acentuação e detalhe técnico. ', 18),
            'amount' => '10,00',
        ], $f['colaborador'], true);
    }
    $longProject = ProjectRepository::find($f['projects'][1]);
    $longPdf = ProjectPdfService::generate($longProject, ChangeRepository::forProject($f['projects'][1]));
    preg_match('/\/Count (\d+)/', $longPdf, $matches);
    assert_true((int) ($matches[1] ?? 0) >= 2, 'PDF longo tem várias páginas');

    Lang::setLocale('en');
    $englishPdf = ProjectPdfService::generate($project, ChangeRepository::forProject($f['projects'][0]));
    assert_true(str_contains($englishPdf, '€8,650.00') || str_contains($englishPdf, '8,650.00'), 'PDF em inglês');
    Lang::setLocale('pt');
});

test('T34', 'Dicionários PT/EN têm as mesmas chaves e cobrem as vistas', function (): void {
    $pt = require OBRACLARA_ROOT . '/lang/pt.php';
    $en = require OBRACLARA_ROOT . '/lang/en.php';

    assert_same([], array_values(array_diff(array_keys($pt), array_keys($en))), 'chaves em falta em EN');
    assert_same([], array_values(array_diff(array_keys($en), array_keys($pt))), 'chaves extra em EN');

    $used = [];
    $directories = [OBRACLARA_ROOT . '/app/views', OBRACLARA_ROOT . '/app/Http', OBRACLARA_ROOT . '/app/Services', OBRACLARA_ROOT . '/app/Support'];
    foreach ($directories as $directory) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            preg_match_all("/(?<![a-z_])t\('([a-z0-9_.]+)'\)/", $source, $matches);
            foreach ($matches[1] as $key) {
                $used[$key] = true;
            }
        }
    }

    $missing = array_values(array_diff(array_keys($used), array_keys($pt)));
    assert_same([], $missing, 'chaves usadas sem tradução');

    Lang::setLocale('en');
    assert_same('Dashboard', Lang::t('nav.dashboard'));
    assert_same('€8,650.00', \ObraClara\Support\Money::formatFor(865000, 'en'));
    assert_same('8 650,00 €', \ObraClara\Support\Money::formatFor(865000, 'pt'));
    Lang::setLocale('pt');
});

test('T35', 'Tutorial concluído fica guardado por utilizador', function (): void {
    $f = fresh_database();
    assert_same(null, $f['colaborador']['tour_completed_at'], 'tutorial pendente');
    AccountService::completeTour((int) $f['colaborador']['id']);

    $updated = UserRepository::find((int) $f['colaborador']['id']);
    $other = UserRepository::find((int) $f['outro']['id']);
    assert_true($updated['tour_completed_at'] !== null, 'estado guardado');
    assert_same(null, $other['tour_completed_at'], 'outro utilizador não foi afetado');
});

test('T36', 'Migrações atualizam base antiga sem perder dados e são idempotentes', function (): void {
    $path = dirname(__DIR__) . '/tmp/test-migration.sqlite';
    foreach ([$path, $path . '-wal', $path . '-shm'] as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }

    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, email TEXT NOT NULL UNIQUE COLLATE NOCASE,
        password_hash TEXT NOT NULL, role TEXT NOT NULL, active INTEGER NOT NULL DEFAULT 1, created_at TEXT NOT NULL
    )");
    $pdo->exec("INSERT INTO users (name, email, password_hash, role, active, created_at)
                VALUES ('Conta Antiga', 'antiga@exemplo.pt', 'hash', 'gestor', 1, '2026-01-01 00:00:00')");

    $executed = Migrator::run($pdo);
    assert_true(in_array('2026_10_29_001_preferencias_utilizador', $executed, true), 'migração de colunas');
    assert_true(in_array('2026_10_29_002_convites_e_auditoria', $executed, true), 'migração de tabelas');

    $columns = array_column($pdo->query('PRAGMA table_info(users)')->fetchAll(), 'name');
    foreach (['locale', 'tour_completed_at', 'last_login_at', 'updated_at'] as $column) {
        assert_true(in_array($column, $columns, true), 'coluna ' . $column);
    }
    assert_same('Conta Antiga', $pdo->query('SELECT name FROM users WHERE id = 1')->fetchColumn(), 'dados preservados');
    assert_same('pt', $pdo->query('SELECT locale FROM users WHERE id = 1')->fetchColumn(), 'preferência por defeito');

    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table'")->fetchAll(PDO::FETCH_COLUMN);
    foreach (['invites', 'admin_events', 'schema_migrations'] as $table) {
        assert_true(in_array($table, $tables, true), 'tabela ' . $table);
    }

    assert_same([], Migrator::pending($pdo), 'sem migrações pendentes');
    assert_same([], Migrator::run($pdo), 'segunda execução não altera');

    $pdo = null;
    foreach ([$path, $path . '-wal', $path . '-shm'] as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
});

test('T37', 'Email duplicado é recusado mesmo com convite válido', function (): void {
    $f = fresh_database();
    $invite = invite_token($f['gestor'], 'duplicado@exemplo.pt');

    // Simula uma corrida: a conta foi criada depois de o convite ser emitido.
    UserRepository::create([
        'name' => 'Conta Existente',
        'email' => 'duplicado@exemplo.pt',
        'password_hash' => password_hash('Existente2026!', PASSWORD_DEFAULT),
        'role' => 'colaborador',
        'locale' => 'pt',
        'created_at' => now(),
    ]);

    assert_throws(ValidationException::class, fn () => TeamService::register([
        'token' => $invite['token'],
        'email' => 'duplicado@exemplo.pt',
        'name' => 'Duplicado',
        'password' => 'Duplicado2026!',
        'password_confirm' => 'Duplicado2026!',
    ]), 'email duplicado');

    assert_same(null, InviteRepository::find($invite['id'])['used_at'], 'convite não consumido');
});

/* --------------------------------------------------------------------------
 * Resumo
 * -------------------------------------------------------------------------- */

$failed = array_filter($results, static fn (array $row): bool => $row['state'] !== 'PASSOU');

echo "\n" . str_repeat('-', 68) . "\n";
echo 'Total: ' . count($results) . ' testes · ' . (count($results) - count($failed)) . ' passaram · ' . count($failed) . " falharam\n";

if ($failed !== []) {
    echo "FALHAS:\n";
    foreach ($failed as $row) {
        echo '  ' . $row['id'] . ' — ' . $row['detail'] . "\n";
    }
}

// Limpeza: a base descartável não fica no projeto.
Database::setPdo(null);
foreach ([$testDatabase, $testDatabase . '-wal', $testDatabase . '-shm'] as $file) {
    if (is_file($file)) {
        @unlink($file);
    }
}

exit($failed === [] ? 0 : 1);
