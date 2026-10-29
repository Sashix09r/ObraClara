# RESULTADOS DOS TESTES — ObraClara V2

> Documento preparado em 29/10/2026. Nenhum teste da V2 foi executado neste pacote. O agente tem de preencher resultados apenas quando os executar.

| ID | Cenário | Esperado | Obtido | Comando/evidência real | Estado |
|---|---|---|---|---|---|
| T01 | Login válido e inválido | Acesso permitido/recusado conforme credenciais | Por executar | — | NÃO EXECUTADO |
| T02 | Clientes CRUD e arquivo | Dados persistentes e validações adequadas | Por executar | — | NÃO EXECUTADO |
| T03 | Obras CRUD e relação cliente | Obra associada a cliente válido | Por executar | — | NÃO EXECUTADO |
| T04 | Rascunho/pendente | Não altera orçamento aprovado | Por executar | — | NÃO EXECUTADO |
| T05 | Aprovação 8 000 + 650 | Orçamento 8 650 € e evento no histórico | Por executar | — | NÃO EXECUTADO |
| T06 | Rejeição | Orçamento base mantém-se | Por executar | — | NÃO EXECUTADO |
| T07 | Permissões colaborador | Decidir alteração proibido no backend | Por executar | — | NÃO EXECUTADO |
| T08 | Transições e edição final | Aprovar de novo/editar aprovada rejeitado | Por executar | — | NÃO EXECUTADO |
| T09 | Valores, campos, IDs, FKs | Validação consistente e sem dados órfãos | Por executar | — | NÃO EXECUTADO |
| T10 | CSRF/SQL Injection/XSS | Pedidos não autorizados bloqueados e output seguro | Por executar | — | NÃO EXECUTADO |
| T11 | Persistência após reinício | Dados mantidos em SQLite | Por executar | — | NÃO EXECUTADO |
| T12 | Dashboard, pesquisa, mobile | UI consistente, estados vazios e contagens reais | Por executar | — | NÃO EXECUTADO |

**Instrução:** substituir «Por executar» com observações reais, não inventar screenshots e usar BD de teste separada.
