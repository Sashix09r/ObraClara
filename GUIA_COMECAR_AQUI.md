# COMEÇAR AQUI — OBRACLARA V2 COM FREEBUFF

**A partir de 29/10/2026, este ZIP é o pacote de INSTRUÇÕES, não a aplicação pronta.** Nunca colocar estes ficheiros por cima do protótipo antigo. Pasta: `C:\laragon\www\ObraClaraV2`.

## A única configuração inicial necessária
1. Instalar/abrir **Freebuff Desktop para Windows 64-bit**, a partir de https://freebuff.com/desktop (se já tens Freebuff Desktop, não reinstalar).
2. Extrair o ZIP em `C:\laragon\www\` e verificar que aparece `C:\laragon\www\ObraClaraV2\AGENTS.md` (evitar pasta duplicada `ObraClaraV2\ObraClaraV2`).
3. Abrir a pasta `ObraClaraV2` no Freebuff Desktop, de preferência numa sessão local, e garantir que o agente vê `AGENTS.md`. Não escolher modo Web App Builder, porque a stack é PHP + SQLite local.
4. No seletor de modelos, usar um dos modelos GRATUITOS realmente oferecidos. **Preferência para desenvolvimento: DeepSeek V4.1 Flash**. Para diagnóstico/bugs difíceis: **GLM 5.3 Flash**. Para revisão de UI, se incluído gratuitamente: **MiMo v2.6 Flash Fast**. O catálogo e os limites mudam; consultar o picker do próprio Freebuff.
5. Colar o conteúdo de `PROMPT_INICIAL_PARA_COLAR.txt` e enviar. **Não é necessário copiar os outros documentos para o chat**: o agente deve lê-los no disco.

## E o GitHub?
O prompt **já autoriza** criar a branch `ai/obraclara-v2` e fazer commits e pushes automaticamente nessa branch. Para a primeira autenticação do GitHub, o Windows pode pedir que inicies sessão ou autorizes acesso: isso não pode ser evitado por um prompt. Nunca partilhar passwords/tokens no chat.

## Enquanto o Freebuff trabalha
- Não aprovar comandos destrutivos ou alterações fora de `ObraClaraV2`.
- Deixar o agente implementar, executar testes e corrigir sem o interromper a cada módulo.
- Em caso de limite de sessão/modelo, abrir uma nova sessão **na mesma pasta** e colar `PROMPT_CONTINUAR_SESSAO.txt`. Não começar uma nova aplicação do zero.
- Se precisar de rever depois, usar `PROMPT_AUDITORIA_FINAL.txt` com um modelo diferente GRATUITO para reduzir erros não detetados pelo implementador.

## O que ainda exige presença humana
1. Autorizar uma única vez o acesso GitHub, se pedido pelo programa.
2. Confirmar numa demonstração curta o cenário gestor/colaborador, porque o professor avalia conhecimento e apresentação.
3. Esclarecer com o professor a regra de IA paga aplicada ao protótipo anterior **e ao planeamento feito com ChatGPT Plus** (não disfarçar esse trabalho como gratuito).
4. Entregar ao Moodle os ficheiros e o link correto. O agente pode preparar tudo; a submissão e a defesa pertencem ao aluno.

## Não confundir GitHub e alojamento
Fazer `push` envia código ao GitHub; **não** publica uma aplicação PHP + SQLite online nem substitui iniciar o Laragon. Para a escola, o enunciado aceita execução local com instruções. O agente deve documentar e verificar o servidor PHP local.

## Se a interface mudar
Não confiar em nomes/limites fixos: verificar a informação do seletor do Freebuff e os requisitos do professor. Nunca escolher um modelo que apareça como exclusivo de plano pago.

Referências públicas consultadas em 29/10/2026:
- https://github.com/CodebuffAI/freebuff/blob/main/README.md
- https://freebuff.com/desktop
- https://freebuff.com/blog/freebuff-subagents-deep-dive
