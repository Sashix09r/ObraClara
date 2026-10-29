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
| 8 | Gestor: aprovar alteração de 650 € | `302 → /obras/1` (sem âncora); página abre no topo e mostra 8 650,00 €, +650,00 €, “Aprovada”, decisor Ana Ferreira | PASSOU |
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

## 3.1 Ensaio no navegador (29/10/2026)

Percurso do guião repetido em Chromium, em desktop (1440 px) e ecrã estreito, contra a
base de demonstração reposta antes e depois. Detalhe em
[`ENSAIO_DEMONSTRACAO.md`](ENSAIO_DEMONSTRACAO.md).

| ID | Cenário | Esperado | Obtido | Estado |
|---|---|---|---|---|
| T26 | Criar e submeter 420,00 € como colaborador | Pendente, orçamento 8 000 € | Pendente; 8 000,00 €; histórico com «Submetida» | **PASSOU** |
| T27 | Aprovar 650 € e rejeitar 420 € como gestor | 8 650 € e depois manter 8 650 € | 8 650,00 € em ambos; mensagens no topo (`scrollY = 0`) | **PASSOU** |
| T28 | Layout dos cartões de decisão | Rádio e texto dentro do cartão, com margem | `padding` 14 px, cartão de 93 px, sem sobreposição | **PASSOU** |
| T29 | Teclado na decisão | Setas mudam a escolha; observação obrigatória ao rejeitar | `ArrowDown` selecionou Rejeitar e ativou `required` | **PASSOU** |
| T30 | Cabeçalho e menu a 390 px | Sem overflow e barra numa linha | `scrollWidth` 373; barra de 58 px; menu abre e «Sair» visível | **PASSOU** |

**Cronometragem:** o mesmo percurso por HTTP, numa base descartável, fez **14 pedidos em
384 ms**, terminando em 8 650,00 €. Os pedidos autenticados demoraram 6–25 ms; o login
76–89 ms (verificação de palavra-passe).

---

## 3.2 Apache 2.4.66 do Laragon com Virtual Host

**Data:** 29/10/2026, 11:50–12:05
**Servidor:** Laragon 8.6.1.60301 · Apache/2.4.66 (Win64) VS18, `mod_php` com PHP 8.3.30,
`pdo_sqlite`, `sqlite3` e `curl`.
**Virtual Host:** `C:\laragon\etc\apache2\sites-enabled\obraclara-v2.test.conf`

```apache
<VirtualHost *:80>
    ServerName obraclara-v2.test
    ServerAlias 172.16.58.199
    DocumentRoot "C:/laragon/www/ObraClaraV2/public"
    <Directory "C:/laragon/www/ObraClaraV2/public">
        Options -Indexes
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

- Entrada em `C:\Windows\System32\drivers\etc\hosts`: `127.0.0.1 obraclara-v2.test`.
- `httpd.exe -t` → **Syntax OK**; `httpd.exe -S` lista o vhost e o alias do IP.
- O vhost automático `auto.ObraClara.test.conf` aponta para o protótipo antigo
  (`www/ObraClara/public`) e **não foi alterado**. O Apache foi arrancado pelo binário do
  Laragon; a interface gráfica do Laragon não foi usada.
- URLs testados: `http://obraclara-v2.test/` e `http://172.16.58.199/`.

| # | Verificação | Resultado observado | Estado |
|---|---|---|---|
| 1 | `GET /login` pelo nome | `200`, título «Entrar · ObraClara» | PASSOU |
| 2 | Cabeçalhos de segurança | CSP, `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy`, `Cache-Control: no-store` | PASSOU |
| 3 | Estáticos | CSS `200 text/css`, JS `200 text/javascript`, SVG `200 image/svg+xml` | PASSOU |
| 4 | Rotas privadas sem sessão | `/obras`, `/clientes`, `/historico` → `302 /login` | PASSOU |
| 5 | Rota inexistente e ficheiro interno | Anónimo → `302`; gestor em `/app/Support/Auth.php` → `404`, sem código fonte | PASSOU |
| 6 | Listagem de diretório | `/assets/` → `403` (`Options -Indexes`) | PASSOU |
| 7 | Login do colaborador | `302`; painel «Bom dia, Rui»; obra com 8 000,00 € e 650,00 € | PASSOU |
| 8 | Colaborador tenta decidir | `403` «Sem permissão» | PASSOU |
| 9 | Logout por POST | `302 /login` | PASSOU |
| 10 | Login do gestor e página de decisão | `200`; «Se aprovar: 8 650,00 €» | PASSOU |
| 11 | Aprovar 650,00 € | `302 /obras/1`; página com **8 650,00 €**, mensagem de sucesso e Ana Ferreira no histórico | PASSOU |
| 12 | Histórico global | Evento «Aprovada» com valor, autor e data | PASSOU |
| 13 | Fluxo completo pelo IP `172.16.58.199` | 15/15 verificações iguais às do nome | PASSOU |
| 14 | Registo de erros do Apache | Apenas o `403` esperado de `/assets/`; sem erros PHP | PASSOU |
| 15 | Reposição após os testes | `seed.php --reset`; demo em **8 000,00 €** com 650 € pendente | PASSOU |

O teste foi executado com um script PHP temporário (cookie jar e token CSRF reais) e
repetido pelo IP para confirmar que o URL preparado para o telemóvel não depende do nome.

### Telemóvel físico

**Estado: NÃO EXECUTADO.** O agente não tem um telemóvel nem `adb` acessível e não pode
operar um dispositivo físico. O que foi validado: o URL da rede local responde `200` a
partir do próprio PC e o fluxo completo passou por esse IP. Isso **não prova** acesso a
partir de outro dispositivo (firewall ou isolamento do ponto de acesso podem bloquear).

Para concluir em dois minutos, com o telemóvel na mesma rede Wi-Fi «CSM»:

1. Abrir `http://172.16.58.199/` no browser do telemóvel.
2. Entrar com `colaborador@obraclara.pt` / `Colab2026!`.
3. Abrir **Obras → Remodelação T3 — Rua das Flores**; confirmar 8 000,00 € e o menu ☰.
4. Confirmar que não existe botão «Decidir» e que a tabela se lê em cartões.
5. Terminar sessão e entrar como gestor; abrir a alteração de 650 € e ver «Se aprovar: 8 650,00 €».
6. Registar aqui o resultado real do dispositivo (modelo, browser e data).

Se o URL não abrir, verificar que o telemóvel não está numa rede de convidados, que o
Windows permite `httpd.exe` na firewall e que o Apache está em execução.

---

## 4. Correções feitas durante os testes

| Problema encontrado | Correção | Verificação |
|---|---|---|
| `--reset` não apagava o SQLite no Windows | A ligação PDO continuava referenciada; passou a `unset($pdo)` + `Database::setPdo(null)` antes do `unlink` | `seed.php --reset` executado com sucesso |
| Cartões de decisão com o radio sobreposto ao texto | `position:absolute` substituído por CSS Grid de duas colunas | Captura `05-decidir.png` |
| Cartões sem `padding` e `fieldset` com moldura nativa | A regra `.field label` sobrepunha-se; `.field--group` remove a moldura e `.field .choice` repõe 14 px | `padding` calculado 14 px; captura `05-decidir.png` |
| Cabeçalho quebrava a data em duas linhas a 390 px | Até 560 px: data oculta, contexto com elipse, barra compacta | Captura `07-mobile-obra.png`; `scrollWidth` 373 |
| Coluna do dashboard esticada pela grelha | `align-items: start` na grelha | Captura `02-painel.png` |
| Texto longo numa célula criava overflow a 390 px | Células empilhadas com `flex-wrap` e `overflow-wrap` | Medição `scrollWidth: 373` a 390 px |
| Data do cabeçalho com todas as palavras capitalizadas | `ucfirst()` no helper e remoção de `text-transform` | Captura do dashboard |
| Teste T16 usava títulos de 1 letra | Títulos de teste corrigidos (regra mínima de 3 caracteres) | Suíte 25/25 |

---

## 5. Não executado (limitações honestas)

| Item | Motivo | Estado |
|---|---|---|
| Teste em telemóvel físico | O agente não tem dispositivo nem `adb`; o URL da LAN e o fluxo por IP foram validados no PC. Passos na secção 3.2 | **NÃO EXECUTADO** |
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
