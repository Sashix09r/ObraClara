# Decisões aprovadas para a ObraClara V2 — 29/10/2026

## Autoridade e documentos
1. `docs/00_ENUNCIADO_PROFESSOR.md` — autoridade máxima quanto a objetivos, entrega e uso de IA.
2. `AGENTS.md` — decisões atuais e critérios de execução do MVP, subordinados ao enunciado.
3. `docs/01_requisitos.md`, `docs/02_modelo_conceptual.md`, `docs/03_ecras_e_fluxos.md`, `docs/04_plano_de_testes.md` — detalhe do domínio e casos principais.
4. `docs/ObraClara_Relatorio_v0.1.docx` — rascunho académico, **não** evidência de testes ou funcionalidades executadas.

## Resolução de inconsistências
- O README inicial do pacote de 23/10 mencionava MySQL/MariaDB e XAMPP; essa indicação é obsoleta nesta V2.
- Nesta versão a stack definida é **PHP 8.3, SQLite com PDO, HTML, CSS e JavaScript mínimo**, com execução local em Windows/Laragon (ou servidor integrado do PHP usando a pasta `public` como raiz).
- O modelo conceptual refere «decimal exato»; em SQLite a representação recomendada é **cêntimos como inteiros** para evitar erros de arredondamento.
- `src/.gitkeep` no pacote inicial está vazio: este pacote contém planeamento, **não código executável**.
- Um protótipo anterior existe noutro local; **não o apagar nem modificar**.

## Ordem rígida de prioridade
P0: base de dados persistente, login/logout, papéis, CRUD de clientes/obras, alterações com estados, aprovação/rejeição, orçamento exato, histórico.
P1: dashboard com SQL real, pesquisa, feedback/formulários, UI profissional e responsiva, dados de demonstração, testes automatizados e manuais.
P2: README instalável, relatório, manual com capturas reais, apresentação, evidências e registo do uso de IA.
Não implementar pagamentos, faturação real, notificações, assinatura digital, integrações externas, multiempresa ou app móvel.

## Cenário principal obrigatório
Orçamento de obra 8 000 €, mudança de pavimento +650 €.
- Em rascunho ou pendente: orçamento atualizado 8 000 €.
- Se rejeitada: orçamento atualizado 8 000 €.
- Se aprovada por gestor: orçamento atualizado 8 650 €.
- Guardar autor, data e hora da decisão e evento no histórico.

## Regra de integridade académica
Usar **exclusivamente funcionalidades e modelos disponibilizados de forma gratuita**. Não recorrer a subscrições, créditos comprados ou API paga. Anotar modelos/ferramentas reais em `docs/REGISTO_IA.md` sem inventar; esclarecer com o professor a assistência paga anterior (protótipo e planeamento) e não declarar conformidade total antes da resposta. Ver `docs/ESCLARECIMENTO_IA_PROFESSOR.md`. A utilização de ideias gerais não deve ser apresentada como código inteiramente produzido sem assistência.
