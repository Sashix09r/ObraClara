# Registo transparente da utilização de IA

> Este documento regista apenas o que é conhecido. Não inventa modelos, planos nem
> resultados. A conformidade com a regra escolar de “apenas IA gratuita” **não está
> confirmada** enquanto o professor não esclarecer os pontos assinalados.

## 1. Situações a esclarecer antes da submissão

1. **Protótipo anterior com assistência paga.** Existiu um protótipo ObraClara desenvolvido
   com apoio de IA paga. A V2 foi construída de raiz nesta pasta; **não foi copiado código
   desse protótipo**. O facto deve ser comunicado ao professor.
2. **Planeamento com ChatGPT Plus.** O pacote de requisitos e orientação da V2 foi
   preparado e revisto numa conversa com ChatGPT Plus em 29/10/2026, antes desta sessão de
   implementação. Isso **não é IA exclusivamente gratuita**. É necessário perguntar se a
   restrição do enunciado abrange o planeamento e, se abranger, refazer essa parte com uma
   ferramenta gratuita permitida.
3. **Plano da conta Freebuff.** A implementação decorreu numa sessão Freebuff
   disponibilizada ao utilizador. A interface não mostrou o nome do modelo nem o custo da
   sessão; não é possível afirmar neste documento qual foi o modelo. O utilizador deve
   confirmar no seletor da conta que a sessão foi gratuita.

## 2. Registo da execução real

| Data | Ferramenta / modelo | Acesso | Tarefa | Verificação real | Estado |
|---|---|---|---|---|---|
| Antes de 29/10/2026 | Protótipo ObraClara anterior, assistido por IA paga | Pago (histórico) | Protótipo antigo | Não reutilizado na V2 | **A ESCLARECER** |
| 29/10/2026 | ChatGPT Plus (conversa de planeamento) | Pago (histórico) | Requisitos, prioridades e prompts | Revisão humana; sem código executado | **A ESCLARECER** |
| 29/10/2026 | Freebuff Desktop — agente **Buffy**; modelo não identificado na interface | Sessão do utilizador; custo por confirmar | Implementação, revisão, testes, UI e documentação | `php -l`, 25 testes, `curl`, servidor local, capturas | **EXECUTADO**; modelo e custo por confirmar |
| 29/10/2026 | Chrome headless + PHP built-in server | Ferramentas locais, não IA | Testar e capturar a aplicação real | Capturas em `docs/capturas/` | **EXECUTADO** |
| 29/10/2026 | Laragon / PHP 8.3.30 / SQLite | Ferramentas locais, não IA | Executar a aplicação e os testes | Versões verificadas no terminal | **EXECUTADO** |

### O que o agente fez nesta sessão

- Criou o esquema SQLite, os serviços, controladores, vistas, CSS e JavaScript.
- Escreveu `scripts/install.php`, `scripts/seed.php` e `tests/run.php`.
- Executou `php -l` em todos os ficheiros PHP.
- Executou a suíte automatizada: **25/25 passaram**, código de saída 0.
- Executou testes HTTP reais com `curl`: login 401, CSRF 419, permissões 403, validação
  422, aprovação 302 com orçamento 8 650 €, rejeição sem alteração, duplicação bloqueada.
- Gerou capturas reais com Chrome headless a partir da aplicação em execução.
- Escreveu este registo, o manual, o relatório e o roteiro, sempre a partir do que foi
  executado.

## 3. Exemplos de sugestões que foram corrigidas

Estes casos mostram que o resultado não foi aceite sem verificação:

1. **Valores monetários.** A primeira implementação tratava a vírgula sempre como decimal,
   falhando `1,234.56`. Os testes T01/T02 obrigaram a corrigir a regra para o último
   separador e a documentá-la.
2. **CSRF no login.** O primeiro teste HTTP reutilizou o token anterior ao login e recebeu
   419. A proteção estava correta; o teste é que foi corrigido — não se enfraqueceu a
   segurança para “passar”.
3. **`seed.php --reset` no Windows.** O ficheiro SQLite ficava bloqueado por uma referência
   PDO; o script passou a libertar a ligação antes de apagar. Verificado com execução real.
4. **Cartões de decisão.** A captura real mostrou o *radio* sobreposto ao texto; o layout
   passou de posicionamento absoluto para grelha.
5. **Dashboard.** A captura mostrou a coluna esquerda esticada; a grelha passou a alinhar
   os cartões ao topo.
6. **Ecrã pequeno.** Uma nota longa criava overflow a 390 px; as células passaram a quebrar
   linha. Verificado com a largura real do documento.
7. **Teste T16.** O teste usava títulos de uma letra, recusados pela validação; o teste foi
   corrigido, não a regra de negócio.

## 4. Limites do apoio recebido

- O agente não “sabe” o que não testou: os itens não executados estão marcados em
  [`RESULTADOS_TESTES.md`](RESULTADOS_TESTES.md) (telemóvel físico, Firefox, leitor de
  ecrã, usabilidade, carga, HTTPS). O Apache foi validado a 29/10/2026.
- O aluno continua responsável por compreender e explicar o código, a base de dados e as
  decisões de segurança.
- Nenhuma chave, token ou conta paga foi usada pela aplicação. A aplicação não chama
  serviços de IA em tempo de execução.

## 5. Texto sugerido para esclarecer com o professor

> “Usei IA para me apoiar no desenvolvimento, como o enunciado permite, mas quero ser
> transparente: a V2 foi implementada numa sessão Freebuff e o planeamento inicial e um
> protótipo anterior tiveram assistência de IA paga. Não reutilizei código desse protótipo.
> Gostaria de saber se a restrição de ‘apenas IA gratuita’ se aplica também ao planeamento
> e, se for necessário, refaço essa parte com uma ferramenta gratuita. Todo o código foi
> revisto e testado por mim, e posso explicar as decisões.”

## 6. Referências

- Enunciado da disciplina: [`00_ENUNCIADO_PROFESSOR.md`](00_ENUNCIADO_PROFESSOR.md), secção 2.
- Evidências de testes: [`RESULTADOS_TESTES.md`](RESULTADOS_TESTES.md).
- Estado do projeto: [`08_ESTADO_E_RETOMA.md`](08_ESTADO_E_RETOMA.md).
