<?php

declare(strict_types=1);

namespace ObraClara\Http\Controllers;

use ObraClara\Repositories\ClientRepository;
use ObraClara\Repositories\ProjectRepository;
use ObraClara\Services\ClientService;
use ObraClara\Support\Auth;
use ObraClara\Support\BusinessRuleException;
use ObraClara\Support\Flash;
use ObraClara\Support\NotFoundException;
use ObraClara\Support\ValidationException;
use ObraClara\Support\View;

final class ClientController extends Controller
{
    /** @param array<string, string> $params */
    public function index(array $params = []): void
    {
        $user = Auth::requireUser();
        $query = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
        $includeArchived = ($_GET['arquivados'] ?? '') === '1';

        View::page('clients/index', [
            'title'           => t('clients.title'),
            'nav'             => 'clients',
            'user'            => $user,
            'clients'         => ClientRepository::search($query, $includeArchived),
            'query'           => $query,
            'includeArchived' => $includeArchived,
        ]);
    }

    /** @param array<string, string> $params */
    public function createForm(array $params = []): void
    {
        $user = Auth::requireManager();

        View::page('clients/form', [
            'title'      => t('clients.new_title'),
            'nav'        => 'clients',
            'user'       => $user,
            'mode'       => 'create',
            'client'     => ['id' => null, 'name' => '', 'contact' => '', 'email' => '', 'notes' => '', 'active' => 1],
            'errors'     => [],
            'formAction' => '/clientes/criar',
        ]);
    }

    /** @param array<string, string> $params */
    public function create(array $params = []): void
    {
        $user = Auth::requireManager();

        try {
            $id = ClientService::save(null, $_POST);
        } catch (ValidationException $exception) {
            $this->renderForm(422, 'create', [
                'id'      => null,
                'name'    => post_string('name'),
                'contact' => post_string('contact'),
                'email'   => post_string('email'),
                'notes'   => post_string('notes'),
                'active'  => 1,
            ], $exception->errors(), '/clientes/criar', t('clients.new_title'), $user);

            return;
        }

        Flash::success(t('flash.client_created'));
        $this->redirect('/clientes/' . $id);
    }

    /** @param array<string, string> $params */
    public function show(array $params = []): void
    {
        $user = Auth::requireUser();
        $id = $this->id($params);
        $client = ClientRepository::find($id);

        if ($client === null) {
            throw new NotFoundException(t('validation.client_not_found'));
        }

        View::page('clients/show', [
            'title'    => $client['name'],
            'nav'      => 'clients',
            'user'     => $user,
            'client'   => $client,
            'projects' => ProjectRepository::forClient($id, true),
        ]);
    }

    /** @param array<string, string> $params */
    public function editForm(array $params = []): void
    {
        $user = Auth::requireManager();
        $id = $this->id($params);
        $client = ClientRepository::find($id);

        if ($client === null) {
            throw new NotFoundException(t('validation.client_not_found'));
        }

        $this->renderForm(200, 'edit', $client, [], '/clientes/' . $id . '/editar', t('clients.edit_title'), $user);
    }

    /** @param array<string, string> $params */
    public function edit(array $params = []): void
    {
        $user = Auth::requireManager();
        $id = $this->id($params);

        try {
            ClientService::save($id, $_POST);
        } catch (ValidationException $exception) {
            $this->renderForm(422, 'edit', [
                'id'      => $id,
                'name'    => post_string('name'),
                'contact' => post_string('contact'),
                'email'   => post_string('email'),
                'notes'   => post_string('notes'),
                'active'  => 1,
            ], $exception->errors(), '/clientes/' . $id . '/editar', t('clients.edit_title'), $user);

            return;
        }

        Flash::success(t('flash.client_updated'));
        $this->redirect('/clientes/' . $id);
    }

    /** @param array<string, string> $params */
    public function archive(array $params = []): void
    {
        Auth::requireManager();
        $id = $this->id($params);

        try {
            ClientService::archive($id);
            Flash::success(t('flash.client_archived'));
        } catch (BusinessRuleException $exception) {
            Flash::error($exception->getMessage());
        }

        $this->redirect('/clientes/' . $id);
    }

    /** @param array<string, string> $params */
    public function reactivate(array $params = []): void
    {
        Auth::requireManager();
        $id = $this->id($params);

        try {
            ClientService::reactivate($id);
            Flash::success(t('flash.client_reactivated'));
        } catch (BusinessRuleException $exception) {
            Flash::error($exception->getMessage());
        }

        $this->redirect('/clientes/' . $id);
    }

    /**
     * @param array<string, mixed> $client
     * @param array<string, string> $errors
     * @param array<string, mixed> $user
     */
    private function renderForm(
        int $status,
        string $mode,
        array $client,
        array $errors,
        string $formAction,
        string $title,
        array $user
    ): void {
        View::page('clients/form', [
            'title'      => $title,
            'nav'        => 'clients',
            'user'       => $user,
            'mode'       => $mode,
            'client'     => $client,
            'errors'     => $errors,
            'formAction' => $formAction,
        ], $status);
    }
}
