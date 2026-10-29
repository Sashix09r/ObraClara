<?php

declare(strict_types=1);

namespace ObraClara\Http\Controllers;

use ObraClara\Services\TeamService;
use ObraClara\Support\Auth;
use ObraClara\Support\Flash;
use ObraClara\Support\ValidationException;
use ObraClara\Support\View;

final class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 60;
    private const MAX_REGISTER_ATTEMPTS = 12;
    private const REGISTER_LOCK_SECONDS = 300;

    /** @param array<string, string> $params */
    public function showLogin(array $params = []): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }

        $this->renderLogin();
    }

    /** @param array<string, string> $params */
    public function login(array $params = []): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }

        $lockedUntil = (int) ($_SESSION['login_locked_until'] ?? 0);

        if ($lockedUntil > time()) {
            $seconds = $lockedUntil - time();
            $this->renderLogin([
                'error' => t('flash.too_many_attempts', ['seconds' => $seconds]),
                'email' => post_string('email'),
            ], 429);

            return;
        }

        $email = post_string('email');
        $password = (string) ($_POST['password'] ?? '');
        $errors = [];

        if ($email === '') {
            $errors['email'] = t('flash.email_required');
        }

        if ($password === '') {
            $errors['password'] = t('flash.password_required');
        }

        if ($errors !== []) {
            $this->renderLogin(['errors' => $errors, 'email' => $email], 422);

            return;
        }

        if (!Auth::attempt($email, $password)) {
            $_SESSION['login_attempts'] = (int) ($_SESSION['login_attempts'] ?? 0) + 1;

            if ($_SESSION['login_attempts'] >= self::MAX_ATTEMPTS) {
                $_SESSION['login_locked_until'] = time() + self::LOCK_SECONDS;
                $_SESSION['login_attempts'] = 0;
            }

            $this->renderLogin([
                'error' => t('flash.invalid_credentials'),
                'email' => $email,
            ], 401);

            return;
        }

        unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);
        Flash::success(t('flash.login_success'));

        $this->redirect('/');
    }

    /** @param array<string, string> $params */
    public function showRegister(array $params = []): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }

        $raw = isset($_GET['token']) && is_string($_GET['token']) ? $_GET['token'] : '';
        $invite = TeamService::inviteFromRawToken($raw);
        $error = null;

        if ($raw !== '') {
            $error = $this->inviteProblem($invite);
        }

        $this->renderRegister([
            'token' => $raw,
            'email' => $invite !== null && $error === null ? (string) $invite['email'] : '',
            'name' => '',
            'error' => $error,
        ]);
    }

    /** @param array<string, string> $params */
    public function register(array $params = []): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }

        $lockedUntil = (int) ($_SESSION['register_locked_until'] ?? 0);

        if ($lockedUntil > time()) {
            $this->renderRegister([
                'token' => post_string('token'),
                'email' => post_string('email'),
                'name' => post_string('name'),
                'error' => t('flash.too_many_attempts', ['seconds' => $lockedUntil - time()]),
            ], 429);

            return;
        }

        try {
            $user = TeamService::register($_POST);
        } catch (ValidationException $exception) {
            $_SESSION['register_attempts'] = (int) ($_SESSION['register_attempts'] ?? 0) + 1;

            if ($_SESSION['register_attempts'] >= self::MAX_REGISTER_ATTEMPTS) {
                $_SESSION['register_locked_until'] = time() + self::REGISTER_LOCK_SECONDS;
                $_SESSION['register_attempts'] = 0;
            }

            $this->renderRegister([
                'token' => post_string('token'),
                'email' => post_string('email'),
                'name' => post_string('name'),
                'errors' => $exception->errors(),
            ], 422);

            return;
        }

        unset($_SESSION['register_attempts'], $_SESSION['register_locked_until']);

        if (!Auth::attempt((string) $user['email'], (string) ($_POST['password'] ?? ''))) {
            $this->redirect('/login');
        }

        Flash::success(t('auth.account_created'));
        $this->redirect('/');
    }

    /** @param array<string, string> $params */
    public function logout(array $params = []): void
    {
        Auth::logout();
        header('Location: ' . url('/login?signed_out=1'));
        exit;
    }

    /** @param array<string, mixed> $invite */
    private function inviteProblem(?array $invite): ?string
    {
        if ($invite === null) {
            return t('auth.invite_invalid');
        }

        if ($invite['used_at'] !== null) {
            return t('auth.invite_used');
        }

        if ($invite['revoked_at'] !== null) {
            return t('auth.invite_revoked');
        }

        if (strtotime((string) $invite['expires_at']) <= time()) {
            return t('auth.invite_expired');
        }

        return null;
    }

    /** @param array<string, mixed> $data */
    private function renderLogin(array $data = [], int $status = 200): void
    {
        http_response_code($status);

        $errors = $data['errors'] ?? [];
        $error = $data['error'] ?? null;
        $email = $data['email'] ?? '';
        $signedOut = isset($_GET['signed_out']);

        ob_start();
        require OBRACLARA_ROOT . '/app/views/auth/login.php';
        $content = (string) ob_get_clean();

        require OBRACLARA_ROOT . '/app/views/auth/layout.php';
    }

    /** @param array<string, mixed> $data */
    private function renderRegister(array $data, int $status = 200): void
    {
        http_response_code($status);

        $errors = $data['errors'] ?? [];
        $error = $data['error'] ?? null;
        $token = $data['token'] ?? '';
        $email = $data['email'] ?? '';
        $name = $data['name'] ?? '';
        $title = t('auth.register_title');

        ob_start();
        require OBRACLARA_ROOT . '/app/views/auth/register.php';
        $content = (string) ob_get_clean();

        require OBRACLARA_ROOT . '/app/views/auth/layout.php';
    }
}
