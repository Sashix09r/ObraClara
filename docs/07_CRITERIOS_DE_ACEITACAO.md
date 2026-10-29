# 07 — Critérios de aceitação alinhados com o professor

O enunciado NÃO atribui pontos numéricos individuais a cada dimensão. Não inventar uma distribuição de 200 pontos. O objetivo é cobrir as cinco dimensões indicadas: conteúdo, apresentação, interface, manual e relatório.

## A. Conteúdo — aplicação real
- [ ] Problema de uma empresa fictícia de remodelações fundamentado
- [ ] Modelo relacional persistente (utilizadores, clientes, obras, alterações, eventos)
- [ ] CRUD de clientes com pesquisa e arquivo
- [ ] CRUD de obras e associação a clientes
- [ ] Gestor e colaborador com permissões reais do lado do servidor
- [ ] Propostas com rascunho, pendente, aprovada e rejeitada
- [ ] Aprovação soma custo adicional à obra; rejeição não soma
- [ ] Histórico de quem tomou uma decisão e quando
- [ ] Dados fictícios de demonstração e cenário dos 8 000 € + 650 €
- [ ] Validações, integridade, proteção contra CSRF/SQL Injection/XSS
- [ ] Código GitHub acessível e README para instalação local

## B. Interface
- [ ] Navegação clara e consistente; botões com ações reais
- [ ] Visual profissional e adequado ao domínio
- [ ] Desktop e mobile utilizáveis; contraste, labels e mensagens
- [ ] Estados vazios, pesquisas sem resultados, erros e confirmação

## C. Manual
- [ ] Procedimentos reais de login, clientes, obras e decisões
- [ ] Capturas reais dos ecrãs finais (nunca mockups fabricados)
- [ ] Resolução de erros frequentes e como iniciar a aplicação

## D. Relatório
- [ ] Pesquisa do setor e fontes verificáveis
- [ ] Objetivos, público-alvo, requisitos, tecnologias e justificações
- [ ] Esquema de BD, relações, arquitetura e regras de negócio
- [ ] Descrição fiel do que funciona, testes executados e dificuldades
- [ ] Transparência sobre IA usada, incluindo histórico a esclarecer
- [ ] Limitações e melhorias futuras

## E. Apresentação
- [ ] 5–8 min de narrativa clara com demonstração real
- [ ] Explicar BD, CRUD, restrições, cálculo de orçamento e tecnologia
- [ ] Roteiro de recuperação se houver falha de internet/servidor
- [ ] Questões/respostas sobre segurança, testes e uso da IA

## Prioridade de execução
- **P0:** Conteúdo funcional e persistente, cenário central, segurança e dados de demo.
- **P1:** Interface excelente, testes e correções.
- **P2:** Manual, relatório, GitHub e apresentação, SEM os deixar para o último minuto.

Só assinalar [x] com evidência real no projeto. Manter casos não testados como tal.
