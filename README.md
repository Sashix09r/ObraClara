# ObraClara V2 — gestão de alterações a obras

**Projeto académico — Programação Web, 12.º F, 2026/2027 · apresentação até 05/11/2026**

Aplicação web para uma empresa fictícia de remodelações. Centraliza clientes, obras,
trabalhos adicionais (alterações ao âmbito) e as decisões que alteram o orçamento,
com histórico auditável de quem decidiu o quê e quando.

> **Estado:** aplicação funcional, bilingue (PT/EN), testada localmente com
> **38 testes automatizados**, **37 verificações HTTP** e **33 verificações de browser**.
> Corre com PHP 8.3 e SQLite; não depende de internet, serviços externos, APIs pagas ou
> envio de emails.

---

## 1. Arranque rápido (Windows + Laragon)

Requisitos: **PHP 8.3** com as extensões `pdo_sqlite` e `sqlite3` (incluídas no Laragon).

```bash
# Na pasta do projeto, com o PHP disponível no PATH
php -v
php -m | findstr pdo_sqlite

# 1) Criar a base de dados e aplicar migrações
php scripts/install.php

# 2) Criar dados fictícios de demonstração
php scripts/seed.php --reset

# 3) Arrancar o servidor local
php -S localhost:8000 -t public public/router.php
```

Abrir **http://localhost:8000**.

Se `php` não estiver no PATH, usar o binário do Laragon:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe scripts/install.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe scripts/seed.php --reset
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -S localhost:8000 -t public public/router.php
```

> A raiz pública é **`public/`**. A base de dados fica em `database/obraclara.sqlite`,
> fora da pasta pública, e nunca é servida por URL.
>
> Com Apache/Laragon, o *DocumentRoot* tem de apontar para `public/` e o `mod_rewrite`
> deve estar ativo (o `.htaccess` já está incluído). Validado com Apache 2.4.66 do
> Laragon num Virtual Host próprio, em `http://obraclara-v2.test`. O vhost automático
> do Laragon aponta para o protótipo antigo e não deve ser usado. Detalhes em
> [`docs/RESULTADOS_TESTES.md`](docs/RESULTADOS_TESTES.md), secção 3.2.

### Contas de demonstração (fictícias, apenas locais)

| Perfil | Email | Palavra-passe | Pode fazer |
|---|---|---|---|
| Gestor | `gestor@obraclara.pt` | `Gestor2026!` | Gerir clientes, obras e equipa, decidir alterações, exportar PDF |
| Colaborador | `colaborador@obraclara.pt` | `Colab2026!` | Consultar obras, criar/editar rascunhos e submeter; **não decide** |

O bloco de credenciais só aparece no modo local de demonstração. Não usar dados reais.

---

## 2. Cenário de aceitação

Na obra **“Remodelação T3 — Rua das Flores”** (cliente Maria Sousa):

1. Orçamento base: **8 000,00 €**.
2. Existe a alteração **“Substituição do pavimento da sala”** de **650,00 €**, pendente.
3. Enquanto rascunho ou pendente, o orçamento atualizado continua **8 000,00 €**.
4. Se for rejeitada, continua **8 000,00 €**.
5. Se o gestor aprovar, passa a **8 650,00 €** (8 000 + 650).
6. A decisão regista gestor, data/hora e observação no histórico.
7. O colaborador que tente aprovar por pedido direto recebe **403 — sem permissão**.

Evidência visual: [`docs/capturas/04-obra-8000.png`](docs/capturas/04-obra-8000.png) (antes)
e [`docs/capturas/11-obra-8650.png`](docs/capturas/11-obra-8650.png) (depois).

---

## 3. Funcionalidades

### Contas e equipa

- **Autenticação** com `password_hash`/`password_verify`, sessão regenerada no login,
  logout por POST com CSRF e bloqueio temporário após tentativas repetidas.
- **Criação de conta apenas por convite**: o gestor define email, perfil e validade;
  o sistema gera um token aleatório de 64 caracteres, guarda apenas o hash SHA-256 e
  permite utilização única. Links inválidos, expirados, revogados ou já usados são
  recusados com mensagem própria.
- **Equipa** (só gestor): pesquisa de utilizadores, perfis, estado ativo/inativo,
  convites pendentes, revogação, alteração de perfil e ativação/desativação.
- **Proteções**: um gestor não desativa a própria conta; não é possível remover o
  último gestor ativo; contas desativadas perdem o acesso mesmo com sessão antiga.
- **O meu perfil**: nome, email, perfil, estado, data de criação, alteração de dados,
  alteração de palavra-passe (com confirmação da atual), idioma e reposição do tutorial.
- **Auditoria administrativa** em `admin_events`: responsável, ação, alvo, nota e data.
  Nunca são registadas palavras-passe nem tokens completos.

### Gestão de obra

- **Clientes**: listar, pesquisar, criar, editar, arquivar/reativar (sem apagar histórico).
- **Obras**: listar, filtrar por estado, pesquisar, criar, editar, arquivar/reativar,
  associar a cliente ativo, orçamento base e atualizado.
- **Alterações**: rascunho → pendente → aprovada/rejeitada; edição apenas em rascunho pelo
  autor ou gestor; submissão; decisão exclusiva do gestor com observação.
- **Orçamento atualizado** = base + soma das alterações **aprovadas**, em cêntimos inteiros.
- **Histórico imutável** de criação, edição, submissão e decisão, com utilizador, data/hora,
  transição de estado e observação. Triggers na base de dados impedem reescrever eventos.
- **Dashboard** com indicadores calculados por SQL real.
- **Exportação PDF real** de cada obra: identidade ObraClara, cliente, estado, datas,
  orçamentos, alterações, decisões e nota de que não é fatura nem contrato. Gerado
  localmente, sem bibliotecas externas, com acentuação portuguesa e paginação.

### Experiência

- **Interface bilingue PT/EN** com dicionários centralizados (`lang/pt.php`,
  `lang/en.php`); a preferência fica associada à conta e o atributo `lang` acompanha.
- **Tutorial interativo contextual** com boas-vindas, destaque de elementos reais,
  progresso, teclado, Escape, “Ignorar” e reposição no perfil. O estado é guardado por
  utilizador; colaboradores não recebem passos exclusivos de gestores.
- **Interface responsiva** (320–1440 px), menu deslizante, tabelas em cartões, estados
  vazios, pesquisa sem resultados, erros por campo, mensagens de sucesso e confirmações.
- **Acessibilidade**: `skip-link`, foco visível, labels, `aria-invalid`, botões com nome
  acessível, contraste e `prefers-reduced-motion`.

Fora do âmbito (não implementado de propósito): pagamentos, faturação certificada,
assinatura digital, envio de emails, uploads, notificações, login de clientes,
multiempresa, chat, IA em execução e aplicação móvel nativa.

---

## 4. Stack e arquitetura

PHP 8.3 · PDO + SQLite · HTML5 · CSS3 · JavaScript mínimo. Sem framework, sem Composer,
sem dependências externas e sem CDNs — funciona offline.

```
public/                    única raiz web
  index.php                front controller, rotas, CSRF, cabeçalhos de segurança
  router.php               router do servidor integrado
  .htaccess                fallback para Apache/Laragon
  assets/                  CSS, JS e favicon
app/
  bootstrap.php            autoload, configuração, ligação à BD
  Http/                    Router e controladores (Auth, Team, Profile, ...)
  Services/                regras de negócio, convites, conta, PDF
  Repositories/            acesso a dados com PDO preparado
  Support/                 Auth, sessão, CSRF, dinheiro, i18n, migrações, PDF, vistas
  views/                   layout e páginas PHP
config/config.php          configuração e caminho da BD
database/schema.sql        esquema, constraints, índices e triggers
scripts/install.php        criação do esquema e migrações (com cópia de segurança)
scripts/seed.php           dados fictícios (com `--reset`)
tests/run.php              testes automatizados
docs/                      requisitos, modelo, manual, relatório, resultados
```

O fluxo é **controlador → serviço → repositório**. As regras críticas (permissões,
transições de estado, orçamento, convites e histórico) vivem nos serviços e em transações
SQL, para não dependerem da interface.

---

## 5. Base de dados e migrações

Tabelas: `users`, `clients`, `projects`, `changes`, `change_events`, `invites`,
`admin_events` e `schema_migrations`.

- Valores monetários em **cêntimos inteiros**, com `CHECK` de positividade e limite.
- `PRAGMA foreign_keys = ON` em todas as ligações.
- Arquivo lógico (`active`, estado `arquivada`), sem eliminar histórico.
- Triggers protegem alterações decididas e eventos de histórico.
- **Migrações incrementais e idempotentes** em `app/Support/Migrator.php`. O
  `scripts/install.php` cria uma cópia de segurança antes de aplicar versões pendentes;
  não é necessário `seed.php --reset` para atualizar a estrutura.

---

## 6. Segurança

- PDO com *prepared statements* em todas as consultas com dados do utilizador.
- Escape de saída com `htmlspecialchars` (função `e()`) em todas as vistas.
- Token CSRF por sessão em todos os POST; token rodado no login e após alteração de palavra-passe.
- Sessão com cookie `HttpOnly`, `SameSite=Lax`, `use_strict_mode` e regeneração de ID.
- Autorização no backend: rotas de gestão exigem perfil `gestor`; decisões recusam
  colaboradores com HTTP 403. O perfil de um convite nunca pode ser elevado pelo formulário.
- Tokens de convite com 256 bits de entropia; só o hash SHA-256 é guardado.
- Cabeçalhos `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`,
  `Referrer-Policy` e `Cache-Control: no-store`.
- Erros internos registados no log do servidor; o utilizador vê apenas uma mensagem genérica.
- Valores monetários em cêntimos inteiros, com limites e `CHECK` na base de dados.

---

## 7. Testes

```bash
php tests/run.php
```

- **38 testes automatizados**, cada um com uma base SQLite **descartável** em `tmp/`;
  os dados de demonstração nunca são usados nos testes.
- Cobrem dinheiro, XSS, autenticação, CSRF, permissões, validações, cenário 8 000 + 650,
  rejeição, múltiplas aprovações, transições inválidas, imutabilidade, arquivo,
  integridade referencial, pesquisa, persistência, dashboard, acentuação, convites,
  registo, último gestor, perfil, palavra-passe, PDF, traduções, tutorial e migrações.
- Resultados reais, 37 verificações HTTP, 33 verificações de browser e limitações em
  [`docs/RESULTADOS_TESTES.md`](docs/RESULTADOS_TESTES.md).

---

## 8. Documentação

- [`entregas/`](entregas/) — pacote para o Moodle: relatório e manual em PDF,
  apresentação em PPTX e PDF, e [LEIA-ME](entregas/README.md) com a lista e as verificações.
- [`docs/MANUAL_UTILIZACAO.md`](docs/MANUAL_UTILIZACAO.md) — manual com capturas reais.
- [`docs/RELATORIO_PROJETO.md`](docs/RELATORIO_PROJETO.md) — relatório académico.
- [`docs/10_MODELO_RELACIONAL.md`](docs/10_MODELO_RELACIONAL.md) — modelo de dados e regras.
- [`docs/APRESENTACAO_ROTEIRO.md`](docs/APRESENTACAO_ROTEIRO.md) — roteiro de 5–8 minutos.
- [`docs/RESULTADOS_TESTES.md`](docs/RESULTADOS_TESTES.md) — evidências de testes.
- [`docs/REGISTO_IA.md`](docs/REGISTO_IA.md) — registo transparente do uso de IA.
- [`docs/ESCLARECIMENTO_IA_PROFESSOR.md`](docs/ESCLARECIMENTO_IA_PROFESSOR.md) — mensagem
  ao professor e plano de transparência sobre a IA paga anterior.
- [`docs/08_ESTADO_E_RETOMA.md`](docs/08_ESTADO_E_RETOMA.md) — estado e próximos passos.

---

## 9. GitHub

- Repositório: <https://github.com/Sashix09r/ObraClara>
- Branch de trabalho e entrega: **`ai/obraclara-v2`**
  → <https://github.com/Sashix09r/ObraClara/tree/ai/obraclara-v2>
- A `main` não foi alterada por este trabalho. Não há merge automático.

---

## 10. Limitações conhecidas

- Aplicação local para demonstração; não está publicada na internet.
- SQLite adequado a uma única empresa e poucos utilizadores concorrentes.
- Sem envio de emails: o link de convite é copiado e entregue manualmente.
- Sem autenticação de dois fatores; proteção de força bruta limitada à sessão.
- Em produção seria necessário HTTPS, cópias de segurança e revisão de políticas de
  retenção de dados.
- O teste num telemóvel físico não foi executado pelo agente; a emulação de viewport
  entre 320 e 1440 px foi executada e está documentada.
- O protótipo anterior e o planeamento tiveram assistência de IA paga. O esclarecimento
  com o professor foi comunicado pelo aluno; não existe autorização escrita anexada.
  O facto está declarado em [`docs/REGISTO_IA.md`](docs/REGISTO_IA.md).

---

## 11. Comandos úteis

```bash
php scripts/install.php          # cria/verifica o esquema e aplica migrações
php scripts/seed.php --reset     # recria os dados de demonstração
php tests/run.php                # 38 testes automatizados
php -S localhost:8000 -t public public/router.php
```

O `--reset` só elimina o ficheiro SQLite configurado para o projeto (`obraclara.sqlite`);
nunca toca noutras bases de dados. Para atualizar uma base existente sem perder dados,
usar `install.php`, que faz uma cópia de segurança em `database/backups/` antes de migrar.
