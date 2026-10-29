<?php

declare(strict_types=1);

/**
 * ObraClara V2 — ponto de entrada único (front controller).
 * A raiz pública é public/, portanto a base de dados e o código PHP
 * da aplicação nunca são acessíveis por URL.
 */

use ObraClara\Http\Controllers\AuthController;
use ObraClara\Http\Controllers\ChangeController;
use ObraClara\Http\Controllers\ClientController;
use ObraClara\Http\Controllers\DashboardController;
use ObraClara\Http\Controllers\HistoryController;
use ObraClara\Http\Controllers\ProfileController;
use ObraClara\Http\Controllers\ProjectController;
use ObraClara\Http\Controllers\TeamController;
use ObraClara\Http\Router;
use ObraClara\Support\Auth;
use ObraClara\Support\Csrf;
use ObraClara\Support\Database;
use ObraClara\Support\Lang;
use ObraClara\Support\NotFoundException;
use ObraClara\Support\Session;
use ObraClara\Support\View;

require dirname(__DIR__) . '/app/bootstrap.php';

// Base do URL calculada a partir da raiz pública real (funciona em Laragon
// com subpasta e no servidor integrado com -t public).
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
$publicDir = realpath(__DIR__) ?: __DIR__;
$base = '';

if ($documentRoot !== '' && str_starts_with($publicDir, $documentRoot)) {
    $base = rtrim(str_replace('\\', '/', substr($publicDir, strlen($documentRoot))), '/');
}

$GLOBALS['OBRACLARA_BASE'] = $base === '/' ? '' : $base;

// Cabeçalhos de segurança para todas as respostas.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");
header('Cache-Control: no-store, no-cache, must-revalidate');

if (!is_file(Database::path())) {
    http_response_code(503);
    require OBRACLARA_ROOT . '/app/views/setup_needed.php';
    exit;
}

Session::start();
Lang::boot();

// Todos os POST exigem token CSRF válido, sem exceções.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !Csrf::check($_POST['_token'] ?? null)) {
    View::error(419, t('errors.419_title'), t('errors.419_text'));
    exit;
}

$router = new Router();

$router->get('/', [DashboardController::class, 'index']);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/registar', [AuthController::class, 'showRegister']);
$router->post('/registar', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/perfil', [ProfileController::class, 'index']);
$router->post('/perfil', [ProfileController::class, 'update']);
$router->post('/perfil/palavra-passe', [ProfileController::class, 'password']);
$router->post('/perfil/idioma', [ProfileController::class, 'locale']);
$router->post('/perfil/tutorial', [ProfileController::class, 'completeTour']);

$router->get('/equipa', [TeamController::class, 'index']);
$router->post('/equipa/convites', [TeamController::class, 'createInvite']);
$router->post('/equipa/convites/{id}/revogar', [TeamController::class, 'revokeInvite']);
$router->get('/equipa/utilizadores/{id}', [TeamController::class, 'showUser']);
$router->post('/equipa/utilizadores/{id}/papel', [TeamController::class, 'setRole']);
$router->post('/equipa/utilizadores/{id}/estado', [TeamController::class, 'setActive']);

$router->get('/clientes', [ClientController::class, 'index']);
$router->get('/clientes/criar', [ClientController::class, 'createForm']);
$router->post('/clientes/criar', [ClientController::class, 'create']);
$router->get('/clientes/{id}', [ClientController::class, 'show']);
$router->get('/clientes/{id}/editar', [ClientController::class, 'editForm']);
$router->post('/clientes/{id}/editar', [ClientController::class, 'edit']);
$router->post('/clientes/{id}/arquivar', [ClientController::class, 'archive']);
$router->post('/clientes/{id}/reativar', [ClientController::class, 'reactivate']);

$router->get('/obras', [ProjectController::class, 'index']);
$router->get('/obras/criar', [ProjectController::class, 'createForm']);
$router->post('/obras/criar', [ProjectController::class, 'create']);
$router->get('/obras/{id}', [ProjectController::class, 'show']);
$router->get('/obras/{id}/pdf', [ProjectController::class, 'pdf']);
$router->get('/obras/{id}/editar', [ProjectController::class, 'editForm']);
$router->post('/obras/{id}/editar', [ProjectController::class, 'edit']);
$router->post('/obras/{id}/arquivar', [ProjectController::class, 'archive']);
$router->post('/obras/{id}/reativar', [ProjectController::class, 'reactivate']);

$router->get('/obras/{id}/alteracoes/criar', [ChangeController::class, 'createForm']);
$router->post('/obras/{id}/alteracoes/criar', [ChangeController::class, 'create']);
$router->get('/alteracoes/{id}/editar', [ChangeController::class, 'editForm']);
$router->post('/alteracoes/{id}/editar', [ChangeController::class, 'edit']);
$router->post('/alteracoes/{id}/submeter', [ChangeController::class, 'submit']);
$router->get('/alteracoes/{id}/decidir', [ChangeController::class, 'decideForm']);
$router->post('/alteracoes/{id}/decidir', [ChangeController::class, 'decide']);

$router->get('/historico', [HistoryController::class, 'index']);

$path = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($path, PHP_URL_PATH) ?: '/';
$path = rawurldecode($path);

if ($base !== '' && str_starts_with($path, $base)) {
    $path = substr($path, strlen($base));
}

$path = '/' . trim($path, '/');

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $path);
} catch (NotFoundException $exception) {
    if (!Auth::check()) {
        header('Location: ' . url('/login'));
        exit;
    }
    View::error(404, t('errors.404_title'), $exception->getMessage());
} catch (\ObraClara\Support\AuthorizationException $exception) {
    View::error(403, t('errors.403_title'), $exception->getMessage());
} catch (\ObraClara\Support\BusinessRuleException $exception) {
    View::error(409, t('errors.409_title'), $exception->getMessage());
} catch (\Throwable $exception) {
    // Nunca revelar SQL, caminhos ou stacktrace ao utilizador.
    error_log('[ObraClara] ' . $exception->getMessage() . ' @ ' . $exception->getFile() . ':' . $exception->getLine());
    View::error(500, t('errors.500_title'), t('errors.500_text'));
}
