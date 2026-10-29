<?php

declare(strict_types=1);

namespace ObraClara\Services;

use InvalidArgumentException;
use ObraClara\Repositories\ClientRepository;
use ObraClara\Repositories\ProjectRepository;
use ObraClara\Support\BusinessRuleException;
use ObraClara\Support\Money;
use ObraClara\Support\ValidationException;

final class ProjectService
{
    /** Estados que podem ser escolhidos no formulário (arquivo tem ação própria). */
    public const EDITABLE_STATUSES = ['planeada', 'em_curso', 'concluida'];

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function validate(array $input): array
    {
        $errors = [];

        $clientId = filter_var($input['client_id'] ?? null, FILTER_VALIDATE_INT);
        $title = trim((string) ($input['title'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $budgetRaw = trim((string) ($input['base_budget'] ?? ''));
        $startDate = trim((string) ($input['start_date'] ?? ''));
        $status = trim((string) ($input['status'] ?? 'planeada'));

        if ($clientId === false || $clientId === null || $clientId <= 0) {
            $errors['client_id'] = t('validation.project_client');
        } else {
            $client = ClientRepository::find((int) $clientId);
            if ($client === null) {
                $errors['client_id'] = t('validation.project_client_missing');
            } elseif ((int) $client['active'] !== 1) {
                $errors['client_id'] = t('validation.project_client_archived');
            }
        }

        if ($title === '') {
            $errors['title'] = t('validation.project_title');
        } elseif (mb_strlen($title) > 150) {
            $errors['title'] = t('validation.project_title_long');
        }

        if (mb_strlen($description) > 2000) {
            $errors['description'] = t('validation.description_long');
        }

        $budgetCents = 0;
        try {
            $budgetCents = Money::parse($budgetRaw, true);
        } catch (InvalidArgumentException $exception) {
            $errors['base_budget'] = $exception->getMessage();
        }

        if ($startDate !== '') {
            $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $startDate);
            if ($parsed === false || $parsed->format('Y-m-d') !== $startDate) {
                $errors['start_date'] = t('validation.date_invalid');
            }
        }

        if (!in_array($status, self::EDITABLE_STATUSES, true)) {
            $status = 'planeada';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'client_id'         => (int) $clientId,
            'title'             => $title,
            'description'       => $description === '' ? null : $description,
            'base_budget_cents' => $budgetCents,
            'start_date'        => $startDate === '' ? null : $startDate,
            'status'            => $status,
        ];
    }

    /** @param array<string, mixed> $input */
    public static function save(?int $id, array $input, int $actorId): int
    {
        $data = self::validate($input);
        $data['updated_at'] = now();

        if ($id === null) {
            $data['created_by'] = $actorId;
            $data['created_at'] = now();

            return ProjectRepository::create($data);
        }

        ProjectRepository::find($id);
        ProjectRepository::update($id, $data);

        return $id;
    }

    public static function archive(int $id): void
    {
        $project = ProjectRepository::find($id);

        if ($project['status'] === 'arquivada') {
            throw new BusinessRuleException(t('validation.project_already_archived'));
        }

        $pending = ProjectRepository::pendingChangeCount($id);

        if ($pending > 0) {
            throw new BusinessRuleException(t('validation.project_pending', ['count' => $pending]));
        }

        ProjectRepository::setStatus($id, 'arquivada');
    }

    public static function reactivate(int $id): void
    {
        $project = ProjectRepository::find($id);

        if ($project['status'] !== 'arquivada') {
            throw new BusinessRuleException(t('validation.project_not_archived'));
        }

        ProjectRepository::setStatus($id, 'planeada');
    }

    /** @return array<string, mixed> */
    public static function find(int $id): array
    {
        return ProjectRepository::find($id);
    }
}
