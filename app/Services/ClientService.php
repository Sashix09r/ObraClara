<?php

declare(strict_types=1);

namespace ObraClara\Services;

use ObraClara\Repositories\ClientRepository;
use ObraClara\Support\BusinessRuleException;
use ObraClara\Support\NotFoundException;
use ObraClara\Support\ValidationException;

final class ClientService
{
    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function validate(array $input): array
    {
        $errors = [];

        $name = trim((string) ($input['name'] ?? ''));
        $contact = trim((string) ($input['contact'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $notes = trim((string) ($input['notes'] ?? ''));

        if ($name === '') {
            $errors['name'] = 'Indica o nome do cliente.';
        } elseif (mb_strlen($name) < 2) {
            $errors['name'] = 'O nome deve ter pelo menos 2 caracteres.';
        } elseif (mb_strlen($name) > 120) {
            $errors['name'] = 'O nome não pode exceder 120 caracteres.';
        }

        if ($contact !== '' && mb_strlen($contact) > 60) {
            $errors['contact'] = 'O contacto não pode exceder 60 caracteres.';
        }

        if ($email !== '') {
            if (mb_strlen($email) > 150) {
                $errors['email'] = 'O email não pode exceder 150 caracteres.';
            } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $errors['email'] = 'Indica um email válido (ex.: nome@exemplo.pt).';
            }
        }

        if (mb_strlen($notes) > 1500) {
            $errors['notes'] = 'As notas não podem exceder 1500 caracteres.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'name'    => $name,
            'contact' => $contact === '' ? null : $contact,
            'email'   => $email === '' ? null : $email,
            'notes'   => $notes === '' ? null : $notes,
        ];
    }

    /** @param array<string, mixed> $input */
    public static function save(?int $id, array $input): int
    {
        $data = self::validate($input);
        $data['updated_at'] = now();

        if ($id === null) {
            $data['created_at'] = now();

            return ClientRepository::create($data);
        }

        ClientRepository::find($id) ?? throw new NotFoundException('O cliente indicado não existe.');
        ClientRepository::update($id, $data);

        return $id;
    }

    public static function archive(int $id): void
    {
        $client = ClientRepository::find($id) ?? throw new NotFoundException('O cliente indicado não existe.');

        if ((int) $client['active'] === 0) {
            throw new BusinessRuleException('Este cliente já está arquivado.');
        }

        $activeProjects = ClientRepository::activeProjectCount($id);

        if ($activeProjects > 0) {
            throw new BusinessRuleException(
                'Não é possível arquivar: o cliente tem ' . $activeProjects
                . ' obra(s) ativa(s). Arquiva ou conclui primeiro essas obras.'
            );
        }

        ClientRepository::setActive($id, false);
    }

    public static function reactivate(int $id): void
    {
        $client = ClientRepository::find($id) ?? throw new NotFoundException('O cliente indicado não existe.');

        if ((int) $client['active'] === 1) {
            throw new BusinessRuleException('Este cliente já está ativo.');
        }

        ClientRepository::setActive($id, true);
    }
}
