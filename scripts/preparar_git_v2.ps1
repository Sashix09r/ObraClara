# ObraClara V2 — inicialização SEGURA do repositório Git no próprio projeto.
# Executar somente dentro desta V2 e quando o agente tiver verificado a pasta.
# NÃO executa commit, push, merge ou operações destrutivas.
# A técnica 'reset --mixed origin/main' preserva os ficheiros atuais da V2.

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$branch = 'ai/obraclara-v2'
$remoteUrl = 'https://github.com/Sashix09r/ObraClara.git'
$projectPath = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path

function Run-Git([string[]] $GitArgs) {
    & git @GitArgs
    if ($LASTEXITCODE -ne 0) {
        throw "Comando Git falhou (código $LASTEXITCODE): git $($GitArgs -join ' ')"
    }
}

if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
    throw 'Git não encontrado. Instala ou disponibiliza Git no PATH do Windows e volta a executar.'
}

Push-Location $projectPath
try {
    if (-not (Test-Path -LiteralPath '.git')) {
        # Evita criar um repositório dentro de outro já aberto, sem comandos que falhem.
        $parentDir = Split-Path -Path $projectPath -Parent
        while (-not [string]::IsNullOrWhiteSpace($parentDir)) {
            if (Test-Path -LiteralPath (Join-Path $parentDir '.git')) {
                throw "A pasta pertence a um repositório pai: $parentDir. Não inicializar Git aqui."
            }
            $nextDir = Split-Path -Path $parentDir -Parent
            if ($nextDir -eq $parentDir) { break }
            $parentDir = $nextDir
        }
        Write-Host "Inicializar Git numa nova branch: $branch"
        Run-Git @('init', '-b', $branch)
        Run-Git @('remote', 'add', 'origin', $remoteUrl)
        Run-Git @('fetch', 'origin', 'main')
        # MUITO IMPORTANTE: --mixed, NUNCA --hard. Não toca nos ficheiros da V2.
        Run-Git @('reset', '--mixed', 'origin/main')
    } else {
        $currentBranch = (& git branch --show-current).Trim()
        if ($LASTEXITCODE -ne 0 -or $currentBranch -ne $branch) {
            throw "Branch ativa '$currentBranch' em vez de '$branch'. Parar para não modificar outra branch."
        }
        $configuredRemote = (& git remote get-url origin).Trim()
        if ($LASTEXITCODE -ne 0) {
            throw 'O remote origin não está configurado.'
        }
        $allowedRemotes = @($remoteUrl, 'https://github.com/Sashix09r/ObraClara', 'git@github.com:Sashix09r/ObraClara.git')
        if ($configuredRemote -notin $allowedRemotes) {
            throw "Remote diferente do autorizado: $configuredRemote"
        }
        # Não fazer reset numa pasta já com Git. Se a inicialização anterior
        # tiver sido interrompida, o agente analisa o estado antes de decidir.
        Write-Host 'Já existe Git na branch autorizada; não foi feito reset.' 
    }

    $active = (& git branch --show-current).Trim()
    if ($active -ne $branch) { throw 'Verificação final da branch falhou.' }
    Write-Host 'Branch segura confirmada:' $active
    Write-Host 'Remote:' (& git remote get-url origin)
    Write-Host 'Estado local (sem staging e sem push):'
    Run-Git @('status', '--short')
    Write-Host 'Concluído. O agente pode rever, fazer commit e push apenas desta branch.'
} finally {
    Pop-Location
}
