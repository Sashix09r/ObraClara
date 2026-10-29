# 06 — GitHub automático, sem alterações à main

## Permissão e destino
- Repositório: `https://github.com/Sashix09r/ObraClara`
- Branch principal EXISTENTE: `main`, intocável até decisão futura.
- Branch autorizada para trabalho e push automático: `ai/obraclara-v2`.
- O utilizador autorizou antecipadamente commits e push repetidos NESTA BRANCH. Não é preciso perguntar após cada módulo.

## Arranque técnico comprovado para pasta V2 que ainda não é repositório Git
O agente deve primeiro identificar se há `.git`. Se não houver, pode usar `scripts/preparar_git_v2.ps1`, cujo método foi ensaiado com um repositório Git de teste. Em termos lógicos:

1. `git init -b ai/obraclara-v2` dentro de `ObraClaraV2`.
2. `git remote add origin https://github.com/Sashix09r/ObraClara.git`.
3. `git fetch origin main`.
4. **`git reset --mixed origin/main`**: associa a branch à história do repositório remoto **sem sobrescrever os ficheiros existentes na pasta V2**. Não usar `--hard`.
5. `git status --short`: os ficheiros antigos da `main` podem aparecer como remoções NESTA BRANCH, e os ficheiros V2 como novos/modificados. Isto é esperado na reconstrução de raiz; **não afeta `main`**.
6. Verificar `.gitignore` e conteúdo a publicar. **Atenção:** `.gitignore` não remove ficheiros já rastreados na `main`. Caso o rascunho histórico `docs/ObraClara_Relatorio_v0.1.docx` esteja rastreado, remover só do índice da branch V2 com `git rm --cached -- docs/ObraClara_Relatorio_v0.1.docx`, sem apagar a cópia local, e rever outros ficheiros potencialmente privados.
7. `git add -A`, conferir `git diff --cached --name-only` e `git diff --cached --stat`, `git commit -m "chore: iniciar ObraClara V2"`, `git push -u origin ai/obraclara-v2`.

Se já há Git, não executar `init` ou `reset` às cegas. Verificar se branch, remote e working tree estão seguros. Se houver outro branch ativo, stash/backup controlado somente após análise, sem perder alterações.

## Políticas permanentes
- Sem force push, sem merge automático, sem push para `main`, sem alterar o branch padrão, sem expor credenciais reais.
- Ao terminar cada módulo funcional, fazer commit claro e push. Se o módulo estiver incompleto, commit como `wip:` e continuar, sem o apresentar como pronto.
- Antes de push: `git status`, `git diff --cached --stat`, conferir `.gitignore` e procurar segredos/dados sensíveis.
- Se Git não tiver a identidade configurada, o agente pode pedir **apenas uma vez** a configuração local `user.name` e `user.email` ao utilizador; não inventar valores pessoais.
- Se aparecer autorização GitHub no browser, é um passo humano de autenticação e não deve ser contornado. Não pedir tokens por mensagem.
- Registar commit SHA e o URL completo da branch apenas após o push ter sido bem-sucedido.

## Entrega ao professor
A branch pode ser entregue diretamente pelo URL `https://github.com/Sashix09r/ObraClara/tree/ai/obraclara-v2`, se realmente existir. Por conveniência, pode abrir um PR de `ai/obraclara-v2` para `main` após os testes; **não fundir automaticamente**. A versão de entrega não é a `main` até existir merge.

## Falhas e recuperação
- Sem rede ou sem autenticação: continuar desenvolvimento, manter commits locais e um impedimento claro no estado de retoma.
- Divergência no remoto: `git fetch`, analisar diferenças, não usar `push --force`.
- Ao retomar, verificar branch antes de qualquer comando de escrita.
