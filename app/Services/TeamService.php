<?php

declare(strict_types=1);

namespace ObraClara\Services;

use ObraClara\Repositories\AdminEventRepository;
use ObraClara\Repositories\InviteRepository;
use ObraClara\Repositories\UserRepository;
use ObraClara\Support\AuthorizationException;
use ObraClara\Support\Database;
use ObraClara\Support\ValidationException;
use PDOException;
use Throwable;

final class TeamService
{
    private const ROLES = ['gestor', 'colaborador'];
    private const EXPIRY_DAYS = [1, 3, 7, 14];

    /**
     * Cria um convite. O token completo só é devolvido uma vez; a base guarda
     * apenas o SHA-256, pelo que uma fuga da BD não permite reutilizar convites.
     *
     * @param array<string, mixed> $actor
     * @param array<string, mixed> $input
     * @return array{id: int, token: string, email: string, expires_at: string}
     */
    public static function createInvite(array $actor, array $input): array
    {
        self::ensureManager($actor);

        $errors = [];
        $email = mb_strtolower(trim((string) ($input['email'] ?? '')));
        $role = trim((string) ($input['role'] ?? 'colaborador'));
        $days = (int) ($input['expiry'] ?? 7);

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false || mb_strlen($email) > 150) {
            $errors['email'] = t('validation.invite_email');
        } elseif (UserRepository::findByEmail($email) !== null) {
            $errors['email'] = t('validation.invite_email_taken');
        } elseif (InviteRepository::pendingForEmail($email) !== null) {
            $errors['email'] = t('validation.invite_duplicate');
        }

        if (!in_array($role, self::ROLES, true)) {
            $errors['role'] = t('validation.invite_role');
        }

        if (!in_array($days, self::EXPIRY_DAYS, true)) {
            $errors['expiry'] = t('validation.invite_expiry');
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + $days * 86400);
        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $id = InviteRepository::create([
                'email'      => $email,
                'role'       => $role,
                'token_hash' => hash('sha256', $token),
                'expires_at' => $expiresAt,
                'created_by' => (int) $actor['id'],
                'created_at' => now(),
            ]);

            AdminEventRepository::log(
                (int) $actor['id'],
                'convite_criado',
                'invite',
                $id,
                $role . ' · ' . $email . ' · expira ' . $expiresAt
            );

            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }

        return ['id' => $id, 'token' => $token, 'email' => $email, 'expires_at' => $expiresAt];
    }

    /** @param array<string, mixed> $actor */
    public static function revokeInvite(array $actor, int $inviteId): void
    {
        self::ensureManager($actor);
        $invite = InviteRepository::find($inviteId);

        if ($invite === null) {
            throw new ValidationException(['invite' => t('validation.invite_invalid')]);
        }

        if (!InviteRepository::revoke($inviteId)) {
            throw new ValidationException(['invite' => t('validation.invite_invalid')]);
        }

        AdminEventRepository::log((int) $actor['id'], 'convite_revogado', 'invite', $inviteId, (string) $invite['email']);
    }

    /** @return array<string, mixed>|null */
    public static function inviteFromRawToken(string $raw): ?array
    {
        $token = self::normalizeToken($raw);

        if ($token === null) {
            return null;
        }

        return InviteRepository::findByHash(hash('sha256', $token));
    }

    /**
     * Registo público apenas com convite válido. O perfil vem do convite e
     * nunca do formulário, impedindo a criação autónoma de gestores.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed> novo utilizador
     */
    public static function register(array $input): array
    {
        $errors = [];
        $rawToken = (string) ($input['token'] ?? '');
        $token = self::normalizeToken($rawToken);
        $invite = $token === null
            ? null
            : InviteRepository::findByHash(hash('sha256', $token));

        if ($invite === null) {
            $errors['token'] = t('validation.invite_invalid');
        } elseif ($invite['used_at'] !== null) {
            $errors['token'] = t('validation.invite_used');
        } elseif ($invite['revoked_at'] !== null) {
            $errors['token'] = t('validation.invite_revoked');
        } elseif (strtotime((string) $invite['expires_at']) <= time()) {
            $errors['token'] = t('validation.invite_expired');
        }

        $email = mb_strtolower(trim((string) ($input['email'] ?? '')));
        $name = trim((string) ($input['name'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        $confirm = (string) ($input['password_confirm'] ?? '');

        if ($invite !== null && $email !== (string) $invite['email']) {
            $errors['email'] = t('validation.invite_email_mismatch');
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || mb_strlen($email) > 150) {
            $errors['email'] = t('validation.register_email');
        }

        if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
            $errors['name'] = t('validation.register_name');
        }

        if (!self::passwordIsStrong($password)) {
            $errors['password'] = t('validation.register_password');
        }

        if ($password !== $confirm) {
            $errors['password_confirm'] = t('validation.register_confirm');
        }

        if ($errors === [] && UserRepository::findByEmail($email) !== null) {
            $errors['email'] = t('validation.register_email_taken');
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            // Revalida dentro da transação para impedir reutilização concorrente.
            $fresh = $invite === null || $token === null
                ? null
                : InviteRepository::findByHash(hash('sha256', $token));

            if ($fresh === null || $fresh['used_at'] !== null || $fresh['revoked_at'] !== null
                || strtotime((string) $fresh['expires_at']) <= time()) {
                throw new ValidationException(['token' => t('validation.invite_invalid')]);
            }

            $userId = UserRepository::create([
                'name'          => $name,
                'email'         => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role'          => (string) $fresh['role'],
                'locale'        => \ObraClara\Support\Lang::locale(),
                'created_at'    => now(),
            ]);

            InviteRepository::markUsed((int) $fresh['id'], $userId);
            AdminEventRepository::log($userId, 'conta_criada', 'user', $userId, 'Convite #' . $fresh['id']);

            $pdo->commit();
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if (str_contains($exception->getMessage(), 'UNIQUE')) {
                throw new ValidationException(['email' => t('validation.register_email_taken')]);
            }
            throw $exception;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }

        return UserRepository::find($userId) ?? [];
    }

    /**
     * @param array<string, mixed> $actor
     */
    public static function setRole(array $actor, int $targetId, string $role): void
    {
        self::ensureManager($actor);
        $target = UserRepository::find($targetId);

        if ($target === null) {
            throw new ValidationException(['target' => t('validation.user_not_found')]);
        }

        if ((int) $target['id'] === (int) $actor['id']) {
            throw new AuthorizationException(t('validation.own_account'));
        }

        if (!in_array($role, self::ROLES, true)) {
            throw new ValidationException(['role' => t('validation.invite_role')]);
        }

        if ($target['role'] === 'gestor' && $role !== 'gestor'
            && (int) $target['active'] === 1 && UserRepository::activeManagerCount() <= 1) {
            throw new ValidationException(['role' => t('validation.last_manager')]);
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            UserRepository::setRole($targetId, $role);
            AdminEventRepository::log(
                (int) $actor['id'],
                'papel_alterado',
                'user',
                $targetId,
                $target['role'] . ' → ' . $role
            );
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    /** @param array<string, mixed> $actor */
    public static function setActive(array $actor, int $targetId, bool $active): void
    {
        self::ensureManager($actor);
        $target = UserRepository::find($targetId);

        if ($target === null) {
            throw new ValidationException(['target' => t('validation.user_not_found')]);
        }

        if (!$active && (int) $target['id'] === (int) $actor['id']) {
            throw new AuthorizationException(t('validation.own_deactivation'));
        }

        if (!$active && UserRepository::isActiveManager($targetId) && UserRepository::activeManagerCount() <= 1) {
            throw new ValidationException(['active' => t('validation.last_manager')]);
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            UserRepository::setActive($targetId, $active);
            AdminEventRepository::log(
                (int) $actor['id'],
                $active ? 'conta_ativada' : 'conta_desativada',
                'user',
                $targetId
            );
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    /** @param array<string, mixed> $actor */
    public static function ensureManager(array $actor): void
    {
        if (($actor['role'] ?? '') !== 'gestor' || (int) ($actor['active'] ?? 0) !== 1) {
            throw new AuthorizationException(t('validation.team_manager_only'));
        }
    }

    public static function passwordIsStrong(string $password): bool
    {
        return mb_strlen($password) >= 8
            && mb_strlen($password) <= 120
            && preg_match('/\p{L}/u', $password) === 1
            && preg_match('/\d/', $password) === 1;
    }

    public static function normalizeToken(string $raw): ?string
    {
        $raw = trim($raw);

        if ($raw === '') {
            return null;
        }

        if (str_contains($raw, 'token=')) {
            $query = parse_url($raw, PHP_URL_QUERY);
            if (is_string($query)) {
                parse_str($query, $params);
                $raw = is_string($params['token'] ?? null) ? $params['token'] : '';
            }
        }

        $raw = trim($raw, " \t\n\r\0\x0B\"'");

        return preg_match('/^[a-f0-9]{64}$/i', $raw) === 1 ? strtolower($raw) : null;
    }
}
