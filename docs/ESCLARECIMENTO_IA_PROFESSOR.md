# Esclarecimento sobre IA — guião para o professor

**Preparado em:** 29/10/2026
**Estado:** **preparado, ainda não enviado nem respondido.** Este documento não declara
conformidade com a regra de IA gratuita. Serve para o aluno enviar a pergunta e registrar a
resposta com exatidão.

**Objetivo:** obter uma indicação do professor sobre a utilização de ChatGPT Plus no
planeamento da V2 e sobre o protótipo anterior com assistência paga; depois, corrigir o que
for necessário e atualizar o relatório e o registo de IA.

---

## 1. Resumo em cinco linhas

1. A V2 foi construída de raiz nesta pasta; não foi reutilizado código do protótipo antigo.
2. O protótipo anterior da ObraClara teve assistência de IA paga; está identificado, mas não
   faz parte da entrega.
3. O planeamento da V2 (requisitos, prioridades e prompts) foi preparado numa conversa com
   **ChatGPT Plus**, que é uma subscrição paga. Não foi essa conversa que escreveu o código.
4. A implementação foi feita no **Freebuff Desktop**; o nome e o custo do modelo não eram
   visíveis na interface e estão por confirmar. A aplicação não usa IA em execução.
5. Pela leitura literal do ponto 2 do enunciado, a restrição parece abranger também o
   planeamento. A pergunta ao professor é sobre a consequência e a forma de corrigir — não
   sobre uma interpretação que favoreça o aluno.

---

## 2. Factos, evidências e limites

| Facto | O que se sabe | Evidência | O que não se afirma |
|---|---|---|---|
| Protótipo anterior | Teve assistência de IA paga, antes de 29/10/2026 | Registo em [`REGISTO_IA.md`](REGISTO_IA.md) | Que a V2 seja uma cópia; que o protótipo tenha sido usado na entrega |
| Planeamento da V2 | Requisitos, prioridades e prompts numa conversa ChatGPT Plus em 29/10/2026 | Registo e pacote de orientação | Que essa conversa tenha escrito o código da V2 |
| Implementação | Freebuff Desktop, agente Buffy; modelo e custo não visíveis | Sessão do utilizador; histórico de commits | Um nome de modelo ou um custo que não foram observados |
| Aplicação em execução | Não chama IA, não usa chaves, funciona offline | Código e configuração | Que a ausência de IA em runtime resolva a regra de utilização |
| Regra do enunciado | Só modelos de acesso gratuito; sem subscrições ou APIs pagas | [`00_ENUNCIADO_PROFESSOR.md`](00_ENUNCIADO_PROFESSOR.md), secção 2 | Que a restrição se aplique apenas a código |

### Leitura que deve ser assumida

O enunciado diz: «Podes escolher qualquer modelo de IA que possas utilizar gratuitamente.
**Não é permitido usar modelos pagos, subscrições pagas de IA ou APIs pagas.**» E logo
depois: «A IA pode ajudar-te a planear a aplicação, escrever e rever código…».

Não é defensável dizer que o planeamento está automaticamente fora da regra. A mensagem ao
professor deve reconhecer isso e perguntar **qual é a consequência e a remediação**, não
pedir autorização retroativa.

---

## 3. Mensagem pronta a enviar (email ou Moodle)

> **Assunto:** Esclarecimento sobre utilização de IA — ObraClara V2 (12.º F)
>
> Bom dia, professor,
>
> Antes de submeter e apresentar a ObraClara V2, quero esclarecer um ponto sobre a regra de
> utilização exclusiva de IA gratuita. Prefiro perguntar antes de assumir uma interpretação
> que me favoreça.
>
> Reli o ponto 2 do enunciado. Aí se diz que só posso usar modelos de acesso gratuito e que
> a IA pode ajudar a planear, escrever e rever código. Pela leitura literal, entendo que a
> restrição também abrange o planeamento. Foi nessa fase que usei **ChatGPT Plus**, uma
> subscrição paga, para organizar requisitos, prioridades e prompts. Não foi essa conversa
> que escreveu o código da V2.
>
> A V2 foi construída de raiz nesta pasta. Não reutilizei código de um protótipo anterior da
> ObraClara, que também teve assistência de IA paga; esse protótipo está identificado no
> registo, mas não faz parte da entrega. A implementação foi feita no **Freebuff Desktop**;
> a interface não mostra o nome do modelo nem o custo da sessão, e estou a confirmar no
> seletor da conta se foi gratuita. A aplicação não usa IA em execução.
>
> A minha pergunta é: **qual é a consequência desta utilização e o que devo fazer para
> corrigir?** Se considerar que o planeamento tem de ser refeito, refaço-o com uma ferramenta
> gratuita (ou sem IA), substituo a versão no registo e explico a alteração no relatório. Se
> houver alguma penalização, aceito e registro.
>
> O código, os 25 testes automatizados, os testes HTTP, a validação em Apache, o histórico do
> repositório e o registo de IA estão disponíveis para verificação. Posso explicar as
> decisões e mostrar o que foi alterado.
>
> Obrigado pelo tempo.
> [Nome do aluno], 12.º F

### Versão curta (Moodle/Teams)

> Professor, antes da entrega quero esclarecer uma situação: usei ChatGPT Plus (subscrição
> paga) para o planeamento da V2, embora não para escrever o código. A V2 foi feita de raiz e
> não reutilizei o protótipo antigo, que também teve ajuda paga. Pela leitura literal do ponto
> 2, a restrição parece abranger o planeamento. Devo refazê-lo com uma ferramenta gratuita?
> Aceito a indicação e registro a resposta no relatório. Obrigado.

---

## 4. Versão oral para a apresentação (30–45 segundos)

> «Antes de mostrar a aplicação, quero ser transparente sobre a IA. A V2 foi feita de raiz e
> não reutilizei código do protótipo antigo. Mas usei ChatGPT Plus para o planeamento, e o
> protótipo anterior teve ajuda paga. O enunciado só permite IA gratuita e diz que a IA pode
> ajudar a planear; por isso não assumo que o planeamento não conta. Já pedi esclarecimento
> ao professor. Se for necessário, refaço o planeamento com uma ferramenta gratuita e registro
> a alteração. O código e os testes estão disponíveis para verificação.»

Não dizer «está tudo conforme» antes da resposta. Não culpabilizar a ferramenta nem o agente.

---

## 5. Perguntas prováveis e respostas

**1. Usaste IA paga para escrever o código?**
Não no código da V2. O ChatGPT Plus foi usado para requisitos, prioridades e prompts. O
protótipo anterior teve assistência paga, mas não foi reutilizado. O código da V2 foi
implementado no Freebuff e testado.

**2. Então o projeto está conforme?**
Não afirmo isso. Pela leitura literal, o planeamento pode estar abrangido pela regra. Por
isso perguntei e proponho refazer essa parte se for necessário.

**3. Porque usaste ChatGPT Plus?**
Foi a ferramenta que tinha para organizar o trabalho no início e não verifiquei a regra com o
cuidado necessário. Assumo a decisão; não a escondo.

**4. O Freebuff é gratuito?**
A sessão foi disponibilizada na minha conta. A interface não mostra o nome do modelo nem o
custo; estou a confirmar no seletor. Não invento um nome. A aplicação não chama IA em execução.

**5. Que parte do trabalho é tua?**
A definição do problema, os requisitos, as decisões e a verificação. O agente escreveu código,
CSS, testes e documentação sob a minha direção. A responsabilidade académica é minha e consigo
explicar o fluxo, o esquema e os testes.

**6. O que vais refazer?**
Se o professor exigir, refaço o planeamento documentado (requisitos, prioridades e prompts)
com uma ferramenta gratuita ou sem IA, com data e registo. O código só muda se os requisitos
mudarem; nesse caso repito os testes e as capturas.

**7. Isto invalida o projeto?**
Não decido eu; aceito a indicação. O que posso garantir é que não escondi a utilização, que o
protótipo não foi reutilizado e que o código e os testes são verificáveis.

**8. Porque não apagaste o histórico?**
Apagar seria esconder. O registo faz parte da entrega e permite ao professor decidir com os
factos.

---

## 6. Cenários de resposta e ações

| Resposta do professor | Interpretação | Ações concretas |
|---|---|---|
| «A restrição abrange o planeamento; refaz com ferramenta gratuita.» | Não conforme na fase de planeamento | Criar `docs/PLANEAMENTO_IA_REVISTO.md` com requisitos/prioridades reescritos sem IA ou com ferramenta gratuita confirmada; marcar a versão ChatGPT como substituída; atualizar `REGISTO_IA.md`, `RELATORIO_PROJETO.md` e `README.md`; repetir testes se os requisitos mudarem; commit e push |
| «Só o código/produto entregue conta; o planeamento é orientação.» | Planeamento fora do âmbito, segundo o professor | Registar a frase exata; mudar o estado para **ESCLARECIDO**; manter a declaração no relatório; não reescrever o planeamento |
| «O protótipo anterior também conta.» | É preciso separar claramente a V2 do protótipo | Mostrar que não há código reutilizado; perguntar se é necessária uma reconstrução limpa; se sim, planear novo repositório e prazo — não prometer o que não é possível até 05/11 |
| «Não há problema; segue.» | Autorização explícita | Registar a resposta; manter a transparência; não usar isso para afirmar que toda a IA foi gratuita |
| Sem resposta até 03/11 | Incerteza mantém-se | Assumir a leitura literal: refazer o planeamento com ferramenta gratuita/sem IA, registar a mudança e entregar com declaração de transparência; não declarar conformidade |
| Resposta ambígua | Não resolve o ponto | Enviar uma única pergunta de confirmação com duas opções: (a) refazer o planeamento; (b) manter e declarar a situação |

**Regra de segurança:** nunca alterar datas, apagar conversas ou reescrever o registo para
parecer que a IA paga não existiu. A remediação é documentada, não apagada.

---

## 7. Registo da resposta (preencher quando chegar)

| Campo | Registo |
|---|---|
| Data e hora | |
| Canal (email, Moodle, presencial) | |
| Resposta literal ou resumo fiel | |
| Decisão: planeamento abrangido? | |
| Decisão: protótipo abrangido? | |
| Ações decididas | |
| Documentos atualizados | |
| Commit / push | |
| Quem confirmou | |

Guardar a mensagem original (email/print) fora do repositório público se contiver dados
pessoais; no repositório, registar apenas o essencial.

---

## 8. Checklist de atualização após a resposta

- [ ] Registar a resposta na tabela anterior, com a frase exata sempre que possível.
- [ ] Atualizar a secção 1 de [`REGISTO_IA.md`](REGISTO_IA.md): estado de cada situação.
- [ ] Atualizar a tabela de execução: **A ESCLARECER** → **ESCLARECIDO** ou **CORRIGIDO**.
- [ ] Atualizar a secção 12 de [`RELATORIO_PROJETO.md`](RELATORIO_PROJETO.md).
- [ ] Atualizar a limitação correspondente no [`README.md`](../README.md).
- [ ] Se houver remediação: criar o planeamento revisto, datar e identificar a ferramenta.
- [ ] Se o código mudar: `php -l`, `php tests/run.php`, testes HTTP e capturas atualizadas.
- [ ] Atualizar [`08_ESTADO_E_RETOMA.md`](08_ESTADO_E_RETOMA.md) e o roteiro da apresentação.
- [ ] Commit e push na branch `ai/obraclara-v2` (nunca na `main`).

---

## 9. O que evitar

- «Foi só planeamento, por isso não conta» — é precisamente o ponto em dúvida.
- «O agente fez tudo» — a responsabilidade e a submissão são do aluno.
- «Não sabia da regra» — o enunciado é explícito e estava disponível.
- «A sessão Freebuff foi gratuita» sem confirmação no seletor/conta.
- Inventar o nome do modelo ou o custo.
- Apagar o protótipo, a conversa ou o histórico para “limpar” a entrega.
- Pedir ao professor uma resposta por escrito que valide uma interpretação favorável; pedir
  uma decisão sobre a consequência e a correção.

---

## 10. Declaração de transparência (se não houver resposta até à entrega)

Se o professor não responder até 04/11/2026, incluir esta declaração no relatório e no
README, preenchendo as datas. Não a substituir por uma afirmação de conformidade.

> **Declaração de utilização de IA.** Usei assistência de IA no desenvolvimento desta
> aplicação. A implementação da V2 recorreu ao Freebuff Desktop, cujo modelo e custo não
> eram visíveis na interface e ficaram por confirmar. O planeamento usou ChatGPT Plus, uma
> subscrição paga, e um protótipo anterior da ObraClara teve assistência paga, não reutilizado
> na V2. Pela leitura literal do ponto 2 do enunciado, esta situação pode não cumprir a regra
> de IA exclusivamente gratuita. Comuniquei-a ao professor em [data] e não obtive resposta
> até [data]; por isso não apresento o trabalho como conforme. Se for determinada uma
> correção, refaço o planeamento com uma ferramenta gratuita ou sem IA e atualizo o registo.
> O código, os testes, o histórico do repositório e o registo de IA estão disponíveis para
> verificação.

---

## 11. Referências

- Enunciado: [`00_ENUNCIADO_PROFESSOR.md`](00_ENUNCIADO_PROFESSOR.md), secção 2.
- Registo de IA: [`REGISTO_IA.md`](REGISTO_IA.md).
- Relatório: [`RELATORIO_PROJETO.md`](RELATORIO_PROJETO.md), secção 12.
- Testes e evidências: [`RESULTADOS_TESTES.md`](RESULTADOS_TESTES.md).
- Estado do projeto: [`08_ESTADO_E_RETOMA.md`](08_ESTADO_E_RETOMA.md).
