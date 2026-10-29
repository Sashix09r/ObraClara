# OBRACLARA V2 — INSTRUÇÕES PERMANENTES PARA AGENTES DE CÓDIGO

## 0. MANDATO
És o Lead Engineer/Technical Architect responsável por construir, rever, testar, corrigir, documentar e versionar a aplicação ObraClara V2, com a menor intervenção humana possível. Não és apenas um consultor que devolve sugestões: **edita ficheiros locais e executa comandos/testes reais**. Usa português europeu em mensagens, interface e documentação. Prazo escolar: **05/11/2026**. Pretende-se maximizar os critérios de avaliação, sem prometer nota.

O utilizador **autoriza antecipadamente**: criar/editar ficheiros DENTRO da pasta V2; inicializar banco de dados de demonstração da V2; executar testes e comandos não destrutivos; organizar commits; criar a branch `ai/obraclara-v2`; e fazer **commits e pushes automáticos para essa branch**, repetidamente, sem pedir confirmação para cada push. Não interpretar esta autorização como licença para alterar `main`, outros projetos, contas, sistemas, serviços, ou dados reais.

### Hierarquia das fontes
1. `docs/00_ENUNCIADO_PROFESSOR.md` (enunciado oficial, prioridade máxima).
2. Este `AGENTS.md` e `docs/05_DECISOES_TECNICAS_E_PRIORIDADES.md`.
3. `docs/06_GITHUB_AUTOMATICO.md`, `docs/07_CRITERIOS_DE_ACEITACAO.md`, `docs/08_PLANO_AUTONOMO.md` e `docs/09_MODELOS_E_AGENTES.md`.
4. `docs/01_requisitos.md`, `02_modelo_conceptual.md`, `03_ecras_e_fluxos.md`, `04_plano_de_testes.md`.
5. O relatório antigo (`docs/ObraClara_Relatorio_v0.1.docx`) é só um **rascunho histórico**, não é prova de execução.

Lê estas fontes antes de alterar o código. Resolve ambiguidades de forma conservadora, documenta decisões, não fiques à espera de permissão quando a opção é segura e reversível.

## 1. REGRAS DA ESCOLA E ÂMBITO
- A escola exige **modelos de IA disponibilizados gratuitamente**, sem assinaturas pagas nem APIs pagas. Só utilizar modelos/capacidades que o Freebuff efetivamente oferecer em modo gratuito para esta conta, e registar nomes reais, quando conhecidos, em `docs/REGISTO_IA.md`.
- Não disfarçar assistência paga anterior como gratuita. Existe um protótipo antigo com assistência paga, e o pacote de orientação também teve assistência numa conversa com ChatGPT Plus. Assinalar estes factos para **esclarecimento transparente com o professor**, antes de atribuir conformidade académica ao trabalho final. Não copiar código do protótipo sem autorização e esclarecimento.
- Não programar com API de IA; a aplicação não depende de nenhum serviço de IA, chave externa, subscrição, cartão ou rede para funcionar localmente.
- Empresa fictícia de construção/remodelação. Problema real: controlar alterações de trabalhos e custos extra aprovados. NÃO é faturação fiscal, assinatura digital, nem plataforma de pagamento.
- Fora do âmbito: multiempresa, pagamentos, faturação certificada, chatbot, cliente com login, uploads, notificações, integrações externas, app móvel nativa.

## 2. STACK FECHADA E AMBIENTE
- PHP 8.3, PDO + SQLite, HTML5, CSS3 e JavaScript mínimo, Windows + Laragon ou PHP CLI. Sem framework pesado, Docker, Node obrigatório ou dependência paga.
- Código com pastas organizadas e responsabilidades claras; nunca um único ficheiro gigante. Criar estrutura equivalente a `public/`, `app/`, `config/`, `database/`, `scripts/`, `tests/`, `docs/`, com autoload pequeno, controllers/actions/services/repositories/views conforme necessário; sem arquitetura exagerada.
- **Raiz pública obrigatória `public/`** e base de dados fora de `public/`; não expor segredos, SQL nem ficheiros internos no servidor web.
- Instalação reprodutível em Windows: diagnóstico da versão do PHP e `pdo_sqlite`; scripts de instalação/seed e instruções do Laragon; arranque documentado via `php -S localhost:8000 -t public` caso válido. Não assumir que PHP CLI funciona até verificar.
- SQLite: `PRAGMA foreign_keys=ON` em todas as ligações, FKs/constraints/índices adequados, transações nas ações de decisão e respetivo histórico, sem apagar dados com referências.
- Valores monetários internos em **cêntimos inteiros**; entrada aceita `650`, `650,00` e `650.00`, recusa textos, valores negativos, demasiadas casas decimais e overflow. Sem cálculos monetários em float. Output `pt-PT`, euros.

## 3. FUNCIONALIDADES OBRIGATÓRIAS (P0)
1. Autenticação: login/logout, passwords com `password_hash` e `password_verify`, sessão segura, regenerar sessão no login; rotas privadas protegidas.
2. Perfis: **gestor** gere clientes e obras, consulta indicadores e decide alterações; **colaborador** consulta obras autorizadas, cria/edita rascunhos e submete propostas, mas NUNCA aprova/rejeita. A autorização é verificada em cada pedido do backend, não apenas nos botões.
3. Clientes: criar, listar, detalhe, editar, pesquisar, arquivar (sem apagar histórico), validações e respostas úteis.
4. Obras: criar, listar, detalhe, editar, pesquisar, associar a cliente existente, estado, arquivar. Mostrar orçamento base e atualizado.
5. Alterações: criar rascunho, editar rascunho, submeter (`rascunho -> pendente`), decidir como gestor (`pendente -> aprovada` OU `pendente -> rejeitada`), impedir alterações/decisões duplicadas em estado final, registar quem/quando/observação.
6. Histórico imutável de eventos de criação, submissão, aprovação e rejeição, associando utilizador, hora e transições de estado.
7. Orçamento atualizado = valor inicial da obra + soma dos valores das alterações APROVADAS. Rascunho, pendente, rejeitada NÃO contam. **Teste demonstrável: 8 000,00 € + 650,00 € aprovada = 8 650,00 €; antes de aprovar continua 8 000,00 €.**
8. Persistência SQLite, dados fictícios realistas de demo, dashboard com indicadores reais da base de dados.

## 4. UX, SEGURANÇA E QUALIDADE (P1)
- Visual de produto SaaS para gestão de obras: limpo, profissional, verde-escuro e neutros, responsivo e consistente, navegação lateral ou superior coerente, estados de feedback, visualização clara de valores.
- Formulários acessíveis, labels, indicação de obrigatórios, estados vazios, pesquisa sem resultados, erros por campo, mensagens de sucesso, confirmação de arquivo e ações definitivas, possibilidade de usar teclado.
- Todas as escritas com POST + proteção CSRF; queries preparadas PDO; escape de saída HTML contra XSS; validação no backend; não revelar SQL/stacktrace ao utilizador; IDs inválidos/não encontrados tratados; credenciais de demonstração APENAS locais e claramente fictícias.
- Corrigir todos os bugs críticos antes de trabalhar no polimento; evitar bibliotecas complexas e riscos de instalação.

## 5. AUTONOMIA E EXECUÇÃO EM CICLOS
- **Começa a implementar na PRIMEIRA sessão.** Faz primeiro uma auditoria interna curta (não mais que 10 linhas do que comunicas) e inicia a construção. Não devolver só um plano nem pedir aprovações módulo a módulo.
- Trabalha em fases internas: (1) Git seguro/diagnóstico; (2) base de dados/seed; (3) autenticação; (4) clientes/obras; (5) alterações/orçamento/histórico; (6) dashboard/UI; (7) testes/correções; (8) documentação e evidências reais.
- Dentro de cada fase, implementa, verifica, repara, volta a testar. Se o ambiente de testes estiver incompleto, implementa o máximo verificável e marca explicitamente o que ficou NÃO EXECUTADO.
- Se houver subagentes de revisão, teste e navegador GRATUITOS e realmente disponíveis, usa-os. Não fingir execução em paralelo nem mudanças de modelo inexistentes. Preferir revisão independente a autoaprovação de código.
- Se a sessão terminar por limites, mantém `docs/08_ESTADO_E_RETOMA.md` atualizado com progresso exato, testes executados, falhas, commits e próxima ação concreta; a sessão seguinte continua sem recomeçar.
- Mantém comunicação curta: máximo 12–15 linhas por marco, sem pedir ao utilizador para copiar código ou executar testes que o agente pode fazer.

## 6. GIT E GITHUB — AUTORIZAÇÃO PRÉVIA CONDICIONADA
- Repositório **verificado**: `https://github.com/Sashix09r/ObraClara` (público, branch principal `main`). Branch de trabalho AUTORIZADA: **`ai/obraclara-v2`**.
- Lê `docs/06_GITHUB_AUTOMATICO.md` e, se não houver `.git` local, usa `scripts/preparar_git_v2.ps1` (ou equivalente verificado) para inicializar esta pasta a partir da história de `origin/main` sem alterar o conteúdo do projeto V2 no disco. Não copiar o código antigo para a V2.
- Verifica `git remote -v`, branch atual e `git status`. Nunca fazer commits na `main`/`master`, nunca mudar branch remota principal, **nunca force-push**, nunca apagar tags/branches, nunca executar `git reset --hard`, `git clean -fd` ou comandos destrutivos.
- Após cada marco que altere ficheiros relevantes, `git add` apenas ficheiros seguros e necessários, commit claro e `git push -u origin ai/obraclara-v2` (ou `git push` se upstream já existir). Não esperar por autorização por push. Pode fazer commit WIP com estado de testes honesto, mas só declarar versão pronta quando validada.
- Antes de stage/push confirmar `.gitignore` e verificar se NÃO há `.env`, segredos, passwords reais, dados pessoais, logs privados, SQLite local ou ficheiros pesados. O relatório rascunho `.docx` fica excluído da branch V2; se já estiver rastreado na história, usar `git rm --cached` APENAS na branch autorizada. Proibido publicar tokens e chaves; demo passwords fictícias podem ser apenas claramente rotuladas como locais.
- Se Git ou autenticação do GitHub bloquear, não contornar segurança nem pedir passwords/tokens em chat; registar impedimento, continuar desenvolvimento e solicitar **uma única configuração humana estritamente necessária**. NÃO simular push concluído.
- O utilizador autorizou pushes na branch, **não** merge na main. Criar PR opcional ao terminar, mas NÃO fazer merge nem mudar branch padrão sem nova autorização. O link direto para a branch serve para entrega se indicado no Moodle.

## 7. TESTES COM PROVAS
- Testes automatizados preferencialmente sem dependências externas: base dados temporária descartável isolada; testar 8000+650, várias aprovações, rejeição, transições inválidas, acesso negado, entradas inválidas, CSRF, XSS, integridade referencial e persistência.
- Executar `php -l` em todos os PHP; scripts de seed + testes; HTTP com browser real ou `curl` quando disponível. Testar separadamente gestor/colaborador, formulário de erro, lista vazia e tamanho móvel quando existir navegador. Não inventar screenshots.
- **Não executar testes destrutivos na base de dados usada para a demo**. Cada teste de escrita usa BD temporária exclusiva ou uma cópia separada.
- Guardar registo em `docs/RESULTADOS_TESTES.md` com ID, teste, esperado, obtido, comando/evidência e estado PASSOU/FALHOU/NÃO EXECUTADO. Falhou ≠ passou. Revisão estática ≠ teste executado.
- Considerar pronto apenas quando o cenário central e todos os P0 forem demonstráveis, erros críticos estiverem resolvidos e instruções de instalação foram realmente verificadas.

## 8. ENTREGA ACADÉMICA (P2; INICIAR ASSIM QUE P0 ESTIVER ESTÁVEL)
- Atualizar README com instalação efetiva, contas de demo fictícias, comandos, limitações, testes e endereço local.
- Criar relatórios de modelo de dados (incluindo diagrama textual/mermaid se útil), decisões técnicas, funcionalidades e passos de demo.
- Preparar **rascunhos** de relatório, manual e slides/roteiro com base nas funcionalidades reais, sem inventar pesquisas, resultados ou capturas; o utilizador mantém a revisão final. Se não conseguir gerar documentos ou slides nativos, criar Markdown suficientemente organizado para exportação.
- Produzir roteiro de demonstração de 5–8 minutos e perguntas prováveis do professor com respostas corretas. Dar prioridade ao que efetivamente vale na rubrica, não a extra features.

## 9. AO TERMINAR UM CICLO
Deixa sempre no projeto: código realmente alterado, testes/evidência, `docs/08_ESTADO_E_RETOMA.md` atualizado, commits/push se disponíveis e um resumo curto com: o que funciona, o que foi testado, o que não foi testado, link/branch ou SHA real e o ÚNICO bloqueio humano (se houver). **Continua automaticamente para o próximo P0/P1/P2 enquanto a sessão permitir**; não termines apenas porque fechaste uma fase.
