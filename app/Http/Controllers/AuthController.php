<?php

declare(strict_types=1);

namespace ObraClara\Http\Controllers;

use ObraClara\Support\Auth;
use ObraClara\Support\Flash;
use ObraClara\Support\View;

final class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 60;

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
                'error' => 'Demasiadas tentativas. Aguarda ' . $seconds . ' segundos antes de tentar novamente.',
                'email' => post_string('email'),
            ], 429);

            return;
        }

        $email = post_string('email');
        $password = (string) ($_POST['password'] ?? '');
        $errors = [];

        if ($email === '') {
            $errors['email'] = 'Indica o email.';
        }

        if ($password === '') {
            $errors['password'] = 'Indica a palavra-passe.';
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
                'error'  => 'Credenciais inválidas. Confirma o email e a palavra-passe.',
                'email'  => $email,
            ], 401);

            return;
        }

        unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);
        Flash::success('Sessão iniciada. Bem-vindo de volta.');

        $this->redirect('/');
    }

    /** @param array<string, string> $params */
    public function logout(array $params = []): void
    {
        Auth::logout();
        header('Location: ' . url('/login'));
        exit;
    }

    /** @param array<string, mixed> $data */
    private function renderLogin(array $data = [], int $status = 200): void
    {
        http_response_code($status);

        $errors = $data['errors'] ?? [];
        $error = $data['error'] ?? null;
        $email = $data['email'] ?? '';

        ob_start();
        require OBRACLARA_ROOT . '/app/views/auth/login.php';
        $content = (string) ob_get_clean();

        require OBRACLARA_ROOT . '/app/views/auth/layout.php';
    }
}
