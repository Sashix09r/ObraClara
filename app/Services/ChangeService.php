<?php

declare(strict_types=1);

namespace ObraClara\Services;

use InvalidArgumentException;
use ObraClara\Repositories\ChangeRepository;
use ObraClara\Repositories\EventRepository;
use ObraClara\Repositories\ProjectRepository;
use ObraClara\Support\AuthorizationException;
use ObraClara\Support\BusinessRuleException;
use ObraClara\Support\Database;
use ObraClara\Support\Money;
use ObraClara\Support\ValidationException;
use Throwable;

/**
 * Regras centrais da aplicação:
 * - o colaborador cria/edita rascunhos e submete, mas NUNCA decide;
 * - só o gestor decide, apenas a partir de «pendente»;
 * - cada transição é registada no histórico na mesma transação.
 */
final class ChangeService
{
    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function validate(array $input): array
    {
        $errors = [];

        $title = trim((string) ($input['title'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $amountRaw = trim((string) ($input['amount'] ?? ''));

        if ($title === '') {
            $errors['title'] = t('validation.change_title');
        } elseif (mb_strlen($title) < 3) {
            $errors['title'] = t('validation.change_title_short');
        } elseif (mb_strlen($title) > 150) {
            $errors['title'] = t('validation.change_title_long');
        }

        if (mb_strlen($description) > 2000) {
            $errors['description'] = t('validation.description_long');
        }

        $amountCents = 0;
        try {
            $amountCents = Money::parse($amountRaw);
        } catch (InvalidArgumentException $exception) {
            $errors['amount'] = $exception->getMessage();
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'title'        => $title,
            'description'  => $description === '' ? null : $description,
            'amount_cents' => $amountCents,
        ];
    }

    /**
     * Cria a alteração em rascunho (e, opcionalmente, submete-a de imediato).
     *
     * @param array<string, mixed> $input
     * @param array<string, mixed> $actor
     */
    public static function create(int $projectId, array $input, array $actor, bool $submit = false): int
    {
        $project = ProjectRepository::find($projectId);
        self::ensureProjectEditable($project);
        $data = self::validate($input);

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $changeId = ChangeRepository::create([
                'project_id'   => $projectId,
                'title'        => $data['title'],
                'description'  => $data['description'],
                'amount_cents' => $data['amount_cents'],
                'status'       => 'rascunho',
                'created_by'   => (int) $actor['id'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            EventRepository::log($changeId, (int) $actor['id'], 'criada', null, 'rascunho');

            if ($submit) {
                ChangeRepository::markSubmitted($changeId);
                EventRepository::log($changeId, (int) $actor['id'], 'submetida', 'rascunho', 'pendente');
            }

            $pdo->commit();

            return $changeId;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed> $actor
     */
    public static function update(int $changeId, array $input, array $actor, bool $submit = false): void
    {
        $change = ChangeRepository::find($changeId);
        self::ensureCanEdit($change, $actor);
        $data = self::validate($input);

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            ChangeRepository::updateDraft($changeId, [
                'title'        => $data['title'],
                'description'  => $data['description'],
                'amount_cents' => $data['amount_cents'],
                'updated_at'   => now(),
            ]);

            EventRepository::log($changeId, (int) $actor['id'], 'editada', 'rascunho', 'rascunho');

            if ($submit) {
                ChangeRepository::markSubmitted($changeId);
                EventRepository::log($changeId, (int) $actor['id'], 'submetida', 'rascunho', 'pendente');
            }

            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    /** @param array<string, mixed> $actor */
    public static function submit(int $changeId, array $actor): void
    {
        $change = ChangeRepository::find($changeId);

        if ($change['status'] !== 'rascunho') {
            throw new BusinessRuleException(t('validation.change_not_draft'));
        }

        self::ensureCanEdit($change, $actor);
        self::ensureProjectEditable(ProjectRepository::find((int) $change['project_id']));

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            ChangeRepository::markSubmitted($changeId);
            EventRepository::log($changeId, (int) $actor['id'], 'submetida', 'rascunho', 'pendente');
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    /**
     * Decisão exclusiva do gestor. A permissão é verificada aqui, no backend,
     * e não apenas na interface.
     *
     * @param array<string, mixed> $actor
     */
    public static function decide(int $changeId, array $actor, string $decision, ?string $note = null): string
    {
        if (($actor['role'] ?? '') !== 'gestor') {
            throw new AuthorizationException(t('validation.manager_only'));
        }

        $decision = trim($decision);
        $note = $note === null ? null : trim($note);

        if (!in_array($decision, ['aprovar', 'rejeitar'], true)) {
            throw new ValidationException(['decision' => t('validation.decision_choose')]);
        }

        if ($note !== null && mb_strlen($note) > 800) {
            throw new ValidationException(['note' => t('validation.note_long')]);
        }

        if ($decision === 'rejeitar' && ($note === null || mb_strlen($note) < 3)) {
            throw new ValidationException(['note' => t('validation.reject_note')]);
        }

        $change = ChangeRepository::find($changeId);

        if ($change['status'] !== 'pendente') {
            throw new BusinessRuleException(t('validation.change_not_pending'));
        }

        $project = ProjectRepository::find((int) $change['project_id']);
        self::ensureProjectEditable($project);

        $newStatus = $decision === 'aprovar' ? 'aprovada' : 'rejeitada';
        $action = $decision === 'aprovar' ? 'aprovada' : 'rejeitada';

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            ChangeRepository::markDecided($changeId, $newStatus, (int) $actor['id'], $note === '' ? null : $note);
            EventRepository::log($changeId, (int) $actor['id'], $action, 'pendente', $newStatus, $note === '' ? null : $note);

            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }

        return $newStatus;
    }

    /**
     * @param array<string, mixed> $change
     * @param array<string, mixed> $actor
     */
    public static function ensureCanEdit(array $change, array $actor): void
    {
        if ($change['status'] !== 'rascunho') {
            throw new BusinessRuleException(t('flash.change_edit_denied'));
        }

        $isAuthor = (int) $change['created_by'] === (int) ($actor['id'] ?? 0);
        $isManager = ($actor['role'] ?? '') === 'gestor';

        if (!$isAuthor && !$isManager) {
            throw new AuthorizationException(t('validation.not_author'));
        }
    }

    /** @param array<string, mixed> $project */
    private static function ensureProjectEditable(array $project): void
    {
        if ($project['status'] === 'arquivada') {
            throw new BusinessRuleException(t('validation.project_archived'));
        }
    }

    public static function find(int $id): array
    {
        return ChangeRepository::find($id);
    }
}
