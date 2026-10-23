# 01 — Requisitos da ObraClara

## Objetivo
Centralizar obras, alterações ao âmbito contratado e decisões sobre custos adicionais para microempresas de remodelação.

## Perfis
- **Gestor:** gere clientes/obras, aprova ou rejeita alterações depois de verificar a decisão recebida do cliente, consulta o histórico.
- **Colaborador:** consulta obras, cria alterações em rascunho e submete-as para decisão; não pode aprová-las.
- **Cliente final:** não terá conta no MVP. A decisão é registada internamente pelo gestor; não é assinatura digital.

## Requisitos funcionais
- RF01 — autenticar utilizadores;
- RF02 — gerir clientes;
- RF03 — gerir obras associadas a clientes;
- RF04 — criar, editar e consultar alterações;
- RF05 — mudar o estado de uma alteração segundo permissões;
- RF06 — calcular orçamento previsto = base + soma de alterações aprovadas;
- RF07 — pesquisar e filtrar obras/alterações;
- RF08 — registar data, autor e ação num histórico;
- RF09 — apresentar indicadores simples no dashboard.

## Regras de negócio
1. Orçamento base ≥ 0 e custo de alteração > 0.
2. Uma alteração pendente ou rejeitada não aumenta o orçamento aprovado.
3. O colaborador não pode aprovar/rejeitar alterações.
4. Alterações aprovadas não podem ser alteradas ou apagadas silenciosamente.
5. Obra e cliente associados devem existir.
6. Datas, preços e texto passam por validação no servidor.
7. Eliminações destrutivas de informação com histórico devem ser substituídas por arquivo/desativação.

## Critérios técnicos e de interface
- Persistência real em base de dados; consultas parametrizadas; palavras-passe armazenadas com hash.
- Layout responsivo, mensagens de erro claras, pesquisa sem resultados e estados vazios.
- Dados fictícios para demonstração.

## Fora do âmbito de 05/11
Pagamentos online, faturas fiscalmente válidas, assinatura digital, IA, notificações automáticas, várias empresas na mesma instalação.
