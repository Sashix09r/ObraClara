# Pacote de entrega — ObraClara V2

Gerado em **29/10/2026** a partir dos documentos Markdown do projeto e das 17 capturas
reais da aplicação em execução. As imagens não são maquetas.

Para o Moodle, o ficheiro mais simples de enviar é
**`ObraClara_V2_Entrega_Moodle.zip`**. Os ficheiros individuais ficam também nesta
pasta, para consulta ou envio separado.

## 1. Conteúdo final

| Ficheiro | Formato | Dimensão | Para que serve |
|---|---|---|---|
| `ObraClara_V2_Relatorio.pdf` | PDF A4 | 19 páginas · 1,3 MB | Relatório do projeto: contexto, requisitos, arquitetura, base de dados, convites, perfil, i18n, tutorial, PDF, segurança, testes, dificuldades, IA e limitações. |
| `ObraClara_V2_Manual.pdf` | PDF A4 | 23 páginas · 2,4 MB | Manual passo a passo: instalação, login, convite, tutorial, idioma, perfil, clientes, obras, alterações, decisão, histórico, PDF, mobile e resolução de erros. |
| `README.md` | Markdown | 5 KB | Este índice e as verificações do pacote. |
| `SHA256SUMS.txt` | Texto | 3 somas | Integridade dos dois PDFs e deste README. |
| `ObraClara_V2_Entrega_Moodle.zip` | ZIP | ≈ 3,5 MB | Relatório, manual, README e somas, prontos a carregar de uma vez. |

**Apresentação:** os ficheiros `ObraClara_V2_Apresentacao.pptx` e `.pdf` que já existiam
foram **mantidos sem alterações** e **não fazem parte do ZIP final**. São a versão-base
anterior; o aluno está a preparar a apresentação final no Canva. O roteiro atualizado
está em [`docs/APRESENTACAO_ROTEIRO.md`](../docs/APRESENTACAO_ROTEIRO.md).

Os documentos-fonte continuam no repositório:

- [`docs/RELATORIO_PROJETO.md`](../docs/RELATORIO_PROJETO.md)
- [`docs/MANUAL_UTILIZACAO.md`](../docs/MANUAL_UTILIZACAO.md)
- [`docs/APRESENTACAO_ROTEIRO.md`](../docs/APRESENTACAO_ROTEIRO.md)
- [`docs/RESULTADOS_TESTES.md`](../docs/RESULTADOS_TESTES.md)
- [`docs/REGISTO_IA.md`](../docs/REGISTO_IA.md)

## 2. Verificações feitas

| Verificação | Resultado |
|---|---|
| Formato das páginas | Relatório e manual em **A4 (210 × 297 mm)** |
| Número de páginas | **19** e **23**, sem páginas em branco |
| Texto pesquisável | Sim; valores, funcionalidades e testes localizáveis |
| Cenário central | «8 000,00 €», «8 650,00 €», «38/38», «37/37» e «33/33» no relatório |
| Índice | Ligações internas clicáveis, geradas a partir dos títulos |
| Metadados | Título, autor, assunto e criador preenchidos |
| Origem das imagens | 17 capturas reais em `docs/capturas/`, integradas nos PDFs |
| Legibilidade | Páginas rasterizadas e revistas; sem imagens cortadas ou sobreposições |
| Integridade do ZIP | `testzip()` sem erros e extração de todos os ficheiros |
| Somas SHA-256 | `sha256sum -c SHA256SUMS.txt` válido |

O relatório e o manual foram paginados com capa, índice, cabeçalho, numeração e quebras
controladas. A capa identifica o autor como «aluno do 12.º F»; o nome fica associado na
submissão do Moodle.

## 3. Testes que sustentam os documentos

- **38/38** testes automatizados (`php tests/run.php`, saída 0).
- **37/37** verificações HTTP reais numa base descartável, repetidas em 29/10/2026
  contra uma base criada de novo (`tmp/http-final-obraclara.sqlite`).
- **33/33** verificações de browser (convite, registo, tutorial, perfil, palavra-passe,
  idioma, permissões, PDF, aprovação, histórico e mobile), repetidas em 29/10/2026 às
  16:54 sobre uma cópia descartável da demo.
- **67 ficheiros PHP** verificados com `php -l`, sem erros.
- Apache 2.4.66/Laragon: rotas novas, PDF, CSS e cabeçalhos verificados.
- PDFs de obra válidos e pesquisáveis em PT e EN.

Evidência completa em [`docs/RESULTADOS_TESTES.md`](../docs/RESULTADOS_TESTES.md).

## 4. Antes de submeter

1. **Identificação.** Se o professor exigir o nome impresso na capa, acrescentá-lo e
   regenerar os PDFs.
2. **IA.** A situação do ChatGPT Plus no planeamento foi explicada ao professor e,
   segundo a informação do aluno, considerada aceitável nessa circunstância. Não existe
   autorização escrita anexada; o relatório mantém a declaração transparente.
3. **Revisão final.** Confirmar datas, nomes fictícios, valores e o estado da demo:
   **8 000,00 €** com a alteração de 650 € **pendente**.
4. **Apresentação.** Levar a apresentação final do Canva; os PDFs servem de plano B.
5. **Tamanho no Moodle.** O ZIP tem cerca de 3,5 MB. Se o limite for menor, enviar os
   dois PDFs individualmente.

## 5. Como abrir

- **PDF:** qualquer leitor (Edge, Chrome, Acrobat, Firefox).
- **Verificação de integridade:**

```bash
sha256sum -c SHA256SUMS.txt
```

No Windows/PowerShell:

```powershell
Get-FileHash .\ObraClara_V2_Relatorio.pdf -Algorithm SHA256
```

## 6. Regeneração

Os PDFs são compostos a partir dos Markdown e das capturas atuais com Chrome/Playwright.
Se o relatório, o manual ou as capturas mudarem, os documentos têm de ser novamente
gerados; não basta renomear os ficheiros antigos. O procedimento está em
[`docs/08_ESTADO_E_RETOMA.md`](../docs/08_ESTADO_E_RETOMA.md).
