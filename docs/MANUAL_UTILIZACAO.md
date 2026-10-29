# Manual de utilização — ObraClara V2

**Aplicação:** ObraClara V2 — gestão de alterações a obras
**Versão:** 29/10/2026
**Destinatários:** gestor de obra e colaboradores da empresa fictícia ObraClara
**Idioma:** português europeu

> As imagens deste manual foram capturadas da aplicação real em execução local,
> com os dados fictícios de demonstração. Não são maquetas.

---

## 1. Como iniciar a aplicação

1. Abrir um terminal na pasta do projeto.
2. Confirmar o PHP e a extensão SQLite:

   ```bash
   php -v
   php -m | findstr pdo_sqlite
   ```

3. Na primeira utilização, criar a base de dados e os dados fictícios:

   ```bash
   php scripts/install.php
   php scripts/seed.php --reset
   ```

4. Arrancar o servidor:

   ```bash
   php -S localhost:8000 -t public public/router.php
   ```

5. Abrir o navegador em **http://localhost:8000**.

Se a base de dados não existir, a aplicação mostra um ecrã com estas instruções em vez
de um erro técnico.

---

## 2. Entrar na aplicação

![Ecrã de início de sessão](capturas/01-login.png)

Na página de entrada:

1. Preencher **Email** e **Palavra-passe**.
2. Clicar em **Entrar**.
3. A aplicação abre o **Painel**.

Contas fictícias de demonstração:

| Perfil | Email | Palavra-passe |
|---|---|---|
| Gestor | `gestor@obraclara.pt` | `Gestor2026!` |
| Colaborador | `colaborador@obraclara.pt` | `Colab2026!` |

Se as credenciais estiverem erradas, aparece uma mensagem clara sem revelar qual dos
campos falhou. Após cinco tentativas falhadas na mesma sessão, o formulário bloqueia
temporariamente durante 60 segundos.

Para terminar a sessão, usar o botão **Sair** no cartão do utilizador, no fundo do menu
lateral. É um pedido POST protegido por CSRF, não um simples link. Em ecrãs pequenos, é
preciso abrir primeiro o menu (☰).

---

## 3. Painel

![Painel com indicadores reais](capturas/02-painel.png)

O painel apresenta quatro indicadores calculados a partir da base de dados:

- **Obras ativas** — obras planeadas ou em curso.
- **Alterações pendentes** — propostas submetidas que aguardam decisão.
- **Custos aprovados** — soma das alterações aprovadas em obras não arquivadas.
- **Orçamento previsto** — soma dos orçamentos atualizados das obras não arquivadas.

Abaixo encontram-se:

- **Alterações por decidir** — o gestor vê o botão **Decidir**; o colaborador vê
  “Aguarda gestor”.
- **Atividade recente** — últimos eventos do histórico.
- **Obras em acompanhamento** — base, custos aprovados e total atualizado de cada obra.

O menu lateral dá acesso a **Painel**, **Clientes**, **Obras** e **Histórico**.
Em ecrãs pequenos, o botão de menu (☰) abre e fecha a navegação.

---

## 4. Clientes

### 4.1 Consultar e pesquisar

![Lista de clientes](capturas/08-clientes.png)

1. Abrir **Clientes** no menu.
2. Escrever parte do nome, email ou contacto e clicar **Filtrar**.
3. Ativar **Mostrar arquivados** para incluir clientes fora da carteira ativa.
4. Clicar no nome do cliente para abrir o detalhe.

Se a pesquisa não tiver resultados, a aplicação explica-o e oferece **Limpar pesquisa**,
em vez de mostrar uma tabela vazia sem contexto.

### 4.2 Criar um cliente

![Formulário de novo cliente](capturas/09-novo-cliente.png)

1. Clicar **Novo cliente**.
2. Preencher o **Nome** (obrigatório, 2–120 caracteres).
3. Opcionalmente, indicar **Contacto telefónico**, **Email** e **Notas internas**.
4. Clicar **Criar cliente**.

O email é validado no servidor. Se houver erros, o formulário é reapresentado com a
mensagem junto ao campo e com os restantes dados preenchidos.

### 4.3 Editar e arquivar

- **Editar** abre o mesmo formulário com os dados atuais.
- **Arquivar** pede confirmação e só é permitido se o cliente não tiver obras ativas.
  O histórico e as obras mantêm-se; o cliente deixa de poder receber novas obras.
- **Reativar cliente** volta a colocá-lo disponível para novas obras.

Nenhum cliente é apagado definitivamente.

---

## 5. Obras

### 5.1 Lista e filtros

![Lista de obras com orçamentos](capturas/03-obras.png)

Em **Obras** é possível:

- pesquisar por título, cliente ou descrição;
- filtrar por **Planeadas**, **Em curso**, **Concluídas** ou **Arquivadas**;
- comparar **Base**, **Aprovado** e **Atualizado** na mesma linha.

### 5.2 Criar uma obra (gestor)

1. Clicar **Nova obra**.
2. Escolher o **Cliente** (apenas clientes ativos).
3. Indicar o **Título** e o **Orçamento base** em euros — por exemplo `8 000,00`.
4. Opcionalmente, indicar data de início, estado e descrição do âmbito.
5. Clicar **Criar obra**.

O campo de dinheiro aceita `8000`, `8000,00`, `8.000,00` ou `8000.00`. Valores negativos,
texto, mais de duas casas decimais ou valores acima de 99 999 999,99 € são recusados.
Internamente o valor é guardado em cêntimos inteiros.

### 5.3 Detalhe da obra

![Detalhe da obra com orçamento base de 8 000 €](capturas/04-obra-8000.png)

O detalhe mostra:

- estado da obra, cliente e data de início;
- **Orçamento base**, **Custos aprovados** e **Orçamento atualizado**;
- âmbito da obra;
- tabela de alterações com valor, estado, autor e datas;
- histórico de eventos da obra;
- ação de arquivar/reativar (apenas gestor).

O orçamento atualizado é sempre **base + soma das alterações aprovadas**. Rascunhos,
pendentes e rejeitadas não alteram o valor. A própria página explica esta regra.

---

## 6. Alterações ao âmbito

### 6.1 Criar uma alteração

![Formulário de nova alteração](capturas/10-nova-alteracao.png)

1. Na obra, clicar **Nova alteração**.
2. Indicar o **Trabalho adicional** (obrigatório).
3. Indicar o **Custo adicional** em euros (obrigatório, superior a zero).
4. Descrever o trabalho, se necessário.
5. Escolher:
   - **Guardar como rascunho** — fica editável e não conta para o orçamento;
   - **Guardar e submeter** — passa imediatamente a pendente.
6. Depois de guardar, a página volta ao topo da obra com a confirmação; o rascunho aparece
   na tabela de alterações.

### 6.2 Ciclo de vida

| Estado | Quem pode | Efeito no orçamento |
|---|---|---|
| **Rascunho** | Autor ou gestor edita; qualquer utilizador autenticado pode criar | Não conta |
| **Pendente** | Já não é editável; aguarda decisão do gestor | Não conta |
| **Aprovada** | Decisão final do gestor | Soma o valor à obra |
| **Rejeitada** | Decisão final do gestor, com motivo | Não altera a obra |

Um rascunho pode ser submetido com o botão **Submeter**, que pede confirmação.
Depois disso, a alteração fica bloqueada para edição.

### 6.3 Decidir (apenas gestor)

![Ecrã de decisão com impacto no orçamento](capturas/05-decidir.png)

1. Abrir a alteração pendente através de **Decidir** (painel ou obra).
2. Confirmar os dados e o impacto apresentado na coluna lateral
   (**Atualizado hoje** vs. **Se aprovar**).
3. Escolher **Aprovar** ou **Rejeitar**.
4. Escrever uma observação. É obrigatória ao rejeitar e fica guardada no histórico.
5. Clicar **Registar decisão**.

A decisão é definitiva e registada com o nome do gestor e a data/hora. Após decidir, a
página regressa ao topo da obra para mostrar a mensagem e o orçamento atualizado. Não é
possível decidir duas vezes, editar uma alteração decidida ou alterar diretamente o valor
na base de dados (trigger de proteção).

Se um colaborador tentar aprovar por um pedido direto ao servidor, recebe
**403 — Sem permissão**, mesmo que consiga construir o pedido manualmente.

### 6.4 Resultado da aprovação

![Obra depois da aprovação: 8 650 €](capturas/11-obra-8650.png)

Depois de aprovar os 650 €, a obra passa a mostrar:

- base **8 000,00 €**;
- custos aprovados **+ 650,00 €**;
- orçamento atualizado **8 650,00 €**;
- decisão e observação no histórico, com autor e data.

> Para repetir a demonstração original, executar `php scripts/seed.php --reset`,
> que repõe a alteração de 650 € como pendente e o orçamento em 8 000 €.

---

## 7. Histórico

![Histórico global de eventos](capturas/06-historico.png)

Em **Histórico** aparecem os últimos 100 eventos, do mais recente para o mais antigo:

- data e hora;
- ação (criada, editada, submetida, aprovada, rejeitada);
- transição de estado;
- alteração, obra e cliente;
- valor;
- utilizador e perfil;
- observação, quando existe.

O histórico é apenas de inserção: não há ecrã para editar ou apagar eventos, e a base de
dados recusa `UPDATE`/`DELETE` nessa tabela.

---

## 8. Arquivo em vez de eliminação

- **Cliente** só pode ser arquivado sem obras ativas.
- **Obra** só pode ser arquivada sem alterações pendentes.
- Obra arquivada não aceita novas alterações nem decisões; pode ser reativada.
- Nada é apagado, preservando a leitura do histórico e dos valores.

---

## 9. Diferentes perfis

| Ação | Gestor | Colaborador |
|---|---|---|
| Ver painel, clientes, obras e histórico | Sim | Sim |
| Criar/editar/arquivar clientes | Sim | Não (403) |
| Criar/editar/arquivar obras | Sim | Não (403) |
| Criar alterações e submeter | Sim | Sim |
| Editar rascunhos de outros autores | Sim | Não (só os próprios) |
| Aprovar ou rejeitar | Sim | Não (403) |

A interface esconde ações sem permissão, mas a decisão é sempre revalidada no servidor.

---

## 10. Mensagens e resolução de problemas

| Situação | O que fazer |
|---|---|
| “Credenciais inválidas” | Confirmar email e palavra-passe; atenção a maiúsculas e ao `!` final. |
| “Demasiadas tentativas” | Aguardar 60 segundos e tentar de novo. |
| “A sua sessão expirou” | Voltar a entrar; a sessão terminou por inatividade ou reinício. |
| “Sessão expirada” ao guardar | A página esteve aberta demasiado tempo; recarregar e repetir. |
| “Apenas o gestor pode aprovar” | Entrar com a conta de gestor. |
| “Só alterações pendentes podem ser decididas” | A alteração já foi decidida; consultar o histórico. |
| “Obra está arquivada” | Reativar a obra antes de criar ou decidir alterações. |
| “Cliente tem obras ativas” | Arquivar ou concluir primeiro as obras. |
| “Sem resultados” | Limpar a pesquisa ou o filtro de estado. |
| Porta 8000 ocupada | Usar outra porta: `php -S localhost:8080 -t public public/router.php`. |
| Base de dados em falta | Executar `php scripts/install.php` e `php scripts/seed.php --reset`. |
| `pdo_sqlite` em falta | Ativar a extensão no `php.ini` do Laragon e reiniciar o servidor. |
| Ecrã estranho após atualização | Recarregar com `Ctrl+F5` para limpar CSS antigo. |

Os detalhes técnicos dos erros ficam no log do servidor, nunca na página.

---

## 11. Repor os dados de demonstração

```bash
php scripts/seed.php --reset
```

O comando recria apenas `database/obraclara.sqlite` e volta a criar:

- 2 utilizadores fictícios;
- 6 clientes (um arquivado);
- 7 obras (uma arquivada);
- 7 alterações em todos os estados;
- 17 eventos de histórico.

**Atenção:** este comando apaga a base de demonstração local. Não deve ser executado
sobre dados que se queiram conservar.

---

## 12. Boas práticas

- Não introduzir dados pessoais reais de clientes nesta instalação académica.
- Terminar a sessão ao sair de um computador partilhado.
- Não publicar a pasta `database/` nem o ficheiro `.sqlite`.
- Em ambiente real, servir sempre por HTTPS e fazer cópias de segurança.
