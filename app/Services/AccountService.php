<?php

declare(strict_types=1);

namespace ObraClara\Services;

use ObraClara\Repositories\AdminEventRepository;
use ObraClara\Repositories\UserRepository;
use ObraClara\Support\Csrf;
use ObraClara\Support\Database;
use ObraClara\Support\Lang;
use ObraClara\Support\ValidationException;
use Throwable;

final class AccountService
{
    /**
     * @param array<string, mixed> $user
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function updateProfile(array $user, array $input): array
    {
        $errors = [];
        $name = trim((string) ($input['name'] ?? ''));
        $email = mb_strtolower(trim((string) ($input['email'] ?? '')));

        if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
            $errors['name'] = t('profile.name_length');
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || mb_strlen($email) > 150) {
            $errors['email'] = t('profile.email_invalid');
        } else {
            $existing = UserRepository::findByEmail($email);
            if ($existing !== null && (int) $existing['id'] !== (int) $user['id']) {
                $errors['email'] = t('profile.email_taken');
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            UserRepository::updateProfile((int) $user['id'], $name, $email, (string) ($user['locale'] ?? 'pt'));
            AdminEventRepository::log((int) $user['id'], 'perfil_alterado', 'user', (int) $user['id'], 'Nome/email');
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }

        return UserRepository::find((int) $user['id']) ?? $user;
    }

    /**
     * @param array<string, mixed> $user
     * @param array<string, mixed> $input
     */
    public static function changePassword(array $user, array $input): void
    {
        $errors = [];
        $current = (string) ($input['current_password'] ?? '');
        $new = (string) ($input['password'] ?? '');
        $confirm = (string) ($input['password_confirm'] ?? '');

        if ($current === '') {
            $errors['current_password'] = t('profile.current_required');
        } elseif (!password_verify($current, (string) $user['password_hash'])) {
            $errors['current_password'] = t('profile.wrong_password');
        }

        if (!TeamService::passwordIsStrong($new)) {
            $errors['password'] = t('profile.password_weak');
        } elseif (password_verify($new, (string) $user['password_hash'])) {
            $errors['password'] = t('profile.same_password');
        }

        if ($new !== $confirm) {
            $errors['password_confirm'] = t('profile.password_mismatch');
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            UserRepository::updatePassword((int) $user['id'], password_hash($new, PASSWORD_DEFAULT));
            AdminEventRepository::log((int) $user['id'], 'palavra_passe_alterada', 'user', (int) $user['id']);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }

        // Sessão renovada após uma alteração sensível. Em CLI/testes não há
        // sessão ativa; nesse caso apenas se roda o token de sessão em memória.
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        Csrf::rotate();
    }

    /** @param array<string, mixed> $user */
    public static function setLocale(array $user, string $locale): void
    {
        if (!in_array($locale, Lang::LOCALES, true)) {
            throw new ValidationException(['locale' => t('validation.locale')]);
        }

        UserRepository::setLocale((int) $user['id'], $locale);
        Lang::setLocale($locale);
        AdminEventRepository::log((int) $user['id'], 'idioma_alterado', 'user', (int) $user['id'], $locale);
    }

    public static function completeTour(int $userId): void
    {
        UserRepository::markTourCompleted($userId);
    }
}
