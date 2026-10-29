# Registo transparente da utilização de IA

> Este documento regista apenas o que é conhecido. Não inventa modelos, planos,
> autorizações ou resultados.

## 1. Estado do esclarecimento

**Estado:** esclarecido com o professor, **segundo informação comunicada pelo aluno**, em
data não registada neste repositório. A utilização de **ChatGPT Plus no planeamento** foi
considerada aceitável nessa circunstância.

**Limite importante:** não existe autorização escrita anexada, nem uma citação literal da
resposta do professor. Este documento não transforma uma comunicação oral/indireta numa
autorização formal. O histórico pago continua declarado e não é apresentado como gratuito.

O guião em [`ESCLARECIMENTO_IA_PROFESSOR.md`](ESCLARECIMENTO_IA_PROFESSOR.md) fica como
registo da pergunta preparada e das ações de transparência. Se o professor vier a pedir
outra correção, o registo é atualizado com a data e a resposta.

## 2. Situações registadas

1. **Protótipo anterior com assistência paga.** Existiu um protótipo ObraClara desenvolvido
   com apoio de IA paga. A V2 foi construída de raiz nesta pasta; **não há código desse
   protótipo na entrega**. O facto é divulgado e não é apresentado como trabalho sem
   assistência paga. Este ponto não foi reutilizado nem apagado do histórico.
2. **Planeamento com ChatGPT Plus.** O pacote de requisitos e orientação da V2 foi
   preparado e revisto numa conversa com ChatGPT Plus em 29/10/2026. É uma subscrição paga.
   Segundo a informação comunicada pelo aluno, a situação foi explicada ao professor e
   considerada aceitável nessa circunstância. **Não há autorização escrita no repositório.**
3. **Sessão Freebuff.** A implementação decorreu no Freebuff Desktop, agente **Buffy**. A
   interface não mostrou o nome do modelo nem o custo da sessão; não é possível afirmar
   neste documento qual foi o modelo. O utilizador deve confirmar no seletor da conta que a
   sessão foi gratuita. O nome do modelo **não é inventado** para preencher a tabela.
4. **Aplicação em execução.** Não chama IA, não usa chaves, não depende de internet e não
   envia dados para serviços externos.

## 3. Registo da execução real

| Data | Ferramenta / modelo | Acesso | Tarefa | Verificação real | Estado |
|---|---|---|---|---|---|
| Antes de 29/10/2026 | Protótipo ObraClara anterior, assistido por IA paga | Pago (histórico) | Protótipo antigo | Não reutilizado na V2 | **DIVULGADO** — não faz parte da entrega |
| 29/10/2026 | ChatGPT Plus (conversa de planeamento) | Pago (histórico) | Requisitos, prioridades e prompts | Revisão humana; sem código da V2 | **ESCLARECIDO** segundo o aluno; sem autorização escrita anexada |
| 29/10/2026 | Freebuff Desktop — agente **Buffy**; modelo não identificado na interface | Sessão do utilizador; custo por confirmar | Implementação, revisão, testes, UI e documentação | `php -l`, 38 testes, 37 verificações HTTP, Apache, servidor local, capturas | **EXECUTADO**; modelo/custo por confirmar |
| 29/10/2026 | Chrome headless + Playwright | Ferramentas locais, não IA | Testar e capturar a aplicação real | 17 capturas em `docs/capturas/` | **EXECUTADO** |
| 29/10/2026 | Laragon / PHP 8.3.30 / SQLite / Apache 2.4.66 | Ferramentas locais, não IA | Executar a aplicação e os testes | Versões verificadas no terminal | **EXECUTADO** |

### O que o agente fez

- Criou o esquema SQLite, migrações, serviços, controladores, vistas, CSS e JavaScript.
- Implementou convites, registo, equipa, perfil, palavra-passe, i18n, tutorial e PDF.
- Escreveu `scripts/install.php`, `scripts/seed.php` e `tests/run.php`.
- Executou `php -l` em todos os ficheiros PHP.
- Executou a suíte automatizada: **38/38 passaram**, código de saída 0.
- Executou um percurso HTTP de **37/37 verificações** numa base descartável: convite,
  registo, perfil, palavra-passe, idioma, PDF, aprovação, CSRF e permissões.
- Validou as rotas novas no Apache 2.4.66 e mediu o layout em 320–1440 px.
- Gerou 17 capturas reais com Chrome/Playwright a partir da aplicação em execução.
- Escreveu o manual, o relatório, o modelo relacional, os resultados e o roteiro a partir
  do que foi executado.

## 4. Exemplos de sugestões que foram corrigidas

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
5. **Tutorial em branco.** O atributo `data-tour-next` do `<body>` colidia com o seletor do
   botão “Seguinte”. Os seletores passaram a estar limitados ao painel do tutorial.
6. **Badges com pouco contraste.** As cores da barra lateral eram usadas em cartões brancos;
   foram criadas variantes com contraste adequado.
7. **Ecrã pequeno.** A barra superior ficou sem identidade e os alvos do idioma eram
   pequenos; foi acrescentada a marca compacta e aumentada a área tátil.
8. **Teste T16.** O teste usava títulos de uma letra, recusados pela validação; o teste foi
   corrigido, não a regra de negócio.

## 5. Limites do apoio recebido

- O agente não “sabe” o que não testou: os itens não executados estão marcados em
  [`RESULTADOS_TESTES.md`](RESULTADOS_TESTES.md) — telemóvel físico, Firefox/Safari, leitor
  de ecrã, usabilidade, carga e HTTPS. O Apache foi validado.
- O aluno continua responsável por compreender e explicar o código, a base de dados e as
  decisões de segurança.
- Nenhuma chave, token ou conta paga foi usada pela aplicação. A aplicação não chama
  serviços de IA em tempo de execução.

## 6. Declaração para o relatório

> **Declaração de utilização de IA.** A V2 foi construída de raiz com assistência de IA.
> A implementação recorreu ao Freebuff Desktop, cujo modelo e custo não eram visíveis na
> interface e ficaram por confirmar. O planeamento usou ChatGPT Plus, uma subscrição paga,
> e um protótipo anterior da ObraClara teve assistência paga, não reutilizado na V2. A
> utilização de ChatGPT Plus no planeamento foi explicada ao professor e, segundo a
> informação comunicada pelo aluno, considerada aceitável nessa circunstância; não existe
> autorização escrita anexada. O histórico pago é declarado e não é apresentado como
> gratuito. O código, os testes, o histórico do repositório e este registo estão disponíveis
> para verificação.

## 7. Referências

- Guião e registo da pergunta: [`ESCLARECIMENTO_IA_PROFESSOR.md`](ESCLARECIMENTO_IA_PROFESSOR.md).
- Enunciado da disciplina: [`00_ENUNCIADO_PROFESSOR.md`](00_ENUNCIADO_PROFESSOR.md), secção 2.
- Evidências de testes: [`RESULTADOS_TESTES.md`](RESULTADOS_TESTES.md).
- Estado do projeto: [`08_ESTADO_E_RETOMA.md`](08_ESTADO_E_RETOMA.md).
