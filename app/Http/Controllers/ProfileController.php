<?php

declare(strict_types=1);

namespace ObraClara\Http\Controllers;

use ObraClara\Services\AccountService;
use ObraClara\Support\Auth;
use ObraClara\Support\Flash;
use ObraClara\Support\ValidationException;
use ObraClara\Support\View;

final class ProfileController extends Controller
{
    /** @param array<string, string> $params */
    public function index(array $params = []): void
    {
        $user = Auth::requireUser();

        View::page('profile/index', [
            'title'  => t('profile.title'),
            'nav'    => 'profile',
            'user'   => $user,
            'errors' => [],
            'passwordErrors' => [],
        ]);
    }

    /** @param array<string, string> $params */
    public function update(array $params = []): void
    {
        $user = Auth::requireUser();

        try {
            $user = AccountService::updateProfile($user, $_POST);
            Flash::success(t('profile.saved'));
            $this->redirect('/perfil');
        } catch (ValidationException $exception) {
            $this->render($user, $exception->errors(), [], 422);
        }
    }

    /** @param array<string, string> $params */
    public function password(array $params = []): void
    {
        $user = Auth::requireUser();

        try {
            AccountService::changePassword($user, $_POST);
            Flash::success(t('profile.password_changed'));
            $this->redirect('/perfil');
        } catch (ValidationException $exception) {
            $this->render($user, [], $exception->errors(), 422);
        }
    }

    /** @param array<string, string> $params */
    public function locale(array $params = []): void
    {
        $user = Auth::requireUser();

        try {
            AccountService::setLocale($user, post_string('locale'));
            Flash::success(t('profile.language_saved'));
        } catch (ValidationException $exception) {
            Flash::error($exception->errors()['locale'] ?? t('validation.locale'));
        }

        $redirect = post_string('redirect');
        if ($redirect === '' || !str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            $redirect = '/perfil';
        }

        $this->redirect($redirect);
    }

    /** @param array<string, string> $params */
    public function completeTour(array $params = []): void
    {
        $user = Auth::requireUser();
        AccountService::completeTour((int) $user['id']);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'completed' => true], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * @param array<string, mixed> $user
     * @param array<string, string> $errors
     * @param array<string, string> $passwordErrors
     */
    private function render(array $user, array $errors, array $passwordErrors, int $status): void
    {
        View::page('profile/index', [
            'title'          => t('profile.title'),
            'nav'            => 'profile',
            'user'           => $user,
            'errors'         => $errors,
            'passwordErrors' => $passwordErrors,
        ], $status);
    }
}
