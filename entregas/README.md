# Pacote de entrega — ObraClara V2

Gerado em **29/10/2026** a partir dos documentos Markdown do projeto e das capturas
reais da aplicação em execução. As imagens dos documentos não são maquetas: foram
capturadas em `docs/capturas/` com Chrome headless e sessão autenticada.

Para o Moodle, o ficheiro mais simples de enviar é
**`ObraClara_V2_Entrega_Moodle.zip`**. Os ficheiros individuais ficam também nesta
pasta, para consulta ou envio separado.

## 1. Conteúdo

| Ficheiro | Formato | Dimensão | Para que serve |
|---|---|---|---|
| `ObraClara_V2_Relatorio.pdf` | PDF A4 | 15 páginas · 900 KB | Relatório do projeto: contexto, requisitos, arquitetura, base de dados, testes, dificuldades, IA e limitações. |
| `ObraClara_V2_Manual.pdf` | PDF A4 | 16 páginas · 1,6 MB | Manual passo a passo: instalação, login, clientes, obras, alterações, decisão, histórico e resolução de erros. |
| `ObraClara_V2_Apresentacao.pptx` | PowerPoint 16:9 | 10 slides + notas · 650 KB | Apresentação editável para a defesa, com capturas reais e notas em cada diapositivo. |
| `ObraClara_V2_Apresentacao.pdf` | PDF 16:9 | 10 páginas · 803 KB | Cópia de segurança da apresentação, para abrir em qualquer computador sem PowerPoint. |
| `SHA256SUMS.txt` | Texto | 4 somas | Verificação de integridade dos PDFs e do PPTX. |
| `ObraClara_V2_Entrega_Moodle.zip` | ZIP | 3,5 MB | Conjunto anterior, pronto a carregar de uma só vez. |

Os documentos-fonte continuam no repositório:

- [`docs/RELATORIO_PROJETO.md`](../docs/RELATORIO_PROJETO.md)
- [`docs/MANUAL_UTILIZACAO.md`](../docs/MANUAL_UTILIZACAO.md)
- [`docs/APRESENTACAO_ROTEIRO.md`](../docs/APRESENTACAO_ROTEIRO.md)
- [`docs/ENSAIO_DEMONSTRACAO.md`](../docs/ENSAIO_DEMONSTRACAO.md)

## 2. Verificações feitas

| Verificação | Resultado |
|---|---|
| Formato das páginas | Relatório e manual em **A4 (210 × 297 mm)**; apresentação em **339 × 190 mm (16:9)** |
| Número de páginas | 15, 16 e 10, sem páginas em branco |
| Texto pesquisável | Sim, nos três PDFs |
| Cenário central | «8 000,00 €», «8 650,00 €» e «25/25» presentes no relatório |
| Índice | Ligações internas clicáveis (41 no relatório, 22 no manual) |
| Notas do orador | Presentes nos 10 diapositivos do PPTX (197–292 caracteres cada) |
| Metadados | Título, autor, assunto e palavras-chave preenchidos |
| Origem das imagens | 11 capturas reais em `docs/capturas/`, integradas nos PDFs |

O relatório e o manual foram paginados com capa, índice, cabeçalho, numeração e
quebras controladas. A apresentação foi exportada pelo PowerPoint instalado, não por
uma conversão aproximada.

## 3. Antes de submeter

1. **Identificação.** Os documentos identificam o autor como «aluno do 12.º F», por
   privacidade. O nome fica associado pela submissão no Moodle; se o professor exigir o
   nome impresso, acrescentá-lo na capa e regenerar os PDFs.
2. **Esclarecimento de IA.** Ler a secção 12 do relatório e
   [`docs/ESCLARECIMENTO_IA_PROFESSOR.md`](../docs/ESCLARECIMENTO_IA_PROFESSOR.md). O
   trabalho **não declara conformidade total** com a regra de IA gratuita enquanto não
   houver resposta do professor.
3. **Revisão final.** Confirmar datas, nomes fictícios e valores. O relatório e o manual
   descrevem apenas o que foi executado; os testes não realizados estão marcados como
   **NÃO EXECUTADO**.
4. **Apresentação.** Levar o PPTX e o PDF. O PPTX contém as notas do orador; o PDF serve
   de reserva se o computador da sala não tiver PowerPoint.
5. **Tamanho no Moodle.** O ZIP tem cerca de 3,5 MB. Se o limite da disciplina for menor,
   enviar os PDFs individualmente e o PPTX à parte.

## 4. Como abrir

- **PDF:** qualquer leitor (Edge, Chrome, Acrobat, Firefox).
- **PPTX:** Microsoft PowerPoint, LibreOffice Impress ou Google Slides. As notas do
  orador podem não aparecer noutras aplicações; nesse caso, usar o roteiro em
  [`docs/APRESENTACAO_ROTEIRO.md`](../docs/APRESENTACAO_ROTEIRO.md).
- **Verificação de integridade:**

```bash
sha256sum -c SHA256SUMS.txt
```

No Windows/PowerShell, comparar com:

```powershell
Get-FileHash .\ObraClara_V2_Relatorio.pdf -Algorithm SHA256
```

## 5. Regeneração

Os PDFs e o PPTX foram compostos a partir dos Markdown e das capturas atuais. Se o
relatório, o manual ou as capturas mudarem, os documentos têm de ser novamente
compostos e exportados; não basta renomear os ficheiros antigos. O procedimento usado
está descrito em [`docs/08_ESTADO_E_RETOMA.md`](../docs/08_ESTADO_E_RETOMA.md), secção 8.
