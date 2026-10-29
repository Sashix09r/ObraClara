# ESTADO E RETOMA — ObraClara V2

**Atualizado:** 29/10/2026, sessão de finalização
**Branch:** `ai/obraclara-v2`
**Repositório:** <https://github.com/Sashix09r/ObraClara/tree/ai/obraclara-v2>
**`main`:** não alterada; sem merge, force-push ou reset destrutivo.
**Commit da implementação:** `b25598d` — *feat: concluir contas, i18n, tutorial e exportação PDF*, publicado em `ai/obraclara-v2`.
**Verificação remota:** `git ls-remote` e `raw.githubusercontent.com` confirmaram o commit e os ficheiros novos.

---

## 1. Estado funcional

A aplicação está **funcional, bilingue e testada localmente**.

| Área | Estado |
|---|---|
| Base de dados SQLite + migrações + seed | Concluída |
| Autenticação, sessões, perfis e bloqueio de tentativas | Concluída |
| Convites, registo e gestão de equipa | Concluída e testada |
| Perfil, dados e alteração de palavra-passe | Concluída e testada |
| Clientes (CRUD, pesquisa, arquivo) | Concluída |
| Obras (CRUD, filtros, arquivo, orçamento) | Concluída |
| Alterações (rascunho → pendente → decisão) | Concluída |
| Orçamento apenas com aprovadas | Concluída e testada |
| Histórico imutável | Concluída (serviço + triggers) |
| Dashboard com SQL real | Concluída |
| Exportação PDF real (PT/EN) | Concluída e testada |
| Tutorial interativo contextual | Concluído e testado |
| Interface PT/EN | Concluída e testada |
| Interface responsiva 320–1440 px | Validada por medição |
| Testes automatizados | **38/38**, saída 0 |
| Verificações HTTP | **37/37** |
| Verificações de browser | **33/33** |
| Apache/Laragon | Rotas, PDF, CSS e cabeçalhos validados |
| Capturas reais | **17** em `docs/capturas/` |
| Relatório e manual | PDFs regenerados (19 e 23 páginas) |
| Pacote Moodle | Regenerado com PDFs e README |
| Apresentação | A antiga foi preservada sem alterações; a final será feita no Canva |

**Servidor de demonstração:** `http://localhost:8000`.
Se tiver sido encerrado:

```bash
php -S localhost:8000 -t public public/router.php
```

**Estado da demo:** reposta com `php scripts/seed.php --reset` — **2 contas oficiais**
(gestor e colaborador), 0 convites pendentes, obra principal com **8 000,00 €** e alteração
de **650,00 € pendente**. As migrações ficam registadas em `schema_migrations` (2 versões).
Cópias anteriores em `database/backups/`, incluindo
`obraclara-20261029-170429-pre-reposicao-demo.sqlite`.

---

## 2. Testes e evidências

```bash
php tests/run.php     # 38/38, saída 0
```

- Resultados completos: [`RESULTADOS_TESTES.md`](RESULTADOS_TESTES.md).
- HTTP: **37/37** verificações numa base descartável (convite, registo, perfil,
  palavra-passe, idioma, PDF, aprovação, CSRF, 403, 419, 422 e conta desativada),
  repetidas em 29/10/2026 contra `tmp/http-final-obraclara.sqlite`.
- Browser: **33/33** verificações com Chrome/Playwright, incluindo tutorial, tradução,
  PDF e mobile a 390 px, repetidas em 29/10/2026 sobre uma cópia da demo.
- PDFs de obra: 1 página, válidos e pesquisáveis; 4 687 bytes antes da aprovação (EN) e
  5 079 bytes depois (PT), com valores e observação.
- Documentos finais: relatório com 19 páginas e manual com 23, ambos A4, sem páginas em
  branco, com 8 e 17 imagens respetivamente; ZIP com 4 ficheiros e `testzip()` limpo.
- `php -l` em **67 ficheiros** PHP, sem erros.
- Os testes usam bases descartáveis em `tmp/`; a demo não é usada para escritas. Foi criada
  uma cópia adicional em `database/backups/` antes da reverificação.

---

## 3. Documentos finais

- [`../entregas/ObraClara_V2_Relatorio.pdf`](../entregas/ObraClara_V2_Relatorio.pdf) — 19 páginas.
- [`../entregas/ObraClara_V2_Manual.pdf`](../entregas/ObraClara_V2_Manual.pdf) — 23 páginas.
- [`../entregas/ObraClara_V2_Entrega_Moodle.zip`](../entregas/ObraClara_V2_Entrega_Moodle.zip) — pacote final.
- [`../entregas/README.md`](../entregas/README.md) — lista e verificações.
- [`../entregas/SHA256SUMS.txt`](../entregas/SHA256SUMS.txt) — integridade.

A apresentação PowerPoint antiga **não foi alterada** e não faz parte do ZIP final; a
versão a apresentar será preparada no Canva. O roteiro atualizado está em
[`APRESENTACAO_ROTEIRO.md`](APRESENTACAO_ROTEIRO.md).

---

## 4. Ficheiros principais

- `public/index.php`, `public/router.php`, `public/assets/`
- `app/Http/Controllers/` (Auth, Team, Profile, Client, Project, Change, History)
- `app/Services/` (regras, convites, conta, PDF)
- `app/Repositories/` (PDO preparado)
- `app/Support/` (Auth, Csrf, Session, Money, Lang, Migrator, Pdf, View)
- `app/views/` (layout, auth, team, profile, clientes, obras, alterações, histórico)
- `lang/pt.php`, `lang/en.php`
- `database/schema.sql`, `scripts/install.php`, `scripts/seed.php`, `tests/run.php`
- `docs/RELATORIO_PROJETO.md`, `MANUAL_UTILIZACAO.md`, `10_MODELO_RELACIONAL.md`,
  `RESULTADOS_TESTES.md`, `REGISTO_IA.md`, `APRESENTACAO_ROTEIRO.md`

---

## 5. Comandos úteis

```bash
php scripts/install.php          # esquema + migrações (cópia antes de migrar)
php scripts/seed.php --reset     # repõe a demo (apaga a base local!)
php tests/run.php                # 38 testes
php -S localhost:8000 -t public public/router.php
git status
git log --oneline -5
git push
```

Contas fictícias: `gestor@obraclara.pt / Gestor2026!` e
`colaborador@obraclara.pt / Colab2026!`.

---

## 6. Bloqueadores e pendentes

**Bloqueadores técnicos:** nenhum.

**Pendente humano:**

1. **Teste num telemóvel físico.** O agente não tem dispositivo; a emulação de viewport foi
   executada. Passos em `RESULTADOS_TESTES.md`, secção 5.
2. **Apresentação final em Canva.** O roteiro está atualizado; os slides antigos não devem
   ser usados como versão final.
3. **Nome do aluno na capa**, se exigido, e revisão final antes do Moodle.
4. **Confirmação da conta Freebuff** quanto ao modelo/custo; o registo não inventa o nome.
5. **Esclarecimento de IA:** foi comunicado ao professor e, segundo o aluno, considerado
   aceitável; não há autorização escrita anexada. Manter a declaração transparente.

---

## 7. Próxima ação autónoma

1. Abrir `http://localhost:8000`, entrar como gestor e percorrer o roteiro.
2. Abrir o ZIP e confirmar os dois PDFs e o README.
3. Se o professor pedir o nome na capa, acrescentá-lo e regenerar os PDFs.
4. Fazer a passagem cronometrada em voz alta.
5. Não alterar a `main`; não fazer merge nem force-push.
