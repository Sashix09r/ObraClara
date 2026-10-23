# 02 — Modelo conceptual (sem SQL)

**Utilizadores**: identificador, nome, email único, palavra-passe protegida, perfil, ativo, criação.

**Clientes**: identificador, nome, contacto, email, notas, ativo.

**Obras**: identificador, cliente, título, descrição, orçamento base, datas, estado, criação.

**Alterações**: identificador, obra, título, descrição, custo adicional, estado, autor, data de criação, data de decisão, responsável pela decisão.

**Histórico de ações**: identificador, alteração, utilizador, tipo de ação, estado anterior, estado novo, data e hora.

## Relações
- Um cliente tem **0..N obras**; cada obra tem **1 cliente**.
- Uma obra tem **0..N alterações**; cada alteração pertence a **1 obra**.
- Cada alteração pode ter **0..N eventos de histórico**.
- Cada evento identifica **1 utilizador**.
- Cada alteração tem um autor (utilizador) e pode ter um decisor (gestor).

## Fórmula central
Orçamento atualizado = orçamento base da obra + soma dos custos das alterações com estado «aprovada».

### Notas
Valores monetários armazenados em tipo decimal exato, não ponto flutuante binário. Datas e ações auditáveis. MVP de uma única empresa; sem multi-tenant.
