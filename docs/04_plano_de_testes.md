# Plano de testes — execução em 29/10/2026

Os testes abaixo foram executados. O detalhe, comandos e limitações estão em
[`RESULTADOS_TESTES.md`](RESULTADOS_TESTES.md). A suíte automatizada
(`php tests/run.php`) tem 25 casos e cada um usa uma base SQLite descartável.

| Caso | Ação | Resultado esperado | Estado |
|---|---|---|---|
| T01 | Login inválido | Erro sem autenticação | **PASSOU** — 401 e mensagem clara |
| T02 | Criar cliente válido | Persistência após reabrir | **PASSOU** — T08/T09 |
| T03 | Criar obra sem cliente | Formulário rejeita | **PASSOU** — T11 |
| T04 | Alteração com valor negativo | Erro de validação | **PASSOU** — T13 e HTTP 422 |
| T05 | Submeter alteração | Estado pendente | **PASSOU** — T12/T14 |
| T06 | Utilizador colaborador tenta aprovar | Acesso recusado | **PASSOU** — T07 e HTTP 403 |
| T07 | Gestor aprova alteração | Orçamento atualizado corretamente | **PASSOU** — 8 650,00 €, T14 |
| T08 | Rejeitar alteração | Orçamento inalterado | **PASSOU** — T15 |
| T09 | Editar alteração aprovada | Modificação bloqueada/auditada | **PASSOU** — T17, trigger SQL |
| T10 | Pesquisa sem resultados | Mensagem clara | **PASSOU** — T09/T21 e interface |
| T11 | Abrir num ecrã pequeno | Layout utilizável | **PASSOU** — captura 500 px e medição a 390 px |
| T12 | Reabrir a aplicação | Dados continuam guardados | **PASSOU** — T22 |

## Não coberto por este plano

Apache/Virtual Host, telemóvel físico, Firefox/Safari, leitor de ecrã, teste de
usabilidade com colega, carga concorrente e HTTPS estão registados como
**NÃO EXECUTADO** em `RESULTADOS_TESTES.md`.
