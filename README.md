# ObraClara V2 — gestão de alterações a obras

**Projeto académico — Programação Web, 12.º F, 2026/2027 · apresentação até 05/11/2026**

Aplicação web para uma empresa fictícia de remodelações. Centraliza clientes, obras,
trabalhos adicionais (alterações ao âmbito) e as decisões que alteram o orçamento,
com histórico auditável de quem decidiu o quê e quando.

> **Estado:** aplicação funcional, testada localmente (25 testes automatizados + testes HTTP reais).
> Corre localmente com PHP 8.3 e SQLite; não depende de internet, serviços externos nem APIs pagas.

---

## 1. Arranque rápido (Windows + Laragon)

Requisitos: **PHP 8.3** com as extensões `pdo_sqlite` e `sqlite3` (já incluídas no Laragon).

```bash
# Na pasta do projeto, com o PHP disponível no PATH
php -v
php -m | findstr pdo_sqlite

# 1) Criar a base de dados e o esquema
php scripts/install.php

# 2) Criar dados fictícios de demonstração
php scripts/seed.php --reset

# 3) Arrancar o servidor local
php -S localhost:8000 -t public public/router.php
```

Abrir **http://localhost:8000**.

Se `php` não estiver no PATH, usar o binário do Laragon, por exemplo:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe scripts/seed.php --reset
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -S localhost:8000 -t public public/router.php
```

> A raiz pública é **`public/`**. A base de dados fica em `database/obraclara.sqlite`,
> fora da pasta pública, e nunca é servida por URL.
>
> Com Apache/Laragon, o *DocumentRoot* tem de apontar para `public/` e `mod_rewrite`
> deve estar ativo (o `.htaccess` já está incluído). Validado com Apache 2.4.66 do
> Laragon num Virtual Host próprio, em `http://obraclara-v2.test`; o vhost automático do
> Laragon aponta para o protótipo antigo e não deve ser usado. Detalhe e configuração em
> [`docs/RESULTADOS_TESTES.md`](docs/RESULTADOS_TESTES.md), secção 3.2.
> O servidor `php -S` continua a ser a via mais rápida para a demonstração.

### Contas de demonstração (fictícias, apenas locais)

| Perfil | Email | Palavra-passe | Pode fazer |
|---|---|---|---|
| Gestor | `gestor@obraclara.pt` | `Gestor2026!` | Gerir clientes e obras, decidir alterações, ver tudo |
| Colaborador | `colaborador@obraclara.pt` | `Colab2026!` | Consultar obras, criar/editar rascunhos e submeter; **não decide** |

Estas credenciais existem apenas na base local de demonstração. Não usar dados reais.

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

- **Autenticação** com `password_hash`/`password_verify`, sessão regenerada no login,
  logout por POST com CSRF e proteção contra tentativas repetidas.
- **Perfis reais**: gestor e colaborador; permissões verificadas em cada pedido do backend,
  não apenas na interface.
- **Clientes**: listar, pesquisar, criar, editar, arquivar/reativar (sem apagar histórico).
- **Obras**: listar, filtrar por estado, pesquisar, criar, editar, arquivar/reativar,
  associar a cliente ativo, orçamento base e atualizado.
- **Alterações**: rascunho → pendente → aprovada/rejeitada; edição apenas em rascunho pelo
  autor ou gestor; submissão; decisão exclusiva do gestor com observação.
- **Orçamento atualizado** = base + soma das alterações **aprovadas**, em cêntimos inteiros.
- **Histórico imutável** de criação, edição, submissão e decisão, com utilizador, data/hora,
  transição de estado e observação. Triggers na base de dados impedem reescrever eventos.
- **Dashboard** com indicadores calculados por SQL real.
- **Interface responsiva** (menu lateral em desktop, menu deslizante em ecrãs pequenos),
  estados vazios, pesquisa sem resultados, erros por campo e mensagens de sucesso.

Fora do âmbito (não implementado de propósito): pagamentos, faturação certificada, assinatura
digital, uploads, notificações, login de clientes, multiempresa, aplicação móvel nativa.

---

## 4. Stack e arquitetura

PHP 8.3 · PDO + SQLite · HTML5 · CSS3 · JavaScript mínimo. Sem framework, sem Composer,
sem dependências externas e sem CDNs — funciona offline.

```
public/                 única raiz web
  index.php             front controller, rotas, CSRF, cabeçalhos de segurança
  router.php            router do servidor integrado
  .htaccess             fallback para Apache/Laragon
  assets/               CSS, JS e favicon
app/
  bootstrap.php         autoload, configuração, ligação à BD
  Http/                 Router e controladores
  Services/             regras de negócio e validações
  Repositories/         acesso a dados com PDO preparado
  Support/              Auth, sessão, CSRF, dinheiro, vistas, exceções
  views/                layout e páginas PHP
config/config.php       configuração e caminho da BD
database/schema.sql     esquema, constraints, índices e triggers
scripts/install.php     criação do esquema
scripts/seed.php        dados fictícios (com `--reset`)
tests/run.php           testes automatizados
docs/                   requisitos, modelo, manual, relatório, resultados
```

O fluxo é **controlador → serviço → repositório**. As regras críticas (permissões,
transições de estado, orçamento e histórico) vivem nos serviços e em transações SQL,
para não dependerem da interface.

---

## 5. Segurança

- PDO com *prepared statements* em todas as consultas com dados do utilizador.
- Escape de saída com `htmlspecialchars` (função `e()`) em todas as vistas.
- Token CSRF por sessão em todos os POST; token rodado no login.
- Sessão com cookie `HttpOnly`, `SameSite=Lax`, `use_strict_mode` e regeneração de ID.
- Autorização no backend: rotas de gestão exigem perfil `gestor`; decisões recusam
  colaboradores com HTTP 403.
- Cabeçalhos `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`,
  `Referrer-Policy` e `Cache-Control: no-store`.
- Erros internos registados no log do servidor; o utilizador vê apenas uma mensagem genérica.
- Valores monetários em cêntimos inteiros, com limites e `CHECK` na base de dados.

---

## 6. Testes

```bash
php tests/run.php
```

- 25 testes automatizados, cada um com uma base SQLite **descartável** em `tmp/`;
  os dados de demonstração nunca são usados nos testes.
- Cobrem dinheiro, XSS, autenticação, CSRF, permissões, validações, cenário 8 000 + 650,
  rejeição, múltiplas aprovações, transições inválidas, imutabilidade, arquivo,
  integridade referencial, pesquisa, persistência, dashboard e acentuação.
- Resultados reais e testes HTTP manuais em [`docs/RESULTADOS_TESTES.md`](docs/RESULTADOS_TESTES.md).

---

## 7. Documentação

- [`docs/MANUAL_UTILIZACAO.md`](docs/MANUAL_UTILIZACAO.md) — manual com capturas reais.
- [`docs/RELATORIO_PROJETO.md`](docs/RELATORIO_PROJETO.md) — relatório académico.
- [`docs/10_MODELO_RELACIONAL.md`](docs/10_MODELO_RELACIONAL.md) — modelo de dados e regras.
- [`docs/APRESENTACAO_ROTEIRO.md`](docs/APRESENTACAO_ROTEIRO.md) — roteiro de 5–8 minutos.
- [`docs/RESULTADOS_TESTES.md`](docs/RESULTADOS_TESTES.md) — evidências de testes.
- [`docs/REGISTO_IA.md`](docs/REGISTO_IA.md) — registo transparente do uso de IA.
- [`docs/08_ESTADO_E_RETOMA.md`](docs/08_ESTADO_E_RETOMA.md) — estado e próximos passos.

---

## 8. GitHub

- Repositório: <https://github.com/Sashix09r/ObraClara>
- Branch de trabalho e entrega: **`ai/obraclara-v2`**
  → <https://github.com/Sashix09r/ObraClara/tree/ai/obraclara-v2>
- A `main` não foi alterada por este trabalho.

---

## 9. Limitações conhecidas

- Aplicação local para demonstração; não está publicada na internet.
- SQLite adequado a uma única empresa e poucos utilizadores concorrentes.
- Sem envio de emails, notificações ou anexos.
- Sem autenticação de dois fatores; proteção de força bruta limitada à sessão.
- Em produção seria necessário HTTPS, cópias de segurança e gestão de utilizadores.
- O protótipo anterior e o planeamento tiveram assistência de IA paga; ver
  [`docs/REGISTO_IA.md`](docs/REGISTO_IA.md) para o esclarecimento que deve ser feito
  com o professor antes da submissão.

---

## 10. Comandos úteis

```bash
php scripts/install.php          # cria/verifica o esquema
php scripts/seed.php --reset     # recria os dados de demonstração
php tests/run.php                # testes automatizados
php -S localhost:8000 -t public public/router.php
```

O `--reset` só elimina o ficheiro SQLite configurado para o projeto (`obraclara.sqlite`);
nunca toca noutras bases de dados.
