# ESTADO E RETOMA — ObraClara V2

**Atualizado:** 29/10/2026 (sessão da manhã)
**Branch:** `ai/obraclara-v2`
**Último commit publicado:** `df243e2` — “feat: reforçar segurança, corrigir UX e documentar a entrega”
**URL da branch:** <https://github.com/Sashix09r/ObraClara/tree/ai/obraclara-v2>
**`main`:** não foi alterada; não houve merge, force-push nem reset destrutivo.

---

## 1. Estado funcional

A aplicação está **funcional e testada localmente**.

| Área | Estado |
|---|---|
| Base de dados SQLite + seed | Concluída |
| Autenticação, sessões e perfis | Concluída |
| Clientes (CRUD, pesquisa, arquivo) | Concluída |
| Obras (CRUD, filtros, arquivo, orçamento) | Concluída |
| Alterações (rascunho → pendente → decisão) | Concluída |
| Orçamento apenas com aprovadas | Concluída e testada |
| Histórico imutável | Concluída (serviço + triggers) |
| Dashboard com SQL real | Concluída |
| Interface responsiva e estados de feedback | Concluída |
| Testes automatizados | 25/25 passaram, exit 0 |
| Testes HTTP | Executados e documentados |
| Capturas reais | 11 imagens em `docs/capturas/` |
| README, manual, relatório, modelo, roteiro | Concluídos |
| Registo de IA | Atualizado, com pontos a esclarecer |

**Servidor de demonstração:** `http://localhost:8000` (PHP built-in server em execução).
Se tiver sido encerrado, arrancar com:

```bash
php -S localhost:8000 -t public public/router.php
```

**Estado atual dos dados:** demonstração reposta com `php scripts/seed.php --reset` —
obra principal com **8 000,00 €** e alteração de **650,00 € pendente**, pronta para a
demonstração. A base está em `database/obraclara.sqlite` (ignorada pelo Git).

---

## 2. Testes e evidências

```bash
php tests/run.php     # 25/25, código de saída 0
```

- Resultados completos: [`RESULTADOS_TESTES.md`](RESULTADOS_TESTES.md).
- Cenário 8 000 + 650 = 8 650 verificado em teste e por HTTP.
- Permissões: colaborador recebe 403 ao decidir; CSRF inválido recebe 419.
- Imutabilidade: trigger recusa alteração de decididas e edição/apagamento de eventos.
- Testes usam bases descartáveis em `tmp/`; os dados de demonstração não foram usados
  em testes destrutivos. Para a captura de 8 650 € a demo foi aprovada e logo reposta.

---

## 3. Ficheiros principais

- `public/index.php`, `public/router.php`, `public/assets/`
- `app/Http/Controllers/`, `app/Http/Router.php`
- `app/Services/` (regras e transações)
- `app/Repositories/` (PDO preparado)
- `app/Support/` (Auth, Csrf, Session, Money, View, exceções)
- `app/views/` (layout e páginas)
- `database/schema.sql`, `scripts/install.php`, `scripts/seed.php`, `tests/run.php`
- `docs/MANUAL_UTILIZACAO.md`, `RELATORIO_PROJETO.md`, `10_MODELO_RELACIONAL.md`,
  `APRESENTACAO_ROTEIRO.md`, `RESULTADOS_TESTES.md`, `REGISTO_IA.md`

---

## 4. Comandos úteis

```bash
php scripts/install.php
php scripts/seed.php --reset
php tests/run.php
php -S localhost:8000 -t public public/router.php
git status
git log --oneline -3
git push
```

Contas fictícias: `gestor@obraclara.pt / Gestor2026!` e
`colaborador@obraclara.pt / Colab2026!`.

---

## 5. Bloqueadores e pendentes

**Bloqueadores técnicos:** nenhum. O Git e o push para a branch autorizada funcionaram.

**Pendente humano (único ponto que exige decisão externa):**

1. **Esclarecer com o professor** o histórico de IA paga (protótipo anterior e planeamento
   com ChatGPT Plus). O texto sugerido está em [`REGISTO_IA.md`](REGISTO_IA.md), secção 5.
   Não afirmar conformidade total antes desta resposta.
2. **Ensaio da apresentação** com o roteiro de 7 minutos e reposição dos dados antes.
3. Confirmar na conta Freebuff que a sessão usada foi gratuita (o modelo não era visível).

**Melhorias não executadas (não são bloqueadores):**

- Testar em Apache/Laragon com Virtual Host e em telemóvel físico.
- Testar Firefox/Safari e leitor de ecrã.
- Sessão de usabilidade com um colega.
- Abrir PR de `ai/obraclara-v2` para `main` (sem merge) se o professor o pedir.

---

## 6. Próxima ação autónoma

Se a sessão for retomada sem instruções novas:

1. Executar `php scripts/seed.php --reset` e `php tests/run.php` para confirmar o estado.
2. Abrir `http://localhost:8000` e percorrer o guião de
   [`APRESENTACAO_ROTEIRO.md`](APRESENTACAO_ROTEIRO.md) uma vez, do início ao fim.
3. Verificar no GitHub que a branch `ai/obraclara-v2` contém o commit mais recente.
4. Não alterar `main`, não fazer merge, não usar force-push.

---

## 7. Notas de transparência

- O servidor auxiliar de testes (8001) e o proxy temporário de captura (8010) foram
  encerrados; os ficheiros temporários com sessões foram removidos.
- O servidor de demonstração em 8000 continua ativo.
- As capturas foram geradas a partir da aplicação real com Chrome headless e uma sessão
  autenticada; não são maquetas.
- O histórico completo do trabalho está nos commits `9373333` (início), `2ff1a5f`
  (aplicação e testes) e `df243e2` (segurança, UX e documentação).
