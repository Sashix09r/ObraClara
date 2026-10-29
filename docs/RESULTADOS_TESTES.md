# Resultados dos testes — ObraClara V2

**Data de execução:** 29/10/2026
**Ambiente:** Windows, Laragon, PHP 8.3.30 (CLI, ZTS), extensões `pdo_sqlite` e `sqlite3`,
servidor integrado `php -S localhost:8000 -t public public/router.php`.
**Base de dados dos testes:** ficheiros SQLite descartáveis em `tmp/`; a base de demonstração
não foi usada para testes destrutivos.

> Regra seguida: **FALHOU ≠ PASSOU**. Só se marca PASSOU o que foi realmente executado,
> com o resultado observado. O que não foi executado está identificado como NÃO EXECUTADO.

---

## 1. Suíte automatizada

Comando:

```bash
php tests/run.php
```

Resultado real (última execução):

```
ObraClara V2 — testes automatizados
Base descartável: C:\laragon\www\ObraClaraV2/tmp/test-obraclara.sqlite

  ✓ T01 Conversão de valores monetários para cêntimos
  ✓ T02 Rejeição de valores inválidos
  ✓ T03 Formatação em euros pt-PT
  ✓ T04 Escape de HTML contra XSS
  ✓ T05 Autenticação com hash de palavra-passe
  ✓ T06 CSRF aceita apenas o token da sessão
  ✓ T07 Colaborador autenticado não pode decidir alteração
  ✓ T08 Validação e criação de cliente
  ✓ T09 Pesquisa de clientes com e sem resultados
  ✓ T10 Arquivo de cliente respeita obras ativas
  ✓ T11 Obra exige cliente ativo existente
  ✓ T12 Orçamento atualizado ignora rascunhos e pendentes
  ✓ T13 Valores de alteração inválidos são recusados
  ✓ T14 Cenário 8 000 € + 650 € aprovada = 8 650 € com histórico
  ✓ T15 Rejeição mantém o orçamento e exige observação
  ✓ T16 Várias aprovações somam corretamente
  ✓ T17 Transições inválidas e imutabilidade após decisão
  ✓ T18 Rascunho só é editável pelo autor ou gestor
  ✓ T19 Arquivo de obra e efeitos nas alterações
  ✓ T20 IDs inexistentes e integridade referencial
  ✓ T21 Pesquisa de obras por texto e estado
  ✓ T22 Persistência após reabrir a base de dados
  ✓ T23 Indicadores do dashboard vêm da base de dados
  ✓ T24 Histórico global inclui autor, obra e transição
  ✓ T25 Acentuação portuguesa preservada na base de dados e no HTML

--------------------------------------------------------------------
Total: 25 testes · 25 passaram · 0 falharam
```

Código de saída: **0**.

Cada teste cria uma base nova, aplica `database/schema.sql` e fixtures próprios, pelo que
não há dependência entre testes nem contaminação da demonstração.

---

## 2. Casos do plano original (`docs/04_plano_de_testes.md`)

| ID | Cenário | Esperado | Obtido | Evidência | Estado |
|---|---|---|---|---|---|
| T01 | Login válido e inválido | Acesso permitido/recusado | Login válido 302 → `/`; inválido 401 com “Credenciais inválidas” | `tests/run.php` T05; `curl` HTTP | **PASSOU** |
| T02 | Clientes CRUD e arquivo | Persistência e validações | Cliente criado e lido; arquivo bloqueado com obras ativas; reativação funciona | T08, T09, T10 | **PASSOU** |
| T03 | Obras CRUD e relação cliente | Obra exige cliente válido | Sem cliente, cliente inexistente e cliente arquivado → erro; obra válida persiste | T11 | **PASSOU** |
| T04 | Alteração com valor negativo | Erro de validação | `-50` e `0,00` recusados; nada criado | T13; HTTP 422 | **PASSOU** |
| T05 | Submeter alteração | Estado pendente | `rascunho → pendente`, `submitted_at` preenchido | T12, T14 | **PASSOU** |
| T06 | Colaborador tenta aprovar | Acesso recusado | `AuthorizationException` no serviço; HTTP **403** no pedido direto | T07; `curl` 403 | **PASSOU** |
| T07 | Gestor aprova 8 000 + 650 | Orçamento 8 650 € e evento | 865 000 cêntimos; evento “aprovada” com gestor e data | T14; captura `11-obra-8650.png` | **PASSOU** |
| T08 | Rejeitar alteração | Orçamento inalterado | Continua 800 000 cêntimos; motivo guardado | T15 | **PASSOU** |
| T09 | Editar alteração aprovada | Bloqueado/auditado | `BusinessRuleException` + trigger SQL recusa `UPDATE` | T17 | **PASSOU** |
| T10 | Pesquisa sem resultados | Mensagem clara | Lista vazia + estado vazio com ação “Limpar pesquisa” | T09, T21; UI | **PASSOU** |
| T11 | Ecrã pequeno | Layout utilizável | Sidebar colapsa, orçamentos empilham, tabela em cartões; sem overflow horizontal | Captura `07-mobile-obra.png`; medição no navegador (390 px → `scrollWidth` 373) | **PASSOU** |
| T12 | Reabrir aplicação | Dados mantidos | Nova ligação PDO ao mesmo ficheiro mantém 8 650 €, estado e 3 eventos | T22 | **PASSOU** |

---

## 3. Testes HTTP reais (curl)

Servidor: `php -S localhost:8000 -t public public/router.php`.

| # | Pedido | Resultado observado | Estado |
|---|---|---|---|
| 1 | `GET /obras` sem sessão | `302 → /login` | PASSOU |
| 2 | Login válido (gestor) | `302 → /` e dashboard 200 | PASSOU |
| 3 | Login inválido | `401` + mensagem sem revelar o campo errado | PASSOU |
| 4 | `POST /login` sem token CSRF | `419 Sessão expirada` | PASSOU |
| 5 | Colaborador: `GET /obras/criar` | `403 Sem permissão` | PASSOU |
| 6 | Colaborador: `GET /clientes/criar` | `403 Sem permissão` | PASSOU |
| 7 | Colaborador: `POST /alteracoes/1/decidir` | `403`, mensagem “Apenas o gestor pode aprovar ou rejeitar alterações.”, estado mantém-se pendente | PASSOU |
| 8 | Gestor: aprovar alteração de 650 € | `302 → /obras/1#alteracao-1`; página mostra 8 650,00 €, +650,00 €, “Aprovada”, decisor Ana Ferreira | PASSOU |
| 9 | Repetir decisão da mesma alteração | Erro “já tem uma decisão final”; orçamento continua 8 650,00 € | PASSOU |
| 10 | Rejeitar alteração do WC (obra de 3 200 €) | `302`; obra continua 3 200,00 €; motivo visível na página | PASSOU |
| 11 | Alteração com valor `-50` | `422` com erro de validação no campo | PASSOU |
| 12 | Editar obra arquivada (gestor) | `302 → /obras/7` com aviso; não abre o formulário | PASSOU |
| 13 | Obra inexistente `/obras/9999` | `404 Página não encontrada` | PASSOU |
| 14 | `POST /logout` sem CSRF | `419`, sessão não terminada | PASSOU |
| 15 | Cabeçalhos de segurança | CSP, `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy`, `Cache-Control: no-store` presentes | PASSOU |

**Nota de isolamento:** os testes de escrita HTTP correram sobre
`tmp/http-test.sqlite` (cópia descartável). Para a captura `11-obra-8650.png`, a aprovação
foi executada na base de demonstração e, imediatamente depois, `php scripts/seed.php --reset`
recompôs o cenário de 8 000 € com a alteração pendente. O estado atual da demo foi
verificado: **8 000,00 €, 650 € pendente**.

---

## 4. Correções feitas durante os testes

| Problema encontrado | Correção | Verificação |
|---|---|---|
| `--reset` não apagava o SQLite no Windows | A ligação PDO continuava referenciada; passou a `unset($pdo)` + `Database::setPdo(null)` antes do `unlink` | `seed.php --reset` executado com sucesso |
| Cartões de decisão com o radio sobreposto ao texto | `position:absolute` substituído por CSS Grid de duas colunas | Captura `05-decidir.png` |
| Coluna do dashboard esticada pela grelha | `align-items: start` na grelha | Captura `02-painel.png` |
| Texto longo numa célula criava overflow a 390 px | Células empilhadas com `flex-wrap` e `overflow-wrap` | Medição `scrollWidth: 373` a 390 px |
| Data do cabeçalho com todas as palavras capitalizadas | `ucfirst()` no helper e remoção de `text-transform` | Captura do dashboard |
| Teste T16 usava títulos de 1 letra | Títulos de teste corrigidos (regra mínima de 3 caracteres) | Suíte 25/25 |

---

## 5. Não executado (limitações honestas)

| Item | Motivo | Estado |
|---|---|---|
| Instalação em Apache/Laragon com Virtual Host | Foi usado o servidor integrado do PHP; `.htaccess` não foi exercitado em Apache | **NÃO EXECUTADO** |
| Teste em telemóvel físico | Só foram usados tamanhos de janela em navegador desktop | **NÃO EXECUTADO** |
| Firefox, Safari e Edge | Apenas Chromium (Chrome/Freebuff) | **NÃO EXECUTADO** |
| Leitor de ecrã (NVDA/VoiceOver) | Fora do tempo disponível | **NÃO EXECUTADO** |
| Teste de usabilidade com colega | Não houve sessão com terceiros | **NÃO EXECUTADO** |
| Testes de carga/concorrência | SQLite e âmbito académico de utilizador único | **NÃO EXECUTADO** |
| Cópia de segurança/restauro | Não implementado | **NÃO EXECUTADO** |
| HTTPS | Ambiente local HTTP | **NÃO EXECUTADO** |

---

## 6. Reprodução

```bash
php tests/run.php
php scripts/install.php
php scripts/seed.php --reset
php -S localhost:8000 -t public public/router.php
```

Consultas úteis para inspeção (base de demonstração):

```sql
SELECT status, COUNT(*) FROM changes GROUP BY status;
SELECT p.title, p.base_budget_cents,
       COALESCE(SUM(CASE WHEN ch.status='aprovada' THEN ch.amount_cents END), 0) AS aprovado
  FROM projects p LEFT JOIN changes ch ON ch.project_id = p.id
 GROUP BY p.id;
```
