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
            $errors['name'] = t('validation.client_name');
        } elseif (mb_strlen($name) < 2) {
            $errors['name'] = t('validation.client_name_short');
        } elseif (mb_strlen($name) > 120) {
            $errors['name'] = t('validation.client_name_long');
        }

        if ($contact !== '' && mb_strlen($contact) > 60) {
            $errors['contact'] = t('validation.contact_long');
        }

        if ($email !== '') {
            if (mb_strlen($email) > 150) {
                $errors['email'] = t('validation.email_long');
            } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $errors['email'] = t('validation.email_invalid');
            }
        }

        if (mb_strlen($notes) > 1500) {
            $errors['notes'] = t('validation.notes_long');
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

        ClientRepository::find($id) ?? throw new NotFoundException(t('validation.client_not_found'));
        ClientRepository::update($id, $data);

        return $id;
    }

    public static function archive(int $id): void
    {
        $client = ClientRepository::find($id) ?? throw new NotFoundException(t('validation.client_not_found'));

        if ((int) $client['active'] === 0) {
            throw new BusinessRuleException(t('validation.client_already_archived'));
        }

        $activeProjects = ClientRepository::activeProjectCount($id);

        if ($activeProjects > 0) {
            throw new BusinessRuleException(t('validation.client_has_projects', ['count' => $activeProjects]));
        }

        ClientRepository::setActive($id, false);
    }

    public static function reactivate(int $id): void
    {
        $client = ClientRepository::find($id) ?? throw new NotFoundException(t('validation.client_not_found'));

        if ((int) $client['active'] === 1) {
            throw new BusinessRuleException(t('validation.client_already_active'));
        }

        ClientRepository::setActive($id, true);
    }
}
