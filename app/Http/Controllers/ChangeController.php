<?php

declare(strict_types=1);

namespace ObraClara\Http\Controllers;

use ObraClara\Repositories\ChangeRepository;
use ObraClara\Repositories\EventRepository;
use ObraClara\Repositories\ProjectRepository;
use ObraClara\Services\ChangeService;
use ObraClara\Support\Auth;
use ObraClara\Support\AuthorizationException;
use ObraClara\Support\BusinessRuleException;
use ObraClara\Support\Flash;
use ObraClara\Support\NotFoundException;
use ObraClara\Support\ValidationException;
use ObraClara\Support\View;

final class ChangeController extends Controller
{
    /** @param array<string, string> $params */
    public function createForm(array $params = []): void
    {
        $user = Auth::requireUser();
        $projectId = $this->id($params);
        $project = ProjectRepository::find($projectId);

        if ($project['status'] === 'arquivada') {
            Flash::error(t('flash.change_archived'));
            $this->redirect('/obras/' . $projectId);
        }

        View::page('changes/form', [
            'title'      => t('changes.new_title'),
            'nav'        => 'projects',
            'user'       => $user,
            'mode'       => 'create',
            'project'    => $project,
            'change'     => ['id' => null, 'title' => '', 'description' => '', 'amount' => ''],
            'errors'     => [],
            'formAction' => '/obras/' . $projectId . '/alteracoes/criar',
        ]);
    }

    /** @param array<string, string> $params */
    public function create(array $params = []): void
    {
        $user = Auth::requireUser();
        $projectId = $this->id($params);
        $project = ProjectRepository::find($projectId);
        $submit = ($_POST['action'] ?? '') === 'submit';

        try {
            $changeId = ChangeService::create($projectId, $_POST, $user, $submit);
        } catch (ValidationException $exception) {
            View::page('changes/form', [
                'title'      => t('changes.new_title'),
                'nav'        => 'projects',
                'user'       => $user,
                'mode'       => 'create',
                'project'    => $project,
                'change'     => [
                    'id'          => null,
                    'title'       => trim((string) ($_POST['title'] ?? '')),
                    'description' => trim((string) ($_POST['description'] ?? '')),
                    'amount'      => trim((string) ($_POST['amount'] ?? '')),
                ],
                'errors'     => $exception->errors(),
                'formAction' => '/obras/' . $projectId . '/alteracoes/criar',
            ], 422);

            return;
        } catch (BusinessRuleException $exception) {
            Flash::error($exception->getMessage());
            $this->redirect('/obras/' . $projectId);
        }

        Flash::success($submit ? t('flash.change_submitted') : t('flash.change_created'));
        // Voltar ao topo da obra: a mensagem e o orçamento têm de ficar visíveis.
        $this->redirect('/obras/' . $projectId);
    }

    /** @param array<string, string> $params */
    public function editForm(array $params = []): void
    {
        $user = Auth::requireUser();
        $change = ChangeRepository::find($this->id($params));

        try {
            ChangeService::ensureCanEdit($change, $user);
        } catch (BusinessRuleException $exception) {
            Flash::error($exception->getMessage());
            $this->redirect('/obras/' . (int) $change['project_id']);
        }

        View::page('changes/form', [
            'title'      => t('changes.edit_title'),
            'nav'        => 'projects',
            'user'       => $user,
            'mode'       => 'edit',
            'project'    => ProjectRepository::find((int) $change['project_id']),
            'change'     => [
                'id'          => (int) $change['id'],
                'title'       => $change['title'],
                'description' => $change['description'] ?? '',
                'amount'      => \ObraClara\Support\Money::toInput((int) $change['amount_cents']),
            ],
            'errors'     => [],
            'formAction' => '/alteracoes/' . (int) $change['id'] . '/editar',
        ]);
    }

    /** @param array<string, string> $params */
    public function edit(array $params = []): void
    {
        $user = Auth::requireUser();
        $change = ChangeRepository::find($this->id($params));
        $submit = ($_POST['action'] ?? '') === 'submit';

        try {
            ChangeService::update((int) $change['id'], $_POST, $user, $submit);
        } catch (ValidationException $exception) {
            View::page('changes/form', [
                'title'      => t('changes.edit_title'),
                'nav'        => 'projects',
                'user'       => $user,
                'mode'       => 'edit',
                'project'    => ProjectRepository::find((int) $change['project_id']),
                'change'     => [
                    'id'          => (int) $change['id'],
                    'title'       => trim((string) ($_POST['title'] ?? '')),
                    'description' => trim((string) ($_POST['description'] ?? '')),
                    'amount'      => trim((string) ($_POST['amount'] ?? '')),
                ],
                'errors'     => $exception->errors(),
                'formAction' => '/alteracoes/' . (int) $change['id'] . '/editar',
            ], 422);

            return;
        } catch (BusinessRuleException | AuthorizationException $exception) {
            Flash::error($exception->getMessage());
            $this->redirect('/obras/' . (int) $change['project_id']);
        }

        Flash::success($submit ? t('flash.change_updated_submitted') : t('flash.change_updated'));
        $this->redirect('/obras/' . (int) $change['project_id']);
    }

    /** @param array<string, string> $params */
    public function submit(array $params = []): void
    {
        $user = Auth::requireUser();
        $change = ChangeRepository::find($this->id($params));

        try {
            ChangeService::submit((int) $change['id'], $user);
            Flash::success(t('flash.change_submit_success'));
        } catch (BusinessRuleException | AuthorizationException $exception) {
            Flash::error($exception->getMessage());
        }

        $this->redirect('/obras/' . (int) $change['project_id']);
    }

    /** @param array<string, string> $params */
    public function decideForm(array $params = []): void
    {
        $user = Auth::requireManager();
        $change = ChangeRepository::find($this->id($params));
        $project = ProjectRepository::find((int) $change['project_id']);

        if ($change['status'] !== 'pendente') {
            Flash::error(t('flash.change_not_pending'));
            $this->redirect('/obras/' . (int) $project['id']);
        }

        View::page('changes/decide', [
            'title'   => t('changes.decide.title'),
            'nav'     => 'projects',
            'user'    => $user,
            'change'  => $change,
            'project' => $project,
            'history' => EventRepository::forChange((int) $change['id']),
            'errors'  => [],
            'decision' => 'aprovar',
            'note'    => '',
        ]);
    }

    /** @param array<string, string> $params */
    public function decide(array $params = []): void
    {
        $user = Auth::requireManager();
        $change = ChangeRepository::find($this->id($params));
        $project = ProjectRepository::find((int) $change['project_id']);

        $decision = post_string('decision');
        $note = post_string('note');

        try {
            $newStatus = ChangeService::decide((int) $change['id'], $user, $decision, $note);
        } catch (ValidationException $exception) {
            View::page('changes/decide', [
                'title'    => t('changes.decide.title'),
                'nav'      => 'projects',
                'user'     => $user,
                'change'   => $change,
                'project'  => $project,
                'history'  => EventRepository::forChange((int) $change['id']),
                'errors'   => $exception->errors(),
                'decision' => $decision,
                'note'     => $note,
            ], 422);

            return;
        } catch (BusinessRuleException | AuthorizationException $exception) {
            Flash::error($exception->getMessage());
            $this->redirect('/obras/' . (int) $project['id']);
        }

        Flash::success($newStatus === 'aprovada' ? t('flash.change_approved') : t('flash.change_rejected'));
        // Sem âncora: o gestor tem de ver logo o novo orçamento e a confirmação.
        $this->redirect('/obras/' . (int) $project['id']);
    }
}
