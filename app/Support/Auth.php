<?php

declare(strict_types=1);

namespace ObraClara\Support;

use ObraClara\Repositories\UserRepository;

/**
 * Autenticação e perfis. A permissão é revalidada na base de dados em
 * cada pedido, para que desativar um utilizador tenha efeito imediato.
 */
final class Auth
{
    /** Hash de referência para igualar o tempo de resposta quando o email não existe. */
    private const DUMMY_HASH = '$2y$10$uci4W1aHqThTRJ4WvbswxehsBjkEOxstgHLevgB4Syfz.wnRWkqQq';

    /** @return array<string, mixed>|null */
    public static function verifyCredentials(string $email, string $password): ?array
    {
        $email = mb_strtolower(trim($email));
        $user = UserRepository::findByEmail($email);

        if ($user === null) {
            password_verify($password, self::DUMMY_HASH);

            return null;
        }

        if ((int) $user['active'] !== 1) {
            password_verify($password, $user['password_hash']);

            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            UserRepository::updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        }

        return $user;
    }

    public static function attempt(string $email, string $password): bool
    {
        $user = self::verifyCredentials($email, $password);

        if ($user === null) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        Lang::setLocale((string) ($user['locale'] ?? 'pt'));
        UserRepository::markLogin((int) $user['id']);
        Csrf::rotate();

        return true;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    /** @return array<string, mixed>|null */
    public static function user(): ?array
    {
        $id = $_SESSION['user_id'] ?? null;

        if (!is_int($id) && !ctype_digit((string) $id)) {
            return null;
        }

        $user = UserRepository::find((int) $id);

        if ($user === null || (int) $user['active'] !== 1) {
            // Uma conta desativada perde imediatamente a sessão autenticada.
            unset($_SESSION['user_id']);

            return null;
        }

        return $user;
    }

    /** @return array<string, mixed> */
    public static function requireUser(): array
    {
        $user = self::user();

        if ($user === null) {
            Flash::error(t('flash.login_required'));
            header('Location: ' . url('/login'));
            exit;
        }

        return $user;
    }

    public static function isManager(?array $user = null): bool
    {
        $user ??= self::user();

        return $user !== null && $user['role'] === 'gestor';
    }

    /** @return array<string, mixed> */
    public static function requireManager(?string $message = null): array
    {
        $user = self::requireUser();

        if (!self::isManager($user)) {
            throw new AuthorizationException($message ?? t('validation.manager_only'));
        }

        return $user;
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie((string) session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => 'Lax',
            ]);
        }

        session_destroy();
    }
}
