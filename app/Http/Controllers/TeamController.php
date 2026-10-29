<?php

declare(strict_types=1);

namespace ObraClara\Http\Controllers;

use ObraClara\Repositories\AdminEventRepository;
use ObraClara\Repositories\InviteRepository;
use ObraClara\Repositories\UserRepository;
use ObraClara\Services\TeamService;
use ObraClara\Support\Auth;
use ObraClara\Support\Flash;
use ObraClara\Support\NotFoundException;
use ObraClara\Support\ValidationException;
use ObraClara\Support\View;

final class TeamController extends Controller
{
    /** @param array<string, string> $params */
    public function index(array $params = []): void
    {
        $user = Auth::requireManager(t('validation.team_manager_only'));
        $query = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';

        $createdInvite = $_SESSION['created_invite'] ?? null;
        unset($_SESSION['created_invite']);

        $inviteErrors = $_SESSION['invite_errors'] ?? [];
        $inviteOld = $_SESSION['invite_old'] ?? ['email' => '', 'role' => 'colaborador', 'expiry' => 7];
        unset($_SESSION['invite_errors'], $_SESSION['invite_old']);

        View::page('team/index', [
            'title'        => t('team.title'),
            'nav'          => 'team',
            'user'         => $user,
            'users'        => UserRepository::search($query),
            'invites'      => InviteRepository::recent(40),
            'pendingCount' => InviteRepository::countPending(),
            'query'        => $query,
            'createdInvite' => is_array($createdInvite) ? $createdInvite : null,
            'inviteErrors' => is_array($inviteErrors) ? $inviteErrors : [],
            'inviteOld'    => is_array($inviteOld) ? $inviteOld : [],
        ]);
    }

    /** @param array<string, string> $params */
    public function createInvite(array $params = []): void
    {
        $user = Auth::requireManager(t('validation.team_manager_only'));

        try {
            $invite = TeamService::createInvite($user, $_POST);
        } catch (ValidationException $exception) {
            $_SESSION['invite_errors'] = $exception->errors();
            $_SESSION['invite_old'] = [
                'email' => post_string('email'),
                'role' => post_string('role'),
                'expiry' => (int) ($_POST['expiry'] ?? 7),
            ];
            $this->redirect('/equipa?tab=convites');
        }

        $_SESSION['created_invite'] = $invite;
        Flash::success(t('team.invite_created', ['email' => $invite['email']]));
        $this->redirect('/equipa?tab=convites#convite-criado');
    }

    /** @param array<string, string> $params */
    public function revokeInvite(array $params = []): void
    {
        $user = Auth::requireManager(t('validation.team_manager_only'));

        try {
            TeamService::revokeInvite($user, $this->id($params));
            Flash::success(t('team.invite_revoked_flash'));
        } catch (ValidationException $exception) {
            Flash::error($exception->errors()['invite'] ?? t('validation.invite_invalid'));
        }

        $this->redirect('/equipa?tab=convites');
    }

    /** @param array<string, string> $params */
    public function showUser(array $params = []): void
    {
        $actor = Auth::requireManager(t('validation.team_manager_only'));
        $id = $this->id($params);
        $target = UserRepository::find($id);

        if ($target === null) {
            throw new NotFoundException(t('validation.user_not_found'));
        }

        View::page('team/user', [
            'title' => $target['name'],
            'nav'   => 'team',
            'user'  => $actor,
            'target' => $target,
            'events' => AdminEventRepository::forTarget('user', $id, 20),
        ]);
    }

    /** @param array<string, string> $params */
    public function setRole(array $params = []): void
    {
        $actor = Auth::requireManager(t('validation.team_manager_only'));
        $id = $this->id($params);

        try {
            TeamService::setRole($actor, $id, post_string('role'));
            Flash::success(t('team.role_changed'));
        } catch (ValidationException | \ObraClara\Support\AuthorizationException $exception) {
            Flash::error($exception->getMessage());
        }

        $this->redirect('/equipa/utilizadores/' . $id);
    }

    /** @param array<string, string> $params */
    public function setActive(array $params = []): void
    {
        $actor = Auth::requireManager(t('validation.team_manager_only'));
        $id = $this->id($params);
        $active = post_string('active') === '1';

        try {
            TeamService::setActive($actor, $id, $active);
            Flash::success($active ? t('team.account_activated') : t('team.account_deactivated'));
        } catch (ValidationException | \ObraClara\Support\AuthorizationException $exception) {
            Flash::error($exception->getMessage());
        }

        $this->redirect('/equipa/utilizadores/' . $id);
    }
}
