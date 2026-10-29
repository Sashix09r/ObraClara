# 09 — Modelos e agentes Freebuff (orientação em 29/10/2026)

**Fonte de verdade:** o seletor de modelos DO TEU FREEBUFF, incluindo preço de sessão, disponibilidade regional e aviso de utilização de dados. O catálogo pode mudar e não é possível selecionar modelos por prompt se a interface não o permitir.

## Ordem recomendada para máxima eficiência

1. **DeepSeek V4.1 Flash 10/17**, quando disponível em modo gratuito: modelo principal para executar ciclos longos de programação, ficheiros e terminal; recomendação base por rapidez em código/tool use.
2. **GLM 5.3 Flash**, quando disponível em modo gratuito: planeamento de arquitetura delicada, diagnóstico de bugs de SQL/segurança e revisão independente da lógica de negócio. A página do Freebuff descreve-o como opção de raciocínio mais profundo.
3. **MiMo v2.6 Flash Fast**, quando disponível em modo gratuito: opção rápida/balanceada, adequada para revisões visuais com imagens ou caso os outros estejam indisponíveis. É a opção padrão anunciada no catálogo recente.
4. **GPT-6 Luna ou MiMo 2.6 Pro** SOMENTE se o seletor dessa conta indicar acesso realmente gratuito (não plano pago, nem BYOK/subscrição/API paga) e se ainda houver saldo diário gratuito; reservar modelos com custo superior para um problema específico ou revisão final, não para boilerplate.

Em conta/região com acesso limitado, usar os modelos listados como disponíveis SEM PAGAR. **Não selecionar Gemini 3.8 Flash, Muse Spark 1.3 ou outros modelos explicitamente reservados a planos pagos**, mesmo que pareçam mais poderosos. O modo DeepSeek V4.1 Flash *Fast* pode ser exclusivo de planos pagos em regiões limitadas; preferir V4.1 Flash regular.

## Uso dos agentes
- `code-reviewer`: revisão de diff e segurança; pedir auditoria independente.
- `test-runner`: testes reais; PHP pode exigir execução direta por terminal em vez de deteção automática de frameworks.
- `browser-use`: testar fluxos e fazer capturas reais se houver browser.
- `git-curator`: commits/PR e push para branch autorizada, nunca main.
- `shell-runner`: executar PHP, `git status`, testes e comandos seguros, respeitando prompts de permissão.
- `project-scout`/`file-picker`: reconhecer a estrutura.

Estes nomes foram divulgados pelo Freebuff; um agente pode não estar habilitado na versão/conta concreta. Não fingir utilização. Não tentar criar sessões simultâneas desnecessárias que consumam o limite da conta. Em Desktop as workspaces paralelas podem isolar ficheiros, logo garantir integração dos resultados antes de declarar concluído.

## Estratégia operacional
- Primeiro sessão: usar DeepSeek V4.1 Flash (ou GLM 5.3 Flash se disponível e sem limite desvantajoso) para o prompt inicial. Deixar executar **autonomamente**.
- Após conclusão P0 ou se aparecer falha profunda: nova sessão com GLM 5.3 Flash e `PROMPT_AUDITORIA_FINAL.txt`.
- Se a sessão acabar: `PROMPT_CONTINUAR_SESSAO.txt` no mesmo projeto. O documento `docs/08_ESTADO_E_RETOMA.md` é a memória objetiva do projeto.
- Controlar o consumo mostrado na interface; não prometer 14 horas de execução: a política de saldo/duração do Freebuff pode impedir sessões ininterruptas.

## Notas sobre privacidade
O Freebuff Desktop corre com permissões do sistema operacional. Mantém o agente focado em `ObraClaraV2`, sem credenciais verdadeiras e sem ficheiros pessoais. Alguns modelos avisam sobre possível utilização dos dados para treino; consultar essa nota e preferir fontes/ficheiros fictícios.

Referências verificadas em 29/10/2026:
- https://github.com/CodebuffAI/freebuff/blob/main/README.md
- https://freebuff.com/blog/freebuff-subagents-deep-dive
- https://freebuff.com/privacy-policy
