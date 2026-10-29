-- ObraClara V2 — esquema relacional SQLite
-- Valores monetários em cêntimos inteiros. Datas em texto ISO (Europe/Lisbon).
-- Este ficheiro representa uma instalação NOVA. Bases existentes são
-- atualizadas por app/Support/Migrator.php (migrações incrementais).

PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    name              TEXT    NOT NULL,
    email             TEXT    NOT NULL UNIQUE COLLATE NOCASE,
    password_hash     TEXT    NOT NULL,
    role              TEXT    NOT NULL CHECK (role IN ('gestor', 'colaborador')),
    active            INTEGER NOT NULL DEFAULT 1 CHECK (active IN (0, 1)),
    locale            TEXT    NOT NULL DEFAULT 'pt' CHECK (locale IN ('pt', 'en')),
    tour_completed_at TEXT,
    last_login_at     TEXT,
    created_at        TEXT    NOT NULL,
    updated_at        TEXT
);

CREATE INDEX IF NOT EXISTS idx_users_role_active ON users (role, active);

CREATE TABLE IF NOT EXISTS invites (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    email       TEXT    NOT NULL COLLATE NOCASE,
    role        TEXT    NOT NULL CHECK (role IN ('gestor', 'colaborador')),
    token_hash  TEXT    NOT NULL UNIQUE,
    expires_at  TEXT    NOT NULL,
    created_by  INTEGER NOT NULL REFERENCES users (id) ON DELETE RESTRICT,
    created_at  TEXT    NOT NULL,
    used_at     TEXT,
    used_by     INTEGER REFERENCES users (id) ON DELETE RESTRICT,
    revoked_at  TEXT
);

CREATE INDEX IF NOT EXISTS idx_invites_email ON invites (email);
CREATE INDEX IF NOT EXISTS idx_invites_pending ON invites (used_at, revoked_at, expires_at);

CREATE TABLE IF NOT EXISTS admin_events (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    actor_id    INTEGER NOT NULL REFERENCES users (id) ON DELETE RESTRICT,
    action      TEXT    NOT NULL CHECK (action IN (
                    'convite_criado', 'convite_revogado', 'conta_criada',
                    'perfil_alterado', 'palavra_passe_alterada', 'papel_alterado',
                    'conta_ativada', 'conta_desativada', 'idioma_alterado'
                )),
    target_type TEXT    NOT NULL CHECK (target_type IN ('user', 'invite')),
    target_id   INTEGER NOT NULL,
    note        TEXT,
    created_at  TEXT    NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_admin_events_created ON admin_events (created_at);
CREATE INDEX IF NOT EXISTS idx_admin_events_target ON admin_events (target_type, target_id);

CREATE TABLE IF NOT EXISTS clients (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT    NOT NULL,
    contact    TEXT,
    email      TEXT,
    notes      TEXT,
    active     INTEGER NOT NULL DEFAULT 1 CHECK (active IN (0, 1)),
    created_at TEXT    NOT NULL,
    updated_at TEXT    NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_clients_active ON clients (active);
CREATE INDEX IF NOT EXISTS idx_clients_name ON clients (name);

CREATE TABLE IF NOT EXISTS projects (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id         INTEGER NOT NULL REFERENCES clients (id) ON DELETE RESTRICT,
    title             TEXT    NOT NULL,
    description       TEXT,
    base_budget_cents INTEGER NOT NULL
        CHECK (base_budget_cents >= 0 AND base_budget_cents <= 9999999999),
    start_date        TEXT,
    status            TEXT    NOT NULL DEFAULT 'planeada'
        CHECK (status IN ('planeada', 'em_curso', 'concluida', 'arquivada')),
    created_by        INTEGER REFERENCES users (id) ON DELETE SET NULL,
    created_at        TEXT    NOT NULL,
    updated_at        TEXT    NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_projects_client ON projects (client_id);
CREATE INDEX IF NOT EXISTS idx_projects_status ON projects (status);

CREATE TABLE IF NOT EXISTS changes (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id    INTEGER NOT NULL REFERENCES projects (id) ON DELETE RESTRICT,
    title         TEXT    NOT NULL,
    description   TEXT,
    amount_cents  INTEGER NOT NULL
        CHECK (amount_cents > 0 AND amount_cents <= 9999999999),
    status        TEXT    NOT NULL DEFAULT 'rascunho'
        CHECK (status IN ('rascunho', 'pendente', 'aprovada', 'rejeitada')),
    created_by    INTEGER NOT NULL REFERENCES users (id) ON DELETE RESTRICT,
    submitted_at  TEXT,
    decided_at    TEXT,
    decided_by    INTEGER REFERENCES users (id) ON DELETE RESTRICT,
    decision_note TEXT,
    created_at    TEXT    NOT NULL,
    updated_at    TEXT    NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_changes_project ON changes (project_id);
CREATE INDEX IF NOT EXISTS idx_changes_status ON changes (status);

CREATE TABLE IF NOT EXISTS change_events (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    change_id   INTEGER NOT NULL REFERENCES changes (id) ON DELETE CASCADE,
    user_id     INTEGER NOT NULL REFERENCES users (id) ON DELETE RESTRICT,
    action      TEXT    NOT NULL
        CHECK (action IN ('criada', 'editada', 'submetida', 'aprovada', 'rejeitada')),
    from_status TEXT,
    to_status   TEXT    NOT NULL,
    note        TEXT,
    created_at  TEXT    NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_events_change ON change_events (change_id);
CREATE INDEX IF NOT EXISTS idx_events_created ON change_events (created_at);

-- Defesa adicional: uma alteração com decisão final é imutável, mesmo que
-- exista um erro na aplicação. O histórico e o valor aprovado não podem mudar.
CREATE TRIGGER IF NOT EXISTS trg_changes_final_is_immutable
BEFORE UPDATE ON changes
WHEN OLD.status IN ('aprovada', 'rejeitada')
BEGIN
    SELECT RAISE(ABORT, 'Uma alteração decidida não pode ser modificada.');
END;

CREATE TRIGGER IF NOT EXISTS trg_changes_guard_status
BEFORE UPDATE OF status ON changes
WHEN NEW.status NOT IN ('rascunho', 'pendente', 'aprovada', 'rejeitada')
BEGIN
    SELECT RAISE(ABORT, 'Estado de alteração inválido.');
END;

-- O histórico é apenas de inserção: não pode ser reescrito nem apagado.
CREATE TRIGGER IF NOT EXISTS trg_events_immutable_update
BEFORE UPDATE ON change_events
BEGIN
    SELECT RAISE(ABORT, 'O histórico é imutável.');
END;

CREATE TRIGGER IF NOT EXISTS trg_events_immutable_delete
BEFORE DELETE ON change_events
BEGIN
    SELECT RAISE(ABORT, 'O histórico é imutável.');
END;
