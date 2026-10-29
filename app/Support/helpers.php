<?php

declare(strict_types=1);

/**
 * Escapa texto para inserção segura em HTML (proteção contra XSS).
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Constrói um URL interno respeitando a pasta onde a aplicação está instalada.
 * Ex.: url('/obras/3') -> /ObraClaraV2/public/obras/3 quando instalada em subpasta.
 */
/** Traduz uma chave do dicionário ativo. */
function t(string $key, array $params = []): string
{
    return \ObraClara\Support\Lang::t($key, $params);
}

function current_locale(): string
{
    return \ObraClara\Support\Lang::locale();
}

function url(string $path = '/'): string
{
    $base = $GLOBALS['OBRACLARA_BASE'] ?? '';
    $path = '/' . ltrim($path, '/');

    return $base . ($path === '/' ? '/' : $path);
}

/**
 * Caminho relativo (sem a base) do pedido atual — usado para destacar o menu.
 */
function current_path(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH) ?: '/';
    $base = $GLOBALS['OBRACLARA_BASE'] ?? '';

    if ($base !== '' && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base));
    }

    return '/' . ltrim($path, '/');
}

/**
 * Data/hora atual no fuso da aplicação, no formato guardado na base de dados.
 */
function now(): string
{
    return date('Y-m-d H:i:s');
}

/**
 * Formata uma data guardada em ISO para leitura portuguesa.
 */
function format_datetime(?string $value): string
{
    if ($value === null || $value === '') {
        return '—';
    }

    $timestamp = strtotime($value);

    if ($timestamp === false) {
        return '—';
    }

    return \ObraClara\Support\Lang::locale() === 'en'
        ? date('d M Y, H:i', $timestamp)
        : date('d/m/Y H:i', $timestamp);
}

function format_date(?string $value): string
{
    if ($value === null || $value === '') {
        return '—';
    }

    $timestamp = strtotime($value);

    if ($timestamp === false) {
        return '—';
    }

    return \ObraClara\Support\Lang::locale() === 'en'
        ? date('d M Y', $timestamp)
        : date('d/m/Y', $timestamp);
}

/** Formata cêntimos em euros no idioma ativo. */
function money(int $cents): string
{
    return \ObraClara\Support\Money::formatFor($cents, \ObraClara\Support\Lang::locale());
}

/** Campo oculto com o token CSRF, obrigatório em todos os formulários POST. */
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(\ObraClara\Support\Csrf::token()) . '">';
}

/** URL de um recurso estático com versão para evitar cache antigo. */
function asset(string $path): string
{
    return url('/assets/' . ltrim($path, '/')) . '?v=3';
}

/**
 * Ícones SVG inline (sem dependências externas, funciona offline).
 */
function icon(string $name, string $class = 'icon'): string
{
    $paths = [
        'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/>',
        'clients'   => '<circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/><path d="M16 4.5a3 3 0 0 1 0 6"/><path d="M18 14.6c2 .7 3 2.5 3 5.4"/>',
        'projects'  => '<path d="M4 21V6.5L12 3l8 3.5V21"/><path d="M9 21v-6h6v6"/><path d="M9 9h2M13 9h2M9 12h2M13 12h2"/>',
        'history'   => '<path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/><path d="M12 7v5l3.5 2"/>',
        'plus'      => '<path d="M12 5v14M5 12h14"/>',
        'logout'    => '<path d="M15 4h4a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-4"/><path d="M10 8l-4 4 4 4M6 12h9"/>',
        'search'    => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
        'arrow'     => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'back'      => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
        'check'     => '<path d="m5 13 4 4L19 7"/>',
        'close'     => '<path d="M6 6l12 12M18 6 6 18"/>',
        'edit'      => '<path d="M4 20h4l10-10a2.8 2.8 0 0 0-4-4L4 16v4Z"/><path d="m14 6 4 4"/>',
        'archive'   => '<rect x="3" y="4" width="18" height="4" rx="1"/><path d="M5 8v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8"/><path d="M10 12h4"/>',
        'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'money'     => '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M7 9v6M17 9v6"/>',
        'alert'     => '<path d="M12 3 2 21h20L12 3Z"/><path d="M12 9v5M12 17.5v.5"/>',
        'team'      => '<circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/><path d="M16 8h5M18.5 5.5v5"/>',
        'profile'   => '<circle cx="12" cy="8" r="3.4"/><path d="M5 20c0-3.6 3-6 7-6s7 2.4 7 6"/>',
        'help'      => '<circle cx="12" cy="12" r="9"/><path d="M9.6 9a2.5 2.5 0 1 1 3.8 2.1c-.9.6-1.4 1.1-1.4 2.2"/><path d="M12 17h.01"/>',
        'pdf'       => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 16h4"/>',
        'globe'     => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.7 3.7 5.7 3.7 9S14.5 18.3 12 21c-2.5-2.7-3.7-5.7-3.7-9S9.5 5.7 12 3Z"/>',
        'lock'      => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
        'mail'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/>',
        'link'      => '<path d="M10 13a5 5 0 0 0 7 0l2-2a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7 0l-2 2a5 5 0 0 0 7 7l1-1"/>',
        'user'      => '<circle cx="12" cy="8" r="3.4"/><path d="M5 20c0-3.6 3-6 7-6s7 2.4 7 6"/>',
        'eye'       => '<path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.6"/>',
    ];

    $body = $paths[$name] ?? $paths['dashboard'];

    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
        . 'stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . $body . '</svg>';
}


/** Data de hoje no idioma ativo, para o cabeçalho. */
function today_label(): string
{
    if (\ObraClara\Support\Lang::locale() === 'en') {
        return date('l, j F Y');
    }

    $days = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'];
    $months = ['', 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];

    return ucfirst($days[(int) date('w')]) . ', ' . (int) date('j') . ' de ' . $months[(int) date('n')] . ' de ' . date('Y');
}

/** Iniciais do utilizador para o avatar. */
function initials(string $name): string
{
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    $parts = array_values(array_filter($parts));

    if ($parts === []) {
        return '?';
    }

    $first = mb_substr($parts[0], 0, 1);
    $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';

    return mb_strtoupper($first . $last);
}

/**
 * Lê um campo de formulário como texto aparado.
 */
function post_string(string $key): string
{
    $value = $_POST[$key] ?? '';

    return is_string($value) ? trim($value) : '';
}

/**
 * Indica se um campo falhou a validação, devolvendo a classe CSS adequada.
 */
function field_class(array $errors, string $field): string
{
    return isset($errors[$field]) ? 'is-invalid' : '';
}
