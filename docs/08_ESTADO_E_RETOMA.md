# ESTADO E RETOMA — ObraClara V2

**Atualizado:** 29/10/2026 (sessão da tarde, após ensaio da demonstração)
**Branch:** `ai/obraclara-v2`
**Último commit de implementação:** `8f79754` — “fix: corrigir apresentação após ensaio cronometrado”
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
| Apache/Laragon com Virtual Host | Validado a 29/10/2026: 15/15 verificações |
| Capturas reais | 11 imagens em `docs/capturas/` (05 e 07 refeitas após o ensaio) |
| Ensaio da demonstração | Concluído: guião percorrido, 2 falhas visuais corrigidas |
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
- Ensaio no navegador (T26–T30): criação/submissão, 403 do colaborador, aprovação,
  rejeição, teclado e ecrã estreito. Detalhe em `RESULTADOS_TESTES.md` e
  `ENSAIO_DEMONSTRACAO.md`.
- Percurso do guião por HTTP numa base descartável: **14 pedidos, 384 ms**, final 8 650 €.

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
2. **Passagem cronometrada em voz alta**, feita pelo aluno. O ensaio do agente já validou a
   mecânica e os tempos (≈4,3 min de fala + 1,5–2 min de interação); falta o ritmo real.
3. **Abrir `http://172.16.58.199/` no telemóvel** na mesma Wi-Fi e confirmar login, menu ☰,
   ausência de decisão para o colaborador e leitura em cartões. O agente não tem dispositivo
   físico; os passos e o resultado esperado estão em `RESULTADOS_TESTES.md`, secção 3.2.
4. Confirmar na conta Freebuff que a sessão usada foi gratuita (o modelo não era visível).

**Melhorias não executadas (não são bloqueadores):**

- Testar Firefox/Safari e leitor de ecrã.
- Sessão de usabilidade com um colega.
- Abrir PR de `ai/obraclara-v2` para `main` (sem merge) se o professor o pedir.

---

## 6. Próxima ação autónoma

Se a sessão for retomada sem instruções novas:

1. Executar `php scripts/seed.php --reset` e `php tests/run.php` para confirmar o estado.
2. Abrir `http://172.16.58.199/` no telemóvel e registar o resultado (única validação em falta).
3. Ensaiar em voz alta com cronómetro, seguindo [`APRESENTACAO_ROTEIRO.md`](APRESENTACAO_ROTEIRO.md);
   o percurso mecânico e as correções já estão validados em `ENSAIO_DEMONSTRACAO.md`.
4. Verificar no GitHub que a branch `ai/obraclara-v2` contém o commit `8f79754`.
5. Não alterar `main`, não fazer merge, não usar force-push.

---

## 7. Notas de transparência

- O servidor auxiliar de testes (8001) e o proxy temporário de captura (8010) foram
  encerrados; os ficheiros temporários com sessões foram removidos.
- O servidor de demonstração em 8000 continua ativo.
- O Apache 2.4.66 do Laragon está em execução manual na porta 80, com o vhost
  `C:\laragon\etc\apache2\sites-enabled\obraclara-v2.test.conf` e a entrada
  `127.0.0.1 obraclara-v2.test` no ficheiro `hosts`. Para parar antes de arrancar o
  Laragon pela interface: `httpd.exe -k shutdown`.
- As capturas foram geradas a partir da aplicação real com Chrome headless e uma sessão
  autenticada; não são maquetas.
- O histórico completo do trabalho está nos commits `9373333` (início), `2ff1a5f`
  (aplicação e testes), `df243e2` (segurança, UX e documentação), `8f79754` (correções do
  ensaio), `a030635` e `e46e7e2` (estado e tempos). O commit da validação Apache é indicado
  no GitHub após este push.
