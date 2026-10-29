<?php

declare(strict_types=1);

namespace ObraClara\Http\Controllers;

use ObraClara\Repositories\ClientRepository;
use ObraClara\Repositories\EventRepository;
use ObraClara\Repositories\ProjectRepository;
use ObraClara\Services\ProjectPdfService;
use ObraClara\Services\ProjectService;
use ObraClara\Support\Auth;
use ObraClara\Support\BusinessRuleException;
use ObraClara\Support\Flash;
use ObraClara\Support\NotFoundException;
use ObraClara\Support\ValidationException;
use ObraClara\Support\View;

final class ProjectController extends Controller
{
    /** @param array<string, string> $params */
    public function index(array $params = []): void
    {
        $user = Auth::requireUser();
        $query = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
        $status = isset($_GET['estado']) && is_string($_GET['estado']) ? trim($_GET['estado']) : '';

        View::page('projects/index', [
            'title'    => t('projects.title'),
            'nav'      => 'projects',
            'user'     => $user,
            'projects' => ProjectRepository::search($query, $status),
            'query'    => $query,
            'status'   => $status,
        ]);
    }

    /** @param array<string, string> $params */
    public function createForm(array $params = []): void
    {
        $user = Auth::requireManager();

        if (ClientRepository::allActive() === []) {
            Flash::error(t('flash.project_create_client'));
            $this->redirect('/clientes/criar');
        }

        $this->renderForm(200, 'create', [
            'id'             => null,
            'client_id'      => isset($_GET['cliente']) ? (int) $_GET['cliente'] : '',
            'title'          => '',
            'description'    => '',
            'base_budget'    => '',
            'start_date'     => '',
            'status'         => 'planeada',
        ], [], '/obras/criar', t('projects.new_title'), $user);
    }

    /** @param array<string, string> $params */
    public function create(array $params = []): void
    {
        $user = Auth::requireManager();

        try {
            $id = ProjectService::save(null, $_POST, (int) $user['id']);
        } catch (ValidationException $exception) {
            $this->renderForm(422, 'create', $this->formData($_POST), $exception->errors(), '/obras/criar', t('projects.new_title'), $user);

            return;
        }

        Flash::success(t('flash.project_created'));
        $this->redirect('/obras/' . $id);
    }

    /** @param array<string, string> $params */
    public function show(array $params = []): void
    {
        $user = Auth::requireUser();
        $id = $this->id($params);

        $project = ProjectRepository::find($id);
        $changes = \ObraClara\Repositories\ChangeRepository::forProject($id);

        $events = EventRepository::forProject($id);
        $eventsByChange = [];
        foreach ($events as $event) {
            $eventsByChange[(int) $event['change_id']][] = $event;
        }

        View::page('projects/show', [
            'title'          => $project['title'],
            'nav'            => 'projects',
            'user'           => $user,
            'project'        => $project,
            'changes'        => $changes,
            'eventsByChange' => $eventsByChange,
        ]);
    }

    /** @param array<string, string> $params */
    public function pdf(array $params = []): void
    {
        Auth::requireUser();
        $id = $this->id($params);
        $project = ProjectRepository::find($id);
        $changes = \ObraClara\Repositories\ChangeRepository::forProject($id);
        $pdf = ProjectPdfService::generate($project, $changes);
        $filename = 'ObraClara-obra-' . $id . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: private, no-store, max-age=0');
        echo $pdf;
        exit;
    }

    /** @param array<string, string> $params */
    public function editForm(array $params = []): void
    {
        $user = Auth::requireManager();
        $id = $this->id($params);
        $project = ProjectRepository::find($id);

        if ($project['status'] === 'arquivada') {
            Flash::error(t('flash.project_needs_reactivate'));
            $this->redirect('/obras/' . $id);
        }

        $this->renderForm(200, 'edit', [
            'id'          => $id,
            'client_id'   => $project['client_id'],
            'title'       => $project['title'],
            'description' => $project['description'] ?? '',
            'base_budget' => \ObraClara\Support\Money::toInput((int) $project['base_budget_cents']),
            'start_date'  => $project['start_date'] ?? '',
            'status'      => $project['status'],
        ], [], '/obras/' . $id . '/editar', t('projects.edit_title'), $user);
    }

    /** @param array<string, string> $params */
    public function edit(array $params = []): void
    {
        $user = Auth::requireManager();
        $id = $this->id($params);
        ProjectRepository::find($id);

        try {
            ProjectService::save($id, $_POST, (int) $user['id']);
        } catch (ValidationException $exception) {
            $this->renderForm(422, 'edit', $this->formData($_POST) + ['id' => $id], $exception->errors(), '/obras/' . $id . '/editar', t('projects.edit_title'), $user);

            return;
        }

        Flash::success(t('flash.project_updated'));
        $this->redirect('/obras/' . $id);
    }

    /** @param array<string, string> $params */
    public function archive(array $params = []): void
    {
        Auth::requireManager();
        $id = $this->id($params);

        try {
            ProjectService::archive($id);
            Flash::success(t('flash.project_archived'));
        } catch (BusinessRuleException $exception) {
            Flash::error($exception->getMessage());
        }

        $this->redirect('/obras/' . $id);
    }

    /** @param array<string, string> $params */
    public function reactivate(array $params = []): void
    {
        Auth::requireManager();
        $id = $this->id($params);

        try {
            ProjectService::reactivate($id);
            Flash::success(t('flash.project_reactivated'));
        } catch (BusinessRuleException $exception) {
            Flash::error($exception->getMessage());
        }

        $this->redirect('/obras/' . $id);
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function formData(array $input): array
    {
        return [
            'id'          => null,
            'client_id'   => $input['client_id'] ?? '',
            'title'       => trim((string) ($input['title'] ?? '')),
            'description' => trim((string) ($input['description'] ?? '')),
            'base_budget' => trim((string) ($input['base_budget'] ?? '')),
            'start_date'  => trim((string) ($input['start_date'] ?? '')),
            'status'      => trim((string) ($input['status'] ?? 'planeada')),
        ];
    }

    /**
     * @param array<string, mixed> $project
     * @param array<string, string> $errors
     * @param array<string, mixed> $user
     */
    private function renderForm(
        int $status,
        string $mode,
        array $project,
        array $errors,
        string $formAction,
        string $title,
        array $user
    ): void {
        View::page('projects/form', [
            'title'    => $title,
            'nav'      => 'projects',
            'user'     => $user,
            'mode'     => $mode,
            'project'  => $project,
            'clients'  => ClientRepository::allActive(),
            'errors'   => $errors,
            'formAction' => $formAction,
        ], $status);
    }
}
