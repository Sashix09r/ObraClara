# Roteiro de apresentação — ObraClara V2 (5 a 8 minutos)

**Formato:** demonstração ao vivo + apresentação preparada no Canva
**Duração-alvo:** 7 minutos + perguntas
**Antes de começar:** servidor a correr, dados repostos, duas janelas anónimas preparadas
(gestor e colaborador) e as capturas abertas como plano B.

```bash
php scripts/seed.php --reset
php -S localhost:8000 -t public public/router.php
```

Contas: `gestor@obraclara.pt / Gestor2026!` e `colaborador@obraclara.pt / Colab2026!`.

> A apresentação PowerPoint antiga em `entregas/` é apenas uma base de 10 slides e não
> cobre todas as funcionalidades novas. A versão a apresentar está a ser preparada pelo
> aluno no Canva; este roteiro é a fonte atualizada.

---

## Guião minuto a minuto

### 0:00–0:35 — O problema

> “A ObraClara é uma pequena empresa de remodelações. Durante uma obra, o cliente pede
> trabalhos que não estavam no contrato: mudar o pavimento, trocar a bancada, acrescentar
> iluminação. Cada pedido tem um custo e precisa de uma decisão. Sem sistema, ninguém sabe
> ao certo o que foi aprovado, por quem, quando — e o orçamento deixa de ser fiável.”

Mostrar o painel com 8 000 € na obra principal.

### 0:35–1:10 — Contas e regra central

> “A aplicação é interna: não há registo livre. O gestor cria um convite com email, perfil
> e validade; a pessoa recebe um link, define a palavra-passe e entra. O colaborador
> consulta obras e submete propostas. O gestor decide. A regra central é: orçamento
> atualizado = base + alterações **aprovadas**.”

Mostrar o botão **Criar conta** no login e, se houver tempo, a página **Equipa → Convites**.

### 1:10–1:45 — Tutorial e idioma

> “Na primeira entrada, a aplicação oferece uma visita guiada. O tutorial destaca
> elementos reais e explica o painel, as obras, as decisões e o PDF. Pode ser ignorado,
> fechado com Escape e repetido no perfil. A interface está em português e inglês; a
> preferência fica na conta.”

Abrir **?** ou **Perfil → Rever tutorial**; mostrar um passo e fechar. Trocar **PT → EN**
e voltar a **PT** (não termina sessão).

### 1:45–2:30 — Base de dados e tecnologia

> “O núcleo são cinco tabelas — utilizadores, clientes, obras, alterações e eventos — e a
> versão final acrescenta convites, auditoria e migrações. Cada obra pertence a um cliente;
> cada alteração pertence a uma obra; cada evento pertence a uma alteração e a um
> utilizador. Os valores são guardados em cêntimos inteiros para não haver erros de
> arredondamento. É PHP 8.3 com PDO, HTML, CSS e pouco JavaScript.”

Se houver slide, mostrar o diagrama de
[`10_MODELO_RELACIONAL.md`](10_MODELO_RELACIONAL.md).

### 2:30–3:15 — Entrar como colaborador

1. Abrir `http://localhost:8000/login` numa janela anónima e entrar como colaborador.
2. Abrir **Obras → Remodelação T3 — Rua das Flores**.
3. Mostrar **8 000,00 €** e a alteração de 650 € já pendente.

> “Este é o cenário que vou usar. O colaborador vê a obra, mas não tem botão para decidir.”

### 3:15–4:30 — Criar e submeter uma alteração

1. **Nova alteração**.
2. Título: “Pintura da cozinha em tom claro”.
3. Custo: `180,50`.
4. **Guardar como rascunho**.
5. Mostrar que o orçamento continua **8 000,00 €** e o estado é **Rascunho**.
6. Clicar **Submeter** e confirmar na caixa de diálogo.
7. Mostrar estado **Pendente**, ainda 8 000 €.

*Opcional, 15 s — provar a permissão no servidor:* abrir
`http://localhost:8000/alteracoes/1/decidir` ainda como colaborador. Aparece **403**
sem stacktrace. Voltar.

> “Enquanto pendente, já não pode ser editada. O gestor vai analisá-la.”

### 4:30–5:45 — Decidir como gestor

1. **Sair** e entrar como gestor na segunda janela.
2. No painel, abrir **Decidir** na alteração de 650 €.
3. Mostrar “Impacto no orçamento”: atualizado hoje 8 000 €, se aprovar 8 650 €.
4. **Aprovar**, observação “Aprovado pelo cliente por email” e **Registar decisão**.
5. Mostrar **+650,00 €**, **8 650,00 €** e o histórico com Ana Ferreira.
6. Abrir **Histórico** e mostrar o evento.

> “A decisão é final. Uma segunda tentativa é recusada e um trigger protege o valor na
> base de dados.”

### 5:45–6:30 — PDF, equipa e perfil

1. Na obra, clicar **Exportar PDF** e abrir o ficheiro descarregado.
2. Mostrar base, custos aprovados, total, alterações, decisões e a nota de que não é
   fatura nem contrato.
3. Se houver tempo, abrir **Equipa**: utilizadores, convites, estados e auditoria.
4. Abrir **O meu perfil**: dados, segurança, idioma e **Rever tutorial**.

> “O PDF é gerado localmente, sem APIs, e acompanha o idioma. A equipa é gerida apenas
> pelo gestor; o último gestor ativo não pode ser removido por acidente.”

### 6:30–7:15 — Segurança, testes e IA

> “Todas as escritas usam POST com CSRF. As consultas são preparadas, a saída é escapada,
> as palavras-passe usam hash e os convites guardam apenas o hash do token. A permissão é
> verificada no servidor: um colaborador que construa o pedido à mão recebe 403. Tenho 38
> testes automatizados, 37 verificações HTTP e 33 verificações de browser; o cenário
> 8 000 + 650 está coberto.”

Sobre IA:

> “Usei Freebuff na implementação. O planeamento usou ChatGPT Plus, uma subscrição paga,
> e o protótipo anterior também teve ajuda paga, mas não foi reutilizado. A situação foi
> explicada ao professor e, segundo a informação que tenho, foi considerada aceitável;
> não há autorização escrita anexada e não apresento a IA paga como gratuita.”

### 7:15–7:45 — Fecho

> “Em resumo: a ObraClara transforma pedidos dispersos num fluxo com estados, permissões,
> orçamento exato, histórico e documentos. O valor para a empresa é saber sempre quanto
> custa a obra hoje e porquê.”

---

## Tempos e ensaio

O texto falado tem cerca de 640 palavras (~4,5 min a 140 palavras/min). A mecânica ocupa
1,5–2 min; total estimado **6–7 min**. O percurso HTTP completo fez 14 pedidos em 384 ms
numa base descartável; o percurso de browser final tem **33 verificações** e passou.
Detalhes em [`ENSAIO_DEMONSTRACAO.md`](ENSAIO_DEMONSTRACAO.md) e
[`RESULTADOS_TESTES.md`](RESULTADOS_TESTES.md).

---

## Plano B (falha de servidor ou internet)

1. Manter abertas as capturas em `docs/capturas/` (login, tutorial, painel, equipa,
   perfil, obra 8 000, decisão, obra 8 650, histórico, mobile).
2. Se o servidor falhar, executar numa pasta preparada:

   ```bash
   php scripts/seed.php --reset
   php -S localhost:8000 -t public public/router.php
   ```

3. Se o computador falhar, usar as capturas, o manual e o diagrama de estados; explicar
   o fluxo com a tabela de eventos.
4. Ter o repositório aberto no browser como alternativa.

---

## Perguntas prováveis do professor e respostas

**1. Porque SQLite e não MySQL?**
É uma base relacional real, sem servidor nem configuração, adequada a uma empresa e a uma
demonstração local. O acesso é por PDO; para várias empresas ou muitos utilizadores
concorrentes, MySQL/PostgreSQL seria melhor.

**2. Porque guardam cêntimos e não euros com casas decimais?**
Porque `float` não representa 0,10 € exatamente e as somas acumulam erros. Com inteiros,
650 € são 65 000 cêntimos e a soma é exata. A conversão para “8 650,00 €” só acontece na
apresentação.

**3. O que impede um colaborador de aprovar?**
A interface esconde a ação, mas sobretudo o serviço `ChangeService::decide` verifica o
perfil e lança 403. O controlador também exige gestor. Testámos por HTTP e no browser.

**4. O que acontece se o gestor tentar aprovar duas vezes?**
O serviço só aceita decidir a partir de `pendente`. A segunda tentativa devolve erro e o
orçamento não muda. Um trigger impede alterações diretas a registos decididos.

**5. Como garantem que o histórico não é alterado?**
Não há rotas de edição/eliminação de eventos; a tabela é só de inserção; dois triggers
recusam `UPDATE` e `DELETE`; o teste T17 verifica as três defesas.

**6. Como é calculado o orçamento atualizado?**
`base + SUM(amount_cents) WHERE status='aprovada'`. Rascunho, pendente e rejeitada ficam
fora. Há testes para 8000+650, várias aprovações e rejeição.

**7. O que é o CSRF e como o resolveram?**
É um ataque que faz o browser do utilizador autenticado enviar um pedido sem intenção.
Cada sessão tem um token aleatório incluído nos formulários; o servidor compara em tempo
constante e recusa pedidos sem token (419). O token é renovado no login.

**8. Como protegeram as palavras-passe?**
Com `password_hash` (bcrypt) e `password_verify`; nunca são guardadas em texto simples.
A sessão é regenerada no login e após alteração de palavra-passe.

**9. Como funcionam os convites?**
O gestor define email, perfil e validade; o sistema gera 256 bits aleatórios, guarda apenas
o SHA-256 e mostra o link uma vez. O convite é de uso único, expira e pode ser revogado.
O perfil vem do convite, não do formulário.

**10. O que impede desativar o último gestor?**
O serviço conta os gestores ativos antes de alterar e recusa a operação; também recusa
que um gestor desative a própria conta. Um colaborador não acede à equipa.

**11. O PDF é uma fatura?**
Não. É um documento informativo de gestão, com a declaração explícita de que não é fatura
certificada, contrato assinado nem prova legal. Não há faturação nem pagamentos.

**12. O que fariam a seguir?**
Confirmação do cliente por link, envio de convites por email, notificações internas,
relatórios e testes de acessibilidade com utilizadores reais.

**13. Que parte da IA foi usada e como a verificaram?**
O agente ajudou a escrever código, CSS, testes e documentação. Cada passo foi executado:
lint, 38 testes, 37 verificações HTTP, 33 de browser, Apache e capturas. As sugestões
erradas foram corrigidas. O histórico de IA paga está no registo; a situação foi
esclarecida com o professor, sem autorização escrita anexada.

**14. Quanto tempo demorou e o que foi mais difícil?**
O mais difícil foi acertar as regras de estado e a proteção do orçamento sem duplicar
lógica; a solução foi centralizar nos serviços e reforçar com constraints/triggers. A
revisão da interface também revelou problemas reais de layout e do tutorial.
