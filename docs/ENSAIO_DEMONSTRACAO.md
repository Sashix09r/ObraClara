# Ensaio da demonstração — ObraClara V2

**Data:** 29/10/2026, ensaios às 11:22 e às 11:30
**Ambiente:** Chromium (painel do Freebuff), viewport 1440×900, servidor
`php -S localhost:8000 -t public public/router.php`, dados repostos com
`php scripts/seed.php --reset`.
**Objetivo:** percorrer o guião de [`APRESENTACAO_ROTEIRO.md`](APRESENTACAO_ROTEIRO.md)
de ponta a ponta, encontrar falhas reais e corrigi-las antes da apresentação.

---

## 1. Passos executados e resultados

| # | Passo | Resultado observado | Estado |
|---|---|---|---|
| 1 | Login como colaborador | Painel de Rui Marques; 0 ligações “Decidir”; 2 “Aguarda gestor” | PASSOU |
| 2 | Abrir obra de 8 000 € | Três cartões com 8 000,00 €; 650 € pendente | PASSOU |
| 3 | Criar rascunho de 180,50 € | Criado; orçamento mantém 8 000,00 € | PASSOU |
| 4 | Ver mensagem e orçamento após criar | Página saltou para `#alteracao-8`; mensagem e cartões ficaram acima do ecrã | **FALHOU** |
| 5 | Submeter o rascunho | Estado passou a Pendente; mesmo salto de página | **FALHOU** |
| 6 | Abrir `/alteracoes/1/decidir` como colaborador | Página 403 “Apenas o gestor pode aprovar ou rejeitar alterações.” | PASSOU |
| 7 | Terminar sessão e entrar como gestor | Sessão de Ana Ferreira; 3 pendentes no painel | PASSOU |
| 8 | Abrir decisão dos 650 € | “Se aprovar: 8 650,00 €” correto | PASSOU |
| 9 | Aprovar com observação | Orçamento 8 650,00 € e histórico atualizados | PASSOU |
| 10 | Ver o resultado da aprovação | Página saltou para `#alteracao-1` (scroll 812 px); mensagem e cartões de orçamento fora do ecrã | **FALHOU** |

No browser, cada navegação completa (pedido + render) levou **36 ms a 198 ms**
(dashboard 198 ms, obra 36 ms, decisão 51 ms), sem falhas de rede. A cronometragem
separada dos pedidos HTTP está na secção 3.1.

### 1.1 Segundo ensaio, depois das correções (11:30–11:45)

Dados repostos com `php scripts/seed.php --reset`. O percurso foi repetido do início ao
fim, com uma alteração nova de **420,00 €** («Pintura do hall de entrada») para exercitar
criação, submissão, rejeição e reposição.

| # | Passo | Resultado observado | Estado |
|---|---|---|---|
| 1 | Login colaborador e abertura da obra | 8 000,00 €; 650 € pendente; sem ações de decisão | PASSOU |
| 2 | Criar e submeter 420,00 € | Estado Pendente; orçamento continua 8 000,00 € | PASSOU |
| 3 | Colaborador abre `/alteracoes/1/decidir` | 403 «Sem permissão» | PASSOU |
| 4 | Login gestor | Painel com 3 pendentes e 1 rascunho | PASSOU |
| 5 | Aprovar 650 € com observação | Regresso ao topo (`scrollY = 0`), mensagem visível e **8 650,00 €** | PASSOU |
| 6 | Rejeitar sem observação | Erro «Indica o motivo da rejeição»; escolha mantida; nada gravado | PASSOU |
| 7 | Rejeitar 420 € com observação | Orçamento mantém **8 650,00 €**; mensagem de rejeição | PASSOU |
| 8 | Histórico global | «Aprovada» e «Rejeitada» por Ana Ferreira, com datas e valores | PASSOU |
| 9 | Layout da decisão | Cartões alinhados, rádio dentro do cartão, texto com margem | PASSOU (após F4) |
| 10 | Cabeçalho a 390 px | Uma linha, sem quebra; `scrollWidth` 373 | PASSOU (após F5) |

---

## 2. Falhas encontradas e correções

### F1 — O orçamento atualizado ficava escondido após decidir

**Causa:** o redirecionamento incluía `#alteracao-{id}`. O browser saltava para a linha
da alteração, deixando o cartão dos 8 650 €, a mensagem de sucesso e os cartões de
orçamento acima da área visível. Num ecrã de 900 px, o scroll era 812 px.

**Correção:** todas as ações de alteração passaram a redirecionar para `/obras/{id}` sem
âncora. A mensagem e o orçamento aparecem primeiro; a tabela continua logo abaixo.

**Re-teste:** após aprovar, `scrollY = 0`, mensagem visível a 97 px do topo, cartões de
orçamento visíveis a 331 px, valores `8 000,00 €`, `+650,00 €`, `8 650,00 €`. **PASSOU.**

### F2 — O mesmo salto acontecia ao criar/editar/submeter

**Causa:** o mesmo padrão de âncora nos restantes redirecionamentos.

**Correção:** create, edição, submissão e o caso de rascunho não editável também voltam
ao topo da obra. Re-teste com um rascunho de 50 €: `scrollY = 0`, mensagem visível.

### F3 — Saída pouco visível para trocar de perfil na demonstração

**Causa:** o botão de logout era apenas um ícone no cartão do utilizador. Na troca
colaborador → gestor, o passo é essencial e o ícone podia passar despercebido.

**Correção:** o botão passou a mostrar ícone + **“Sair”**, mantendo `title`. Verificado:
cabe no cartão de 228 px (botão de 56 px), sem truncar o nome.

### F4 — Cartões de decisão sem padding e com a borda nativa do `fieldset`

**Causa:** a regra genérica `.field label` tem especificidade superior a `.choice` e
anulava o `padding: 14px` do cartão. O `fieldset` mantinha ainda a borda e o `legend`
nativos do browser, pelo que o texto ficava encostado às bordas e o rádio parecia cortado.

**Correção:** classe `.field--group` para remover a moldura nativa (`border: 0`, `padding: 0`,
`min-width: 0`) e regra `.field .choice { padding: 14px }` com especificidade suficiente.

**Re-teste:** `padding` calculado **14 px**, cartão de **93 px** (duas linhas de texto),
sem sobreposição; captura [`05-decidir.png`](capturas/05-decidir.png) refeita. **PASSOU.**

### F5 — Cabeçalho partia a data em duas linhas a 390 px

**Causa:** o contexto da sessão e a data não tinham regra para ecrãs estreitos e quebravam,
deixando a barra com 70 px e aspeto desalinhado.

**Correção:** até 560 px, a data é oculta, o contexto fica numa linha com elipse e a barra
passa a `padding: 12px 16px`, ficando com 58 px.

**Re-teste:** a 390 px, `scrollWidth` **373** (sem overflow horizontal) e barra numa linha.
A captura `07-mobile-obra.png` foi refeita a 500 px porque o Chrome headless no Windows
impõe uma janela mínima de 500 px; a 390 px o layout foi medido no browser, não capturado.

### F6 — A confirmação de submissão acrescenta um clique

Não é um defeito, mas o guião não o mencionava. A caixa “Submeter …?” obriga a confirmar.
O guião passou a dizer explicitamente **confirmar na caixa de diálogo**.

### Observação de automação

No painel existem dois links com o mesmo nome da obra (na lista de pendentes e na tabela
de obras). Para um humano não é ambíguo; para um robô de teste é. Não foi alterado porque
é útil ter a obra acessível nos dois contextos. O ensaio usou o caminho visível.

---

## 3. Tempos do guião

### 3.1 Cronometragem real do percurso mecânico

O percurso do guião foi executado por HTTP contra uma **base descartável**
(`tmp/ensaio.sqlite`, servidor em 8001, já encerrado), somando o tempo de cada pedido:

| Bloco do guião | Pedidos | Tempo real |
|---|---:|---:|
| Login do colaborador | 2 | 82 ms |
| Abrir obra + criar rascunho de 180,50 € | 3 | 52 ms |
| Submeter o rascunho | 2 | 60 ms |
| Sair, entrar como gestor | 4 | 128 ms |
| Abrir decisão, aprovar 650 €, confirmar | 3 | 62 ms |
| **Total** | **14** | **384 ms** |

O resultado final foi **8 650,00 €**, com o rascunho submetido visível na obra. O tempo
dominante é o `password_verify` do login (76–89 ms); as páginas autenticadas respondem em
6–25 ms. Isto mede a mecânica, não a fala: a duração da apresentação continua a depender
do ritmo do aluno.

O texto falado do guião tem **cerca de 610 palavras** (excluindo títulos e comandos):

| Ritmo | Tempo estimado de fala |
|---|---|
| 140 palavras/min | ~4,3 min |
| 150 palavras/min | ~4,0 min |

A mecânica medida (login, navegação, preencher três campos, duas submissões, decisão e
consulta do histórico) ocupa cerca de **1,5 a 2 minutos** com cliques e escrita humanos;
o servidor acrescenta **0,4 s** no total. Estimativa total: **6 a 7 minutos**, dentro do
alvo de 5–8, com margem para uma pausa e para o passo opcional do 403.

| Secção | Palavras | Fala a 140 ppm | Orçamento no guião |
|---|---:|---:|---:|
| O problema | 79 | 0,6 min | 0:45 |
| Utilizadores e regra | 53 | 0,4 min | 0:45 |
| BD e tecnologia | 90 | 0,6 min | 0:45 |
| Entrar como colaborador | 50 | 0,4 min | 0:45 |
| Criar e submeter | 53 | 0,4 min | 1:15 |
| Decidir | 100 | 0,7 min | 1:30 |
| Segurança e testes | 74 | 0,5 min | 0:45 |
| Limitações e IA | 80 | 0,6 min | 0:45 |
| Fecho | 35 | 0,2 min | 0:30 |

> Os tempos de fala são estimativas a partir da contagem de palavras; não substituem um
> ensaio cronometrado do aluno. A margem é positiva em todas as secções.

---

## 4. Alterações ao guião

- Passo 6 da secção “Criar e submeter”: **confirmar a caixa de diálogo**.
- Novo passo opcional de 15 segundos: abrir `/alteracoes/1/decidir` como colaborador e
  mostrar a página **403**. Prova que a permissão é do servidor.
- Secção “Decidir”: referir que a página **regressa ao topo** e mostra a mensagem, o
  acréscimo de 650 € e os 8 650 €.
- No fecho, se houver tempo, mostrar o histórico com o evento “Aprovada” de Ana Ferreira.
- Repor sempre os dados antes de começar: `php scripts/seed.php --reset`.

---

## 5. Regressão após correções

- `php -l` em todos os ficheiros: **sem erros**.
- `php tests/run.php`: **25/25 passaram**, código de saída **0**.
- Testes HTTP mantêm 403 para colaborador, 419 para CSRF inválido e 8650 € após aprovação.
- Segundo ensaio: aprovação, rejeição, histórico e layout da decisão verificados; capturas
  `05-decidir.png` e `07-mobile-obra.png` refeitas após as correções F4 e F5.
- Dados de demonstração repostos: **8 000,00 €** e 650 € **pendente**.

## 6. Riscos que restam

- O ensaio foi feito pelo agente no navegador; o aluno deve fazer pelo menos uma passagem
  cronometrada em voz alta (o texto tem ~4,3 min de fala a 140 ppm).
- A escrita e os cliques humanos podem ser mais lentos do que os automatizados; a margem
  de 1 minuto cobre isso.
- Se a rede/localhost falhar, usar as capturas e o plano B do guião.
- Não executar o reset com dados que se queiram conservar.
