# Roteiro de apresentação — ObraClara V2 (5 a 8 minutos)

**Formato:** demonstração ao vivo + 3 diapositivos de apoio (opcional)
**Duração-alvo:** 7 minutos + perguntas
**Antes de começar:** servidor a correr, dados de demonstração repostos, duas janelas
anónimas preparadas (gestor e colaborador) e capturas abertas como plano B.

```bash
php scripts/seed.php --reset
php -S localhost:8000 -t public public/router.php
```

Contas: `gestor@obraclara.pt / Gestor2026!` e `colaborador@obraclara.pt / Colab2026!`.

---

## Guião minuto a minuto

### 0:00–0:45 — O problema

> “A ObraClara é uma pequena empresa de remodelações. Durante uma obra, o cliente pede
> trabalhos que não estavam no contrato: mudar o pavimento, trocar a bancada, acrescentar
> iluminação. Cada pedido tem um custo e precisa de uma decisão. O problema é que, sem
> sistema, ninguém sabe ao certo o que foi aprovado, por quem, quando — e o orçamento
> deixa de ser fiável.”

Mostrar o painel com 8 000 € na obra principal enquanto se fala.

### 0:45–1:30 — Utilizadores e regra central

> “Há dois perfis. O colaborador consulta obras, cria propostas e submete-as. O gestor
> decide. A regra central é simples: o orçamento atualizado é o valor base mais a soma das
> alterações **aprovadas**. Rascunhos, pendentes e rejeitadas não contam.”

Apontar para os três cartões: base, aprovado, atualizado.

### 1:30–2:15 — Base de dados e tecnologia

> “Os dados estão em SQLite, com cinco tabelas: utilizadores, clientes, obras, alterações e
> eventos de histórico. Cada obra pertence a um cliente; cada alteração pertence a uma obra;
> cada evento pertence a uma alteração e a um utilizador. Os valores são guardados em
> cêntimos inteiros para não haver erros de arredondamento. A aplicação é PHP 8.3 com PDO,
> HTML, CSS e muito pouco JavaScript.”

Se houver slide, mostrar o diagrama de
[`docs/10_MODELO_RELACIONAL.md`](10_MODELO_RELACIONAL.md).

### 2:15–3:00 — Entrar como colaborador

1. Abrir `http://localhost:8000/login` e entrar com a conta de colaborador.
2. Abrir **Obras → Remodelação T3 — Rua das Flores**.
3. Mostrar **8 000,00 €** e a alteração de 650 € já pendente.

> “Este é o cenário que vou usar. O colaborador vê a obra, mas não tem botão para decidir.”

### 3:00–4:15 — Criar e submeter uma alteração

1. **Nova alteração**.
2. Título: “Pintura da cozinha em tom claro”.
3. Custo: `180,50`.
4. **Guardar como rascunho**.
5. Mostrar que o orçamento continua **8 000,00 €** e o estado é **Rascunho**.
6. Clicar **Submeter** e confirmar.
7. Mostrar estado **Pendente**, ainda 8 000 €.

> “Enquanto pendente, já não pode ser editada. O gestor vai agora analisá-la.”

### 4:15–5:45 — Decidir como gestor

1. Terminar sessão (botão de saída) e entrar como gestor.
2. No painel, abrir **Decidir** na alteração de 650 €.
3. Mostrar a coluna “Impacto no orçamento”: atualizado hoje 8 000 €, se aprovar 8 650 €.
4. Escolher **Aprovar**, escrever “Aprovado pelo cliente por telefone” e **Registar decisão**.
5. Mostrar **8 650,00 €** e **+650,00 €** nos cartões.
6. Mostrar o histórico: criada, submetida e aprovada por Ana Ferreira, com data e observação.
7. Abrir **Histórico** e mostrar o evento.

> “A decisão é final. Se tentar decidir outra vez, a aplicação recusa. O valor na base de
> dados também está protegido por um trigger.”

### 5:45–6:30 — Segurança e testes

> “Todas as escritas usam POST com token CSRF. As consultas são preparadas, a saída é
> escapada, as palavras-passe usam hash e a sessão é regenerada no login. A permissão é
> verificada no servidor: um colaborador que construa o pedido à mão recebe 403. Tenho 25
> testes automatizados e testes HTTP reais; o cenário 8 000 + 650 está coberto e passou.”

Referir [`docs/RESULTADOS_TESTES.md`](RESULTADOS_TESTES.md) e mostrar rapidamente o
resultado “25/25”.

### 6:30–7:15 — Limitações e uso de IA

> “É uma aplicação local, para uma empresa e poucos utilizadores. Não tem pagamentos,
> faturação certificada nem login de clientes, porque está fora do âmbito. Usei IA no
> desenvolvimento e nos testes, mas verifiquei tudo com execução real. Há uma assistência
> anterior paga no planeamento que preciso de esclarecer com o professor; está registada
> no relatório, não a escondo.”

### 7:15–7:45 — Fecho

> “Em resumo: a ObraClara transforma pedidos dispersos num fluxo com estados, permissões,
> orçamento exato e histórico. O valor para a empresa é saber sempre quanto custa a obra
> hoje e porquê.”

---

## Plano B (falha de servidor ou internet)

1. Manter abertas as capturas em `docs/capturas/` (login, painel, obra 8 000, decisão,
   obra 8 650, histórico, mobile).
2. Se o servidor falhar, executar numa pasta preparada:

   ```bash
   php scripts/seed.php --reset
   php -S localhost:8000 -t public public/router.php
   ```

3. Se o computador falhar, usar as capturas e o manual; explicar o fluxo com o diagrama de
   estados e a tabela de eventos.
4. Ter o repositório aberto no browser como alternativa de consulta.

---

## Perguntas prováveis do professor e respostas

**1. Porque SQLite e não MySQL?**
É uma base relacional real, sem servidor nem configuração, adequada a uma empresa e a uma
demonstração local. O acesso é por PDO, por isso a migração para MySQL exigiria sobretudo
ajustar o SQL específico. Para várias empresas ou muitos utilizadores concorrentes,
MySQL/PostgreSQL seria melhor.

**2. Porque guardam cêntimos e não euros com casas decimais?**
Porque `float` não representa 0,10 € exatamente e as somas acumulam erros. Com inteiros,
650 € são 65 000 cêntimos e a soma é exata. A conversão para “8 650,00 €” só acontece na
apresentação.

**3. O que impede um colaborador de aprovar?**
Duas coisas: a interface esconde a ação, mas sobretudo o serviço `ChangeService::decide`
verifica o perfil e lança 403. O controlador também exige gestor. Testámos por HTTP: um
pedido direto do colaborador devolve 403 e a alteração continua pendente.

**4. O que acontece se o gestor tentar aprovar duas vezes?**
O serviço só aceita decidir a partir de `pendente`. A segunda tentativa devolve erro e o
orçamento não muda. Além disso, um trigger impede alterações diretas a registos decididos.

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
A sessão é regenerada no login para evitar fixação.

**9. Porque arquivam em vez de apagar?**
Porque apagar perderia o histórico de obras e decisões. Arquivar desativa sem destruir;
há regras para não arquivar clientes com obras ativas nem obras com pendentes.

**10. O que fariam a seguir?**
Confirmação do cliente por link, exportação de proposta em PDF, notificações internas,
relatórios e testes de acessibilidade com utilizadores reais.

**11. Que parte da IA foi usada e como a verificaram?**
O agente ajudou a escrever código, CSS, testes e documentação. Cada passo foi executado:
lint, 25 testes, servidor real, pedidos HTTP e capturas. As sugestões erradas foram
corrigidas — por exemplo, a leitura de valores com vírgula e ponto. O histórico de IA paga
anterior está no registo e será esclarecido.

**12. Quanto tempo demorou e o que foi mais difícil?**
O mais difícil foi acertar as regras de estado e a proteção do orçamento sem duplicar
lógica; a solução foi centralizar nos serviços e reforçar com constraints/triggers. As
capturas e a revisão da interface também revelaram problemas reais de layout.
