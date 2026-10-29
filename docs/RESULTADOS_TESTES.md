# Resultados dos testes — ObraClara V2

**Data da execução final:** 29/10/2026
**Ambiente:** Windows, Laragon, PHP 8.3.30 (CLI, ZTS), extensões `pdo_sqlite` e `sqlite3`,
servidor integrado `php -S 127.0.0.1:8000 -t public public/router.php`.
**Bases dos testes:** ficheiros SQLite descartáveis em `tmp/`; a base de demonstração não
foi usada para testes destrutivos.

**Resumo:** 38/38 testes automatizados, 37/37 verificações HTTP e 33/33 verificações de
browser passaram. A demo principal foi preservada em 8 000 € com a alteração pendente.

> Regra seguida: **FALHOU ≠ PASSOU**. Só se marca PASSOU o que foi realmente executado,
> com o resultado observado. O que não foi executado está identificado como NÃO EXECUTADO.

---

## 1. Suíte automatizada

Comando:

```bash
php tests/run.php
```

Resultado real da execução final:

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
  ✓ T26 Convite guarda apenas hash e só pode ser usado uma vez
  ✓ T27 Convite inválido não cria conta
  ✓ T27b Convite revogado e expirado não criam conta
  ✓ T28 Registo valida email do convite e palavras-passe
  ✓ T29 Perfil do convite não pode ser elevado pelo formulário
  ✓ T30 Gestão de equipa protege o último gestor e contas próprias
  ✓ T31 Perfil valida email único e regista auditoria
  ✓ T32 Alteração de palavra-passe exige atual e confirmação
  ✓ T33 PDF real contém valores, acentos e várias páginas quando necessário
  ✓ T34 Dicionários PT/EN têm as mesmas chaves e cobrem as vistas
  ✓ T35 Tutorial concluído fica guardado por utilizador
  ✓ T36 Migrações atualizam base antiga sem perder dados e são idempotentes
  ✓ T37 Email duplicado é recusado mesmo com convite válido

--------------------------------------------------------------------
Total: 38 testes · 38 passaram · 0 falharam
```

Código de saída: **0**.

Cada teste cria uma base nova, aplica `database/schema.sql` e fixtures próprios, pelo que
não há dependência entre testes nem contaminação da demonstração. T27b é um caso adicional
(introduzido durante a revisão) e entra na contagem como teste próprio.

---

## 2. Cenário de aceitação

| Passo | Esperado | Obtido | Evidência | Estado |
|---|---|---|---|---|
| Obra base | 8 000,00 € | 800 000 cêntimos | T14; captura `04-obra-8000.png` | **PASSOU** |
| Alteração de 650 € pendente | Orçamento continua 8 000,00 € | `updated_budget_cents = 800000` | T12, T14 | **PASSOU** |
| Rejeição | Orçamento continua 8 000,00 € e motivo guardado | 800 000; observação obrigatória | T15 | **PASSOU** |
| Aprovação pelo gestor | 8 650,00 € e evento com autor/data | 865 000; histórico com Ana Ferreira | T14; HTTP #25–27; captura `11-obra-8650.png` | **PASSOU** |
| Colaborador tenta decidir | 403, estado mantém-se | `AuthorizationException`; HTTP 403 | T07; HTTP #12 | **PASSOU** |
| Várias aprovações | Soma exata | 1 000,50 € adicionados; total 9 000,50 € | T16 | **PASSOU** |
| Persistência | Dados mantêm-se ao reabrir | 865 000 e 3 eventos após nova ligação | T22 | **PASSOU** |

---

## 3. Testes HTTP reais

**Servidor:** `php -S 127.0.0.1:8021 -t public public/router.php` com
`OBRACLARA_DB=tmp/http-obraclara-test.sqlite`.
**Base:** `php scripts/install.php` e `php scripts/seed.php --reset` antes do percurso.
**Cliente:** `curl` com cookie jar e tokens CSRF reais; script temporário em `tmp/`
(ignorado pelo Git).

Resultado: **37/37 verificações passaram**. O servidor de demonstração em 8000 não foi
usado para escritas.

### 3.1 Conta, convite, registo e perfil

| # | Pedido / verificação | Resultado observado | Estado |
|---|---|---|---|
| 1 | `GET /login` | 200 | PASSOU |
| 2 | `POST /login` gestor | 302 → `/` | PASSOU |
| 3 | `GET /equipa?tab=convites` | 200 | PASSOU |
| 4 | `POST /equipa/convites` (colaborador, 7 dias) | 302 | PASSOU |
| 5 | Página de convite criado | 200, link visível | PASSOU |
| 6 | Token gerado | 64 caracteres hexadecimais | PASSOU |
| 7 | `GET /registar?token=...` | 200, email preenchido | PASSOU |
| 8 | `POST /registar` | 302; sessão iniciada | PASSOU |
| 9 | `GET /` como novo utilizador | 200; nome visível | PASSOU |
| 10 | Menu do colaborador | sem ligação **Equipa** | PASSOU |
| 11 | `GET /equipa` como colaborador | 403 | PASSOU |
| 12 | `GET /alteracoes/1/decidir` como colaborador | 403 | PASSOU |
| 13 | `GET /perfil` | 200 | PASSOU |
| 14 | `POST /perfil` (nome) | 302; nome atualizado na leitura seguinte | PASSOU |
| 15 | `GET /perfil` após alteração | 200; “Novo Nome” | PASSOU |
| 16 | `POST /perfil/palavra-passe` | 302; sessão renovada | PASSOU |
| 17 | `POST /perfil/idioma` (EN) | 302 | PASSOU |
| 18 | `GET /` em inglês | 200; texto “Dashboard” | PASSOU |
| 19 | Atributo de idioma | `lang="en-GB"` | PASSOU |
| 20 | `GET /obras/1/pdf` autenticado | 200, `application/pdf` | PASSOU |
| 21 | Assinatura do PDF | começa por `%PDF-` | PASSOU |
| 22 | `GET /obras/1/pdf` sem sessão | 302 → login | PASSOU |
| 23 | `POST /login` sem token CSRF | 419 | PASSOU |

### 3.2 Decisão e orçamento

| # | Pedido / verificação | Resultado observado | Estado |
|---|---|---|---|
| 24 | `GET /obras/1` antes da decisão | 200; 8 000,00 € | PASSOU |
| 25 | `POST /alteracoes/1/decidir` (aprovar) | 302 | PASSOU |
| 26 | `GET /obras/1` depois | 200; 8 650,00 € | PASSOU |
| 27 | Total atualizado | 865 000 cêntimos | PASSOU |

### 3.3 Convites, equipa e contas

| # | Pedido / verificação | Resultado observado | Estado |
|---|---|---|---|
| 28 | `GET /registar?token=...` já usado | 200; aviso “utilizado” | PASSOU |
| 29 | `POST /registar` com o mesmo token | 422; nenhuma conta criada | PASSOU |
| 30 | Feedback de convite reutilizado | mensagem específica | PASSOU |
| 31 | `GET /equipa/utilizadores/1` | 200 | PASSOU |
| 32 | `POST` desativar o próprio gestor | 302 com erro; conta continua ativa | PASSOU |
| 33 | Estado do último gestor | badge **Ativo** | PASSOU |
| 34 | `POST` desativar o novo utilizador | 302 | PASSOU |
| 35 | `GET /` com sessão da conta desativada | 302 → login | PASSOU |
| 36 | Acesso efetivo após desativação | perdido | PASSOU |
| 37 | `POST /login` com a nova palavra-passe numa conta desativada | 401 | PASSOU |

### 3.4 PDF em inglês

O PDF foi descarregado por HTTP com a sessão em inglês e inspecionado com `pypdf`:

- 1 página, 5 079 bytes;
- rótulos “Project summary”, “Total approved changes”, “Updated budget”;
- valores `€8,000.00`, `€650.00` e `€8,650.00`;
- rodapé “Page 1 of 1”.

**PASSOU.** O documento acompanha o idioma ativo, sem traduzir os nomes introduzidos.

---

## 3.5 Percurso completo no browser (33 verificações)

**Ferramenta:** Chrome 142 + Playwright, contra `http://127.0.0.1:8021` e a base
descartável `tmp/http-obraclara-test.sqlite`.

O percurso foi executado como um utilizador real, com cliques e preenchimento de
formulários, não apenas pedidos HTTP:

| # | Fluxo | Resultado observado | Estado |
|---|---|---|---|
| B01 | Login do gestor | Painel de Ana Ferreira | PASSOU |
| B02 | Criar convite pela interface | Formulário submetido; link visível | PASSOU |
| B03 | Link do convite | 64 caracteres no campo | PASSOU |
| B04 | Convite pendente listado | Email e estado pendente | PASSOU |
| B05 | Logout | Regresso ao login | PASSOU |
| B06 | Registo por convite | Conta criada e sessão iniciada | PASSOU |
| B07 | Boas-vindas | Modal visível | PASSOU |
| B08 | Iniciar tutorial | Passo contextual visível | PASSOU |
| B09 | Primeiro passo | Título do painel | PASSOU |
| B10 | Fechar tutorial com Escape | Modal fechado | PASSOU |
| B11 | Editar perfil | Nome novo apresentado | PASSOU |
| B12 | Alterar palavra-passe | Operação aceite | PASSOU |
| B13 | Login com nova palavra-passe | Sessão iniciada | PASSOU |
| B14 | Mudar para inglês | `lang="en-GB"` | PASSOU |
| B15 | Menu traduzido | “Dashboard” e “Projects” | PASSOU |
| B16 | Tutorial em inglês | Visita aberta | PASSOU |
| B17 | Texto traduzido | “summarises … projects” | PASSOU |
| B18 | Ignorar tutorial | Modal fechado | PASSOU |
| B19 | Colaborador tenta decidir | HTTP 403 | PASSOU |
| B20 | Página de erro | Sem stacktrace | PASSOU |
| B21 | PDF antes da aprovação | Download válido | PASSOU |
| B22 | Conteúdo do PDF antes | `8,000.00` (EN) | PASSOU |
| B23 | Pesquisa na equipa | Conta nova encontrada | PASSOU |
| B24 | Aprovar 650 € | 8 650,00 € | PASSOU |
| B25 | Mensagem de sucesso | “Aprovada” | PASSOU |
| B26 | Histórico | Evento com Ana Ferreira | PASSOU |
| B27 | PDF depois da aprovação | Download válido | PASSOU |
| B28 | Conteúdo do PDF depois | `8 650,00` | PASSOU |
| B29 | Decisão no PDF | Observação registada | PASSOU |
| B30 | Tutorial a 390 px | Caixa dentro do viewport | PASSOU |
| B31 | Overflow a 390 px | `scrollWidth` = viewport | PASSOU |
| B32 | Fechar tutorial móvel | Modal fechado | PASSOU |
| B33 | Formulário móvel | Sem overflow | PASSOU |

**33/33 passaram.** Os PDFs descarregados foram lidos com `pypdf`: 1 página, 4 687 bytes
(antes) e 5 079 bytes (depois), com os valores e a observação corretos.

---

## 4. Revisão de interface e responsividade

**Ferramenta:** Chrome 142 (headless) com Playwright, sessão autenticada real.
**Rotas medidas:** `/`, `/clientes`, `/clientes/1`, `/obras`, `/obras/1`, `/historico`,
`/equipa`, `/equipa?tab=convites`, `/perfil`; além de `/login` e `/registar` sem sessão.
**Larguras:** 320, 375, 390, 430, 768, 1024 e 1440 px.

| Verificação | Resultado | Estado |
|---|---|---|
| `scrollWidth` vs. viewport | Nenhuma rota ultrapassou a largura em nenhuma das 7 larguras | **PASSOU** |
| Menu móvel | Botão ☰ visível e funcional até 960 px | **PASSOU** |
| Marca no topo móvel | “ObraClara” visível a 320 px, sem sobreposição | **PASSOU** |
| Alvos do seletor de idioma | 36 × 34 px após correção | **PASSOU** |
| Tabelas em cartões | Colunas passam a linhas com rótulos até 760 px | **PASSOU** |
| Tutorial a 320 px | Caixa 296 × 331 px, dentro do ecrã; botões empilhados | **PASSOU** |
| Fecho do tutorial | `Escape` fecha e devolve o foco | **PASSOU** |
| Capturas | 17 imagens reais em `docs/capturas/` | **PASSOU** |

A única medida abaixo de 24 px é a caixa nativa `checkbox` de “Mostrar arquivados”; o
label clicável à volta é maior. Não foi tratado como falha de alvo táctil.

### 4.1 Capturas finais

| Ficheiro | Ecrã |
|---|---|
| `01-login.png` | Login com **Criar conta** e seletor PT/EN |
| `02-painel.png` | Painel com indicadores SQL |
| `03-obras.png` | Lista de obras |
| `04-obra-8000.png` | Obra antes da aprovação |
| `05-decidir.png` | Decisão com impacto no orçamento |
| `06-historico.png` | Histórico global |
| `07-mobile-obra.png` | Obra a 390 px |
| `08-clientes.png` | Clientes |
| `09-novo-cliente.png` | Formulário de cliente |
| `10-nova-alteracao.png` | Formulário de alteração |
| `11-obra-8650.png` | Obra depois da aprovação |
| `12-tutorial-boas-vindas.png` | Boas-vindas ao tutorial |
| `13-tutorial.png` | Tutorial sobre o painel |
| `14-equipa.png` | Equipa |
| `15-convites.png` | Convites |
| `16-perfil.png` | Perfil |
| `17-registo.png` | Registo por convite |

Todas foram obtidas da aplicação real após as correções finais. A captura `11` foi gerada
numa base descartável com uma aprovação real e uma observação realista; a demo principal
ficou em 8 000 € com a alteração pendente.

---

## 5. Apache/Laragon

**Servidor:** Apache 2.4.66 do Laragon, Virtual Host `obraclara-v2.test`, DocumentRoot
`C:/laragon/www/ObraClaraV2/public`.
**Data:** 29/10/2026.

Verificações finais executadas por `curl`:

| # | Verificação | Resultado | Estado |
|---|---|---|---|
| A1 | `GET /login` | 200 | PASSOU |
| A2 | `POST /login` gestor | 302 | PASSOU |
| A3 | `GET /` | 200 `text/html` | PASSOU |
| A4 | `GET /equipa` | 200 | PASSOU |
| A5 | `GET /equipa?tab=convites` | 200 | PASSOU |
| A6 | `GET /perfil` | 200 | PASSOU |
| A7 | `GET /obras/1` | 200 | PASSOU |
| A8 | `GET /obras/1/pdf` | 200 `application/pdf` | PASSOU |
| A9 | `GET /assets/css/app.css` | 200 `text/css` | PASSOU |
| A10 | Cabeçalhos | CSP, `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy`, `no-store` | PASSOU |

O fluxo completo de escrita em Apache (login, decisão, histórico e reposição) já tinha
sido validado na mesma versão funcional; nesta execução final foram reconfirmadas as
rotas novas de equipa, convites, perfil e PDF. `/registar` com sessão de gestor devolve
302 para `/`, como esperado.

### Telemóvel físico

**Estado: NÃO EXECUTADO.** O agente não tem um telemóvel nem `adb` acessível e não pode
operar um dispositivo físico. A emulação de viewport foi executada nas sete larguras
acima. Para concluir em dois minutos, com o telemóvel na mesma rede Wi-Fi:

1. Abrir `http://172.16.58.199/` no browser do telemóvel.
2. Entrar com `colaborador@obraclara.pt` / `Colab2026!`.
3. Abrir **Obras → Remodelação T3 — Rua das Flores**; confirmar 8 000,00 € e o menu ☰.
4. Confirmar que não existe botão **Decidir** e que a tabela se lê em cartões.
5. Terminar sessão e entrar como gestor; abrir a alteração de 650 € e ver
   “Se aprovar: 8 650,00 €”.
6. Registar aqui o resultado real (modelo, browser e data).

Se o URL não abrir, verificar que o telemóvel não está numa rede de convidados, que o
Windows permite `httpd.exe` na firewall e que o Apache está em execução.

---

## 6. Segurança — verificações específicas

| Verificação | Resultado | Estado |
|---|---|---|
| Senha guardada com hash | `password_hash`; hash diferente do texto simples | **PASSOU** |
| Login com conta desativada | 401 | **PASSOU** |
| Sessão antiga de conta desativada | pedido seguinte → login | **PASSOU** |
| CSRF ausente no login | 419 | **PASSOU** |
| Colaborador em `/equipa` | 403 | **PASSOU** |
| Colaborador em `/alteracoes/1/decidir` | 403 | **PASSOU** |
| Perfil elevado por campo manipulado | ignorado; perfil vem do convite | **PASSOU** |
| Convite reutilizado | 422; sem conta duplicada | **PASSOU** |
| Convite revogado/expirado | recusado | **PASSOU** |
| Email duplicado | recusado mesmo com convite | **PASSOU** |
| Último gestor | não pode ser desativado/despromovido | **PASSOU** |
| Própria conta | gestor não se desativa | **PASSOU** |
| PDF sem sessão | 302 → login | **PASSOU** |
| Alteração decidida | `UPDATE` bloqueado por trigger | **PASSOU** |
| Evento de histórico | `UPDATE`/`DELETE` bloqueados por trigger | **PASSOU** |
| SQL Injection | consultas preparadas em todas as entradas | **PASSOU** |
| XSS | escape de saída em HTML e atributos (T04) | **PASSOU** |
| Erros internos | mensagem genérica; detalhe apenas no log | **PASSOU** |

---

## 7. Correções feitas durante os testes finais

| Problema encontrado | Correção | Verificação |
|---|---|---|
| Tutorial ficava em branco ao clicar em “Seguinte” | `data-tour-next` do `<body>` colidia com o seletor; os seletores passaram a estar limitados ao painel | Tutorial aberto e percorrido no browser; capturas 12/13 |
| Alvo do primeiro passo do tutorial era o cartão errado | O destaque passou a apontar para o KPI do painel | Captura 13 |
| Badges “Gestor”/“Colaborador” quase invisíveis em cartões brancos | Criadas variantes `badge--role-manager`/`badge--role-staff` | Capturas 14 e 16 |
| Seletor de idioma com alvos de 28 × 23 px | Mínimo 36 × 34 px na barra móvel | Medição no browser |
| Barra superior móvel sem identidade | Marca compacta “ObraClara” até 960 px | Captura a 320 px |
| `seed.php --reset` não apagava o SQLite no Windows | Libertar `PDO` + `Database::setPdo(null)` antes de `unlink` | Reset executado repetidamente |
| PDF com acentos | Codificação Windows-1252 e teste de extração | T33; PDF em PT e EN |
| Migrações em base existente | Cópia de segurança + versões transacionais | T36 |
| Capturas antigas desatualizadas | 17 capturas refeitas após a interface final | Inspeção visual |

---

## 8. Não executado (limitações honestas)

| Item | Motivo | Estado |
|---|---|---|
| Telemóvel físico | O agente não tem dispositivo nem `adb`; a emulação foi executada | **NÃO EXECUTADO** |
| Firefox, Safari e Edge | Apenas Chrome/Chromium | **NÃO EXECUTADO** |
| Leitor de ecrã (NVDA/VoiceOver) | Fora do tempo disponível; revisão manual de ARIA | **NÃO EXECUTADO** |
| Teste de usabilidade com colega | Não houve sessão com terceiros | **NÃO EXECUTADO** |
| Testes de carga/concorrência | SQLite e âmbito académico de utilizador único | **NÃO EXECUTADO** |
| HTTPS | Ambiente local HTTP | **NÃO EXECUTADO** |
| Envio de emails de convite | Fora do âmbito; link copiado manualmente | **NÃO IMPLEMENTADO** |

---

## 9. Reprodução

```bash
php -l app/Support/Auth.php          # exemplo; o projeto foi todo verificado com php -l
php tests/run.php                    # 38/38
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
SELECT version, applied_at FROM schema_migrations ORDER BY version;
```

O comando `php -l` foi executado em todos os ficheiros PHP de `app/`, `public/`, `scripts/`,
`tests/` e `lang/`, sem erros de sintaxe.

---

## 10. Reverificação final do pacote (29/10/2026, 16:54)

Esta execução foi repetida depois da implementação de convites, equipa, perfil, i18n,
tutorial e PDF, para confirmar o estado que vai ser entregue. A base de demonstração foi
copiada para `database/backups/obraclara-20261029-165312-pre-verificacao.sqlite` e os
ensaios de escrita correram sobre `tmp/e2e-browser.sqlite`.

| Verificação | Comando / instrumento | Resultado observado | Estado |
|---|---|---|---|
| Sintaxe PHP | `php -l` em `app/`, `config/`, `public/`, `scripts/`, `tests/` e `lang/` | **67 ficheiros**, 0 erros | **PASSOU** |
| Suíte automatizada | `php tests/run.php` | **38/38**, saída 0 | **PASSOU** |
| Fluxos de browser | Chrome 142 + Playwright em `http://127.0.0.1:8033` | **33/33**, incluindo convite, registo, tutorial, perfil, palavra-passe, PT/EN, 403, aprovação, histórico, PDF e mobile 390 px | **PASSOU** |
| Percurso HTTP | `curl` em `http://127.0.0.1:8034`, base nova `tmp/http-final-obraclara.sqlite` | **37/37**, do login à conta desativada, com CSRF, 403, 419, 422 e PDF | **PASSOU** |
| PDF antes da decisão | Download real, `tmp/e2e/before.pdf` | 1 página, 4 687 bytes, inglês, `€8,000.00` e alteração pendente | **PASSOU** |
| PDF depois da decisão | Download real, `tmp/e2e/after.pdf` | 1 página, 5 079 bytes, português, `8 650,00 €`, decisão e observação | **PASSOU** |
| Integridade do relatório | `pypdf` + imagens rasterizadas | 19 páginas A4, 28 838 caracteres, 8 imagens, metadados, 0 páginas em branco | **PASSOU** |
| Integridade do manual | `pypdf` + imagens rasterizadas | 23 páginas A4, 20 402 caracteres, 17 imagens, metadados, 0 páginas em branco | **PASSOU** |
| Capturas | Inspeção das folhas de contacto dos PDFs | Formulários, tutorial, equipa, convites, perfil e registo correspondem à UI atual | **PASSOU** |
| ZIP do Moodle | `zipfile.testzip()` e listagem | 4 ficheiros, sem corrupção | **PASSOU** |
| Base de demonstração | Consulta SQL após os ensaios | Continua com 8 000,00 € e a alteração de 650,00 € **pendente**; os ensaios usaram a cópia | **PASSOU** |

O teste de browser foi o mesmo percurso de 33 passos documentado em 3.5, agora repetido
contra uma cópia limpa da demo. O formulário móvel e o tutorial a 390 px foram medidos por
`bounding_box` e `scrollWidth`; não houve overflow horizontal.

**Não executado nesta reverificação:** telemóvel físico, Firefox/Safari/Edge, leitor de
ecrã, teste com terceiros, carga e HTTPS. Continuam registados na secção 8.
