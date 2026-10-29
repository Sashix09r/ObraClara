<?php

declare(strict_types=1);

use ObraClara\Support\Money;

/**
 * @var array<string, mixed> $user
 * @var list<array<string, mixed>> $users
 * @var list<array<string, mixed>> $invites
 * @var int $pendingCount
 * @var string $query
 * @var array<string, mixed>|null $createdInvite
 * @var array<string, string> $inviteErrors
 * @var array<string, mixed> $inviteOld
 */
$tab = ($_GET['tab'] ?? '') === 'convites' ? 'convites' : 'utilizadores';
$now = time();
$inviteStatus = static function (array $invite) use ($now): string {
    if ($invite['used_at'] !== null) {
        return 'used';
    }
    if ($invite['revoked_at'] !== null) {
        return 'revoked';
    }
    if (strtotime((string) $invite['expires_at']) <= $now) {
        return 'expired';
    }

    return 'pending';
};
$badgeClass = static fn (string $status): string => match ($status) {
    'used' => 'badge--approved',
    'revoked' => 'badge--rejected',
    'expired' => 'badge--archived',
    default => 'badge--pending',
};
$statusLabel = static fn (string $status): string => match ($status) {
    'used' => t('team.invite_used'),
    'revoked' => t('team.invite_revoked'),
    'expired' => t('team.invite_expired'),
    default => t('team.invite_pending'),
};
?>
<section class="page-head">
    <div>
        <p class="eyebrow"><?= e(t('team.eyebrow')) ?></p>
        <h1><?= e(t('team.title')) ?></h1>
        <p class="text-muted"><?= e(t('team.subtitle')) ?></p>
    </div>
    <div class="page-head__actions">
        <button type="button" class="btn btn--ghost" data-tour-help data-tour-target="team">
            <?= icon('help') ?> <?= e(t('tour.page_tour')) ?>
        </button>
    </div>
</section>

<nav class="tabs" aria-label="<?= e(t('team.title')) ?>">
    <a class="tabs__link<?= $tab === 'utilizadores' ? ' is-active' : '' ?>" href="<?= e(url('/equipa')) ?>"
       <?= $tab === 'utilizadores' ? 'aria-current="page"' : '' ?>>
        <?= icon('team') ?> <?= e(t('team.users')) ?>
        <span class="count-pill"><?= e((string) count($users)) ?></span>
    </a>
    <a class="tabs__link<?= $tab === 'convites' ? ' is-active' : '' ?>" href="<?= e(url('/equipa?tab=convites')) ?>"
       <?= $tab === 'convites' ? 'aria-current="page"' : '' ?>>
        <?= icon('mail') ?> <?= e(t('team.invites')) ?>
        <span class="count-pill"><?= e((string) $pendingCount) ?></span>
    </a>
</nav>

<?php if ($tab === 'convites'): ?>
    <?php if ($createdInvite !== null): ?>
        <section class="card invite-created" id="convite-criado" data-tour-id="team-invite-created">
            <header class="card__head">
                <div>
                    <h2><?= e(t('team.invite_created', ['email' => (string) $createdInvite['email']])) ?></h2>
                    <p class="text-muted"><?= e(t('team.invite_created_help')) ?></p>
                </div>
                <span class="badge badge--pending"><?= e(t('team.invite_pending')) ?></span>
            </header>

            <?php $inviteLink = url('/registar?token=' . $createdInvite['token']); ?>
            <div class="field">
                <label for="invite-link"><?= e(t('team.invite_link')) ?></label>
                <div class="copy-field">
                    <input type="text" id="invite-link" value="<?= e($inviteLink) ?>" readonly class="mono">
                    <button type="button" class="btn btn--ghost" data-copy-target="invite-link" data-copy-label="<?= e(t('common.copied')) ?>">
                        <?= icon('link') ?> <?= e(t('common.copy')) ?>
                    </button>
                </div>
                <p class="field__help"><?= e(t('team.invite_expires', ['date' => format_datetime((string) $createdInvite['expires_at'])])) ?></p>
            </div>
        </section>
    <?php endif; ?>

    <div class="grid grid--2-1">
        <section class="card" data-tour-id="team-invites"
                 data-tour-title="<?= e(t('tour.team_invites_title')) ?>"
                 data-tour-text="<?= e(t('tour.team_invites_text')) ?>">
            <header class="card__head">
                <div>
                    <h2><?= e(t('team.pending')) ?></h2>
                    <p class="text-muted"><?= e(t('team.invite_token_hint')) ?></p>
                </div>
                <span class="count-pill"><?= e((string) $pendingCount) ?></span>
            </header>

            <?php if ($invites === []): ?>
                <div class="empty-state empty-state--sm">
                    <span class="empty-state__icon"><?= icon('mail') ?></span>
                    <h3><?= e(t('team.no_invites')) ?></h3>
                    <p><?= e(t('team.no_invites_hint')) ?></p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col"><?= e(t('team.col_email')) ?></th>
                                <th scope="col"><?= e(t('team.col_role')) ?></th>
                                <th scope="col"><?= e(t('team.col_expires')) ?></th>
                                <th scope="col"><?= e(t('team.col_state')) ?></th>
                                <th scope="col" class="text-right"><?= e(t('common.actions')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invites as $invite): ?>
                                <?php $status = $inviteStatus($invite); ?>
                                <tr>
                                    <td data-label="<?= e(t('team.col_email')) ?>">
                                        <strong><?= e($invite['email']) ?></strong>
                                        <span class="cell-sub"><?= e(t('team.invite_created_by', ['name' => (string) $invite['creator_name']])) ?></span>
                                    </td>
                                    <td data-label="<?= e(t('team.col_role')) ?>">
                                        <span class="badge <?= $invite['role'] === 'gestor' ? 'badge--role-manager' : 'badge--role-staff' ?>">
                                            <?= e($invite['role'] === 'gestor' ? t('common.manager') : t('common.staff')) ?>
                                        </span>
                                    </td>
                                    <td data-label="<?= e(t('team.col_expires')) ?>">
                                        <?= e(format_datetime((string) $invite['expires_at'])) ?>
                                    </td>
                                    <td data-label="<?= e(t('team.col_state')) ?>">
                                        <span class="badge <?= e($badgeClass($status)) ?>"><?= e($statusLabel($status)) ?></span>
                                    </td>
                                    <td data-label="<?= e(t('common.actions')) ?>" class="text-right action-cell">
                                        <?php if ($status === 'pending'): ?>
                                            <form method="post" action="<?= e(url('/equipa/convites/' . $invite['id'] . '/revogar')) ?>"
                                                  data-confirm="<?= e(t('team.revoke_confirm')) ?>">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn--sm btn--danger-ghost"><?= e(t('team.revoke')) ?></button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted text-sm"><?= e($statusLabel($status)) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <aside class="stack">
            <section class="card" aria-labelledby="novo-convite" data-tour-id="team-invite-form"
                     data-tour-title="<?= e(t('tour.team_invites_title')) ?>"
                     data-tour-text="<?= e(t('tour.team_invites_text')) ?>">
                <header class="card__head">
                    <div>
                        <h2 id="novo-convite"><?= e(t('team.invite_title')) ?></h2>
                        <p class="text-muted"><?= e(t('team.invite_help')) ?></p>
                    </div>
                </header>

                <form method="post" action="<?= e(url('/equipa/convites')) ?>" class="form" novalidate>
                    <?= csrf_field() ?>

                    <div class="field">
                        <label for="invite-email"><?= e(t('team.invite_email')) ?> <span class="required" aria-hidden="true">*</span></label>
                        <input type="email" id="invite-email" name="email" value="<?= e((string) ($inviteOld['email'] ?? '')) ?>"
                               required maxlength="150" autocomplete="off" class="<?= field_class($inviteErrors, 'email') ?>">
                        <?php if (isset($inviteErrors['email'])): ?>
                            <p class="field__error"><?= e($inviteErrors['email']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="invite-role"><?= e(t('team.invite_role')) ?></label>
                        <select id="invite-role" name="role">
                            <option value="colaborador" <?= ($inviteOld['role'] ?? '') === 'colaborador' ? 'selected' : '' ?>><?= e(t('common.staff')) ?></option>
                            <option value="gestor" <?= ($inviteOld['role'] ?? '') === 'gestor' ? 'selected' : '' ?>><?= e(t('common.manager')) ?></option>
                        </select>
                        <?php if (isset($inviteErrors['role'])): ?>
                            <p class="field__error"><?= e($inviteErrors['role']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="invite-expiry"><?= e(t('team.invite_expiry')) ?></label>
                        <select id="invite-expiry" name="expiry">
                            <?php foreach ([1 => t('team.expiry_1'), 3 => t('team.expiry_3'), 7 => t('team.expiry_7'), 14 => t('team.expiry_14')] as $days => $label): ?>
                                <option value="<?= e((string) $days) ?>" <?= (int) ($inviteOld['expiry'] ?? 7) === $days ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn--primary btn--block"><?= icon('plus') ?> <?= e(t('team.generate')) ?></button>
                </form>
            </section>

            <section class="callout callout--card">
                <?= icon('lock') ?>
                <p><?= e(t('team.admin_note')) ?></p>
            </section>
        </aside>
    </div>
<?php else: ?>
    <form class="toolbar" method="get" action="<?= e(url('/equipa')) ?>" role="search">
        <div class="search-field">
            <?= icon('search') ?>
            <label class="sr-only" for="q"><?= e(t('team.search_placeholder')) ?></label>
            <input type="search" id="q" name="q" value="<?= e($query) ?>" placeholder="<?= e(t('team.search_placeholder')) ?>">
        </div>
        <button type="submit" class="btn btn--ghost"><?= e(t('common.search')) ?></button>
        <?php if ($query !== ''): ?>
            <a class="btn btn--link" href="<?= e(url('/equipa')) ?>"><?= e(t('common.clear')) ?></a>
        <?php endif; ?>
    </form>

    <section class="card" data-tour-id="team-users"
             data-tour-title="<?= e(t('tour.team_users_title')) ?>"
             data-tour-text="<?= e(t('tour.team_users_text')) ?>">
        <header class="card__head">
            <div>
                <h2><?= e(t('team.users_title')) ?></h2>
                <p class="text-muted"><?= e(t(count($users) === 1 ? 'team.users_count_one' : 'team.users_count', ['count' => count($users)])) ?></p>
            </div>
            <span class="count-pill"><?= e((string) count($users)) ?></span>
        </header>

        <?php if ($users === []): ?>
            <div class="empty-state empty-state--sm">
                <p><?= e(t('team.no_users')) ?></p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col"><?= e(t('team.col_user')) ?></th>
                            <th scope="col"><?= e(t('team.col_role')) ?></th>
                            <th scope="col"><?= e(t('team.col_account')) ?></th>
                            <th scope="col"><?= e(t('team.col_created')) ?></th>
                            <th scope="col" class="text-right"><?= e(t('common.actions')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $account): ?>
                            <tr>
                                <td data-label="<?= e(t('team.col_user')) ?>">
                                    <span class="user-line">
                                        <span class="avatar avatar--xs" aria-hidden="true"><?= e(initials((string) $account['name'])) ?></span>
                                        <span>
                                            <strong><?= e($account['name']) ?></strong>
                                            <span class="cell-sub"><?= e($account['email']) ?></span>
                                        </span>
                                    </span>
                                </td>
                                <td data-label="<?= e(t('team.col_role')) ?>">
                                    <span class="badge <?= $account['role'] === 'gestor' ? 'badge--role-manager' : 'badge--role-staff' ?>">
                                        <?= e($account['role'] === 'gestor' ? t('common.manager') : t('common.staff')) ?>
                                    </span>
                                </td>
                                <td data-label="<?= e(t('team.col_account')) ?>">
                                    <?php if ((int) $account['active'] === 1): ?>
                                        <span class="badge badge--active"><?= e(t('common.active')) ?></span>
                                    <?php else: ?>
                                        <span class="badge badge--archived"><?= e(t('common.inactive')) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="<?= e(t('team.col_created')) ?>"><?= e(format_date((string) $account['created_at'])) ?></td>
                                <td data-label="<?= e(t('common.actions')) ?>" class="text-right">
                                    <a class="btn btn--sm btn--ghost" href="<?= e(url('/equipa/utilizadores/' . $account['id'])) ?>">
                                        <?= e(t('team.profile')) ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
