# 08 — Plano de execução autónomo e continuidade

## Estratégia para 29–30 outubro (janelas de até 7 h/dia)
Estimativas orientadoras, não promessas nem temporizadores: prioridade a concluir o fluxo completo hoje e terminar revisão/documentação amanhã; adaptar sem pedir aprovações intermédias.

| Ordem | Entrega verificável | Gate mínimo antes de passar |
|---|---|---|
| 0 | Git V2 isolado + ferramentas/diagnóstico | Branch correta; `main` intocada; detetar PHP/PDO |
| 1 | SQLite, schema, seed e utilizadores demo | FKs ligadas; reexecução seed controlada; persistência |
| 2 | Login/logout, sessões e perfis | Gestor entra; colaborador não aprova |
| 3 | Clientes e obras | CRUD real; pesquisa; validação e arquivo |
| 4 | Alterações e orçamentos | Fluxo rascunho → pendente → aprovada/rejeitada e histórico |
| 5 | Dashboard + UX | Indicadores SQL reais; responsive; estados vazios/feedback |
| 6 | Auditoria + regressão | Testes executados e falhas críticas resolvidas |
| 7 | README, manual, relatório, apresentação | Fidelidade ao que existe; capturas reais quando possível |

**Não esperar confirmação para avançar.** Assim que um gate estiver cumprido, fazer commit/push seguro e trabalhar no seguinte. Se bloqueado, documentar o problema e avançar para outro módulo não dependente. Priorizar corrigir bugs críticos antes de nova funcionalidade.

## Se a ferramenta parar por contexto, limite ou sessão
Atualizar `docs/08_ESTADO_E_RETOMA.md` e deixar uma instrução pronta para a sessão seguinte. Não inventar trabalho feito, modelos usados, testes ou pushes. Formato:

- Data/hora e branch/último SHA validado
- Fases concluídas, parciais e não iniciadas
- Ficheiros principais novos/alterados
- Testes executados + resultados efetivos
- Bloqueadores e comandos precisos
- **PRÓXIMA AÇÃO AUTÓNOMA** (uma instrução concreta para continuar)

## Qualidade e custo de tempo
- O agente faz a maioria dos testes; o aluno só precisa de confirmar uma demo essencial e explicar o projeto.
- Evitar funcionalidades sofisticadas e framework novo só para impressionar.
- Usar revisão estática independente se disponível; testes reais são condição separada.
- Manter relatórios sucintos durante a implementação e completos na documentação de entrega.
