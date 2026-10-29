# ObraClara V2 — aplicação de gestão de alterações a obras

**Projeto académico — Programação Web, 12.º F, 2026/2027.** Apresentação até 05/11/2026.

> **Estado em 29/10/2026:** pacote de arranque e instruções para o Freebuff. A pasta `src` inicial está vazia. **Ainda não existe uma aplicação V2 funcional neste ZIP, nem testes V2 executados ou commits V2 publicados**; o agente deverá atualizar este README de forma fiel durante o desenvolvimento.

## Começar
1. Ler `GUIA_COMECAR_AQUI.md`.
2. Abrir esta pasta no Freebuff Desktop e colar `PROMPT_INICIAL_PARA_COLAR.txt`.
3. `AGENTS.md` contém requisitos e regras de execução persistentes.

## Resumo do problema
Pequenas empresas de remodelação precisam de gerir pedidos de trabalhos adicionais, custos e respetivas decisões sem perder o controlo do orçamento. A ObraClara associa clientes, obras, alterações e histórico de aprovações.

## Stack pretendida
PHP 8.3, PDO + SQLite, HTML5/CSS3/JavaScript, Windows/Laragon. Os montantes são guardados como cêntimos inteiros. A raiz do servidor será `public/` e a base de dados ficará fora da pasta pública.

## GitHub
Repositório: https://github.com/Sashix09r/ObraClara

- Branch principal `main` — preservada.
- Branch autorizada `ai/obraclara-v2` — a criar pelo agente e usar para commits/pushes automáticos.
- Instruções: `docs/06_GITHUB_AUTOMATICO.md` e `scripts/preparar_git_v2.ps1`.

## Documentação
- Enunciado: `docs/00_ENUNCIADO_PROFESSOR.md`.
- Requisitos iniciais: `docs/01_requisitos.md` a `docs/04_plano_de_testes.md`.
- Decisões: `docs/05_DECISOES_TECNICAS_E_PRIORIDADES.md`.
- Git automático: `docs/06_GITHUB_AUTOMATICO.md`.
- Checklist avaliação: `docs/07_CRITERIOS_DE_ACEITACAO.md`.
- Etapas: `docs/08_PLANO_AUTONOMO.md` e `docs/08_ESTADO_E_RETOMA.md`.
- IA: `docs/REGISTO_IA.md`.

## Instalação, testes e credenciais fictícias
**Por implementar e validar pelo agente**. Não inventar comandos, credenciais nem resultados enquanto a aplicação não existir.

## Integridade académica
O professor exige IA gratuita. Houve assistência anterior com IA paga; ver `docs/REGISTO_IA.md` para esclarecimento transparente antes da submissão.
