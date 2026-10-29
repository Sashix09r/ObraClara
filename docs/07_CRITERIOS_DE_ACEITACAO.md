# 07 — Critérios de aceitação (estado real em 29/10/2026)

O enunciado não atribui pontos numéricos por dimensão. Esta lista cobre as cinco
dimensões e só está assinalada com evidência real. O que depende de revisão humana ou de
apresentação continua em aberto.

## A. Conteúdo — aplicação real

- [x] Problema de uma empresa fictícia de remodelações fundamentado
- [x] Modelo relacional persistente: `users`, `clients`, `projects`, `changes`,
      `change_events`, `invites`, `admin_events` e `schema_migrations`
- [x] CRUD de clientes com pesquisa e arquivo
- [x] CRUD de obras e associação a cliente ativo
- [x] Gestor e colaborador com permissões reais no servidor (HTTP 403 testado)
- [x] Criação de contas apenas por convite, com token de uso único e expiração
- [x] Gestão de equipa: pesquisa, perfis, ativação/desativação e auditoria
- [x] Perfil pessoal com nome, email, palavra-passe e idioma
- [x] Proteção do último gestor ativo e da própria conta
- [x] Propostas com rascunho, pendente, aprovada e rejeitada
- [x] Aprovação soma o custo; rejeição não soma (testes T14/T15)
- [x] Histórico de quem decidiu, quando e com observação
- [x] Exportação PDF real, autenticada, com valores e acentos
- [x] Tutorial contextual, com estado por utilizador
- [x] Interface PT/EN com preferência persistente
- [x] Dados fictícios de demonstração e cenário 8 000 € + 650 €
- [x] Validações, integridade, CSRF, SQL Injection e XSS tratados e testados
- [x] Migrações incrementais com cópia de segurança
- [x] Código acessível no GitHub e README com instalação testada

## B. Interface

- [x] Navegação clara, botões com ações reais e menu responsivo
- [x] Visual profissional, identidade verde-escura coerente
- [x] Desktop e viewports 320–1440 px utilizáveis; sem scroll horizontal
- [x] Estados vazios, pesquisas sem resultados, erros e confirmações
- [x] Tutorial com teclado, Escape, progresso e reposição
- [x] Seletor PT/EN e conteúdo traduzido nos fluxos principais
- [ ] Teste de usabilidade com um colega sem explicações — **NÃO EXECUTADO**
- [ ] Telemóvel físico — **NÃO EXECUTADO** (emulação feita)

## C. Manual

- [x] Procedimentos reais de instalação, login, convite, perfil, tutorial, idioma,
      clientes, obras, decisões, PDF e mobile
- [x] 17 capturas reais dos ecrãs finais em `docs/capturas/`
- [x] Resolução de erros frequentes e forma de repor a demonstração
- [x] Manual em PDF regenerado (23 páginas)

## D. Relatório

- [x] Contexto do setor com fontes verificáveis (INE, IMPIC)
- [x] Objetivos, público-alvo, requisitos, tecnologias e justificações
- [x] Esquema de BD, relações, migrações, arquitetura e regras de negócio
- [x] Descrição fiel do que funciona, 38 testes, 37 verificações HTTP e dificuldades
- [x] Transparência sobre IA, incluindo o histórico pago e o esclarecimento comunicado
- [x] Limitações e melhorias futuras
- [x] Relatório em PDF regenerado (19 páginas)
- [ ] Revisão final pelo aluno — **PENDENTE HUMANO**

## E. Apresentação

- [x] Roteiro de 5–8 minutos com demonstração real, atualizado com contas, tutorial,
      idioma e PDF ([`APRESENTACAO_ROTEIRO.md`](APRESENTACAO_ROTEIRO.md))
- [x] Explicação de BD, CRUD, restrições, cálculo e tecnologia
- [x] Plano de recuperação para falha de servidor
- [x] Perguntas prováveis com respostas corretas
- [ ] Ensaio cronometrado em voz alta e apresentação à turma — **PENDENTE HUMANO**

## F. Pacote de entrega

- [x] Relatório em PDF A4, 19 páginas, com texto pesquisável e índice clicável
- [x] Manual em PDF A4, 23 páginas, com 17 capturas reais
- [x] Apresentação-base em PPTX (10 slides, 16:9, notas do orador) e cópia PDF;
      a versão final será preparada pelo aluno em Canva
- [x] Metadados, paginação e verificação de páginas em branco
- [x] ZIP único para o Moodle, com lista e somas SHA-256
- [ ] Nome do aluno na capa, se exigido, e revisão final antes da submissão — **PENDENTE HUMANO**

## Evidência principal

- [`RESULTADOS_TESTES.md`](RESULTADOS_TESTES.md) — 38/38 testes, 37 verificações HTTP,
  Apache, responsividade e limitações.
- [`MANUAL_UTILIZACAO.md`](MANUAL_UTILIZACAO.md) — manual com capturas.
- [`RELATORIO_PROJETO.md`](RELATORIO_PROJETO.md) — relatório.
- [`10_MODELO_RELACIONAL.md`](10_MODELO_RELACIONAL.md) — modelo de dados.
- [`REGISTO_IA.md`](REGISTO_IA.md) — registo de IA.
- [`08_ESTADO_E_RETOMA.md`](08_ESTADO_E_RETOMA.md) — estado e próximos passos.
- [`../entregas/README.md`](../entregas/README.md) — pacote PDF/PPTX e verificações.
