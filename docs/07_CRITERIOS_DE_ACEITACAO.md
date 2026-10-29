# 07 — Critérios de aceitação (estado real em 29/10/2026)

O enunciado não atribui pontos numéricos por dimensão. Esta lista cobre as cinco
dimensões e só está assinalada com evidência real. O que depende de revisão humana ou de
apresentação continua em aberto.

## A. Conteúdo — aplicação real

- [x] Problema de uma empresa fictícia de remodelações fundamentado
- [x] Modelo relacional persistente (`users`, `clients`, `projects`, `changes`, `change_events`)
- [x] CRUD de clientes com pesquisa e arquivo
- [x] CRUD de obras e associação a cliente ativo
- [x] Gestor e colaborador com permissões reais no servidor (HTTP 403 testado)
- [x] Propostas com rascunho, pendente, aprovada e rejeitada
- [x] Aprovação soma o custo; rejeição não soma (testes T14/T15)
- [x] Histórico de quem decidiu, quando e com observação
- [x] Dados fictícios de demonstração e cenário 8 000 € + 650 €
- [x] Validações, integridade, CSRF, SQL Injection e XSS tratados e testados
- [x] Código acessível no GitHub e README com instalação testada

## B. Interface

- [x] Navegação clara, botões com ações reais e menu responsivo
- [x] Visual profissional, identidade verde-escura coerente
- [x] Desktop e ecrã compacto utilizáveis; contraste, labels e mensagens
- [x] Estados vazios, pesquisas sem resultados, erros e confirmações
- [ ] Teste de usabilidade com um colega sem explicações — **NÃO EXECUTADO**

## C. Manual

- [x] Procedimentos reais de login, clientes, obras e decisões
- [x] Capturas reais dos ecrãs finais em `docs/capturas/`
- [x] Resolução de erros frequentes e forma de iniciar a aplicação

## D. Relatório

- [x] Contexto do setor com fontes verificáveis (INE, IMPIC)
- [x] Objetivos, público-alvo, requisitos, tecnologias e justificações
- [x] Esquema de BD, relações, arquitetura e regras de negócio
- [x] Descrição fiel do que funciona, testes executados e dificuldades
- [x] Transparência sobre IA, incluindo histórico pago a esclarecer
- [x] Limitações e melhorias futuras
- [ ] Revisão final pelo aluno e esclarecimento com o professor — **PENDENTE HUMANO**

## E. Apresentação

- [x] Roteiro de 5–8 minutos com demonstração real ([`APRESENTACAO_ROTEIRO.md`](APRESENTACAO_ROTEIRO.md))
- [x] Explicação de BD, CRUD, restrições, cálculo e tecnologia
- [x] Plano de recuperação para falha de servidor
- [x] Perguntas prováveis com respostas corretas
- [ ] Ensaio cronometrado e apresentação à turma — **PENDENTE HUMANO**

## Evidência principal

- [`RESULTADOS_TESTES.md`](RESULTADOS_TESTES.md) — 25/25 testes, HTTP e limitações.
- [`MANUAL_UTILIZACAO.md`](MANUAL_UTILIZACAO.md) — manual com capturas.
- [`RELATORIO_PROJETO.md`](RELATORIO_PROJETO.md) — relatório.
- [`10_MODELO_RELACIONAL.md`](10_MODELO_RELACIONAL.md) — modelo de dados.
- [`08_ESTADO_E_RETOMA.md`](08_ESTADO_E_RETOMA.md) — estado e próximos passos.
