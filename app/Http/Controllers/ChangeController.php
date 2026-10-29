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
            Flash::error('A obra está arquivada. Reativa-a antes de criar alterações.');
            $this->redirect('/obras/' . $projectId);
        }

        View::page('changes/form', [
            'title'      => 'Nova alteração',
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
                'title'      => 'Nova alteração',
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

        Flash::success(
            $submit
                ? 'Alteração submetida para decisão do gestor.'
                : 'Rascunho guardado. Podes submetê-lo quando estiver pronto.'
        );
        $this->redirect('/obras/' . $projectId . '#alteracao-' . $changeId);
    }

    /** @param array<string, string> $params */
    public function editForm(array $params = []): void
    {
        $user = Auth::requireUser();
        $change = ChangeRepository::find($this->id($params));
        ChangeService::ensureCanEdit($change, $user);

        View::page('changes/form', [
            'title'      => 'Editar rascunho',
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
                'title'      => 'Editar rascunho',
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

        Flash::success($submit ? 'Alteração atualizada e submetida para decisão.' : 'Rascunho atualizado.');
        $this->redirect('/obras/' . (int) $change['project_id'] . '#alteracao-' . (int) $change['id']);
    }

    /** @param array<string, string> $params */
    public function submit(array $params = []): void
    {
        $user = Auth::requireUser();
        $change = ChangeRepository::find($this->id($params));

        try {
            ChangeService::submit((int) $change['id'], $user);
            Flash::success('Alteração submetida. O gestor vai agora analisá-la.');
        } catch (BusinessRuleException | AuthorizationException $exception) {
            Flash::error($exception->getMessage());
        }

        $this->redirect('/obras/' . (int) $change['project_id'] . '#alteracao-' . (int) $change['id']);
    }

    /** @param array<string, string> $params */
    public function decideForm(array $params = []): void
    {
        $user = Auth::requireManager();
        $change = ChangeRepository::find($this->id($params));
        $project = ProjectRepository::find((int) $change['project_id']);

        if ($change['status'] !== 'pendente') {
            Flash::error('Esta alteração já não está pendente: não pode ser decidida de novo.');
            $this->redirect('/obras/' . (int) $project['id']);
        }

        View::page('changes/decide', [
            'title'   => 'Decidir alteração',
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
                'title'    => 'Decidir alteração',
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

        Flash::success(
            $newStatus === 'aprovada'
                ? 'Alteração aprovada. O orçamento atualizado da obra foi recalculado.'
                : 'Alteração rejeitada. O orçamento da obra não foi alterado.'
        );
        $this->redirect('/obras/' . (int) $project['id'] . '#alteracao-' . (int) $change['id']);
    }
}
