<?php

declare(strict_types=1);

namespace ObraClara\Support;

/**
 * Renderização de vistas com escape centralizado no layout.
 */
final class View
{
    /** @param array<string, mixed> $data */
    public static function render(string $template, array $data = []): string
    {
        $file = OBRACLARA_ROOT . '/app/views/' . $template . '.php';

        if (!is_file($file)) {
            throw new \RuntimeException('Vista inexistente: ' . $template);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $file;

        return (string) ob_get_clean();
    }

    /**
     * Renderiza uma página completa dentro do layout.
     *
     * @param array<string, mixed> $data
     */
    public static function page(string $template, array $data = [], int $status = 200): void
    {
        $content = self::render($template, $data);
        $title = $data['title'] ?? 'Painel';
        $user = $data['user'] ?? null;
        $nav = $data['nav'] ?? '';
        $flash = Flash::consume();

        http_response_code($status);
        require OBRACLARA_ROOT . '/app/views/layout.php';
    }

    /** Página simples de erro, sem depender de dados da base. */
    public static function error(int $status, string $heading, string $message): void
    {
        http_response_code($status);
        $title = $heading;
        $user = Auth::user();
        $nav = '';
        $flash = [];

        ob_start();
        ?>
        <section class="empty-state card">
            <p class="empty-state__code"><?= e((string) $status) ?></p>
            <h1><?= e($heading) ?></h1>
            <p><?= e($message) ?></p>
            <a class="btn btn--primary" href="<?= e(url('/')) ?>"><?= e(t('errors.back_dashboard')) ?></a>
        </section>
        <?php
        $content = (string) ob_get_clean();

        require OBRACLARA_ROOT . '/app/views/layout.php';
    }
}
