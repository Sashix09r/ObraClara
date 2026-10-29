<?php

declare(strict_types=1);

namespace ObraClara\Support;

use RuntimeException;

/**
 * Gerador PDF mínimo para os resumos de obra.
 *
 * Usa as fontes base Helvetica e codificação Windows-1252, o que permite
 * acentuação portuguesa e o símbolo € sem incorporar fontes externas.
 * Não é um motor tipográfico: cobre texto, linhas, retângulos, paginação
 * e quebras de linha — exatamente o necessário para o documento de obra.
 */
final class Pdf
{
    public const WIDTH = 595.28;
    public const HEIGHT = 841.89;

    /** @var list<string> */
    private array $pages = [];

    /** @var list<string> */
    private array $footers = [];

    public function __construct()
    {
        $this->addPage();
    }

    public function addPage(): void
    {
        $this->pages[] = '';
        $this->footers[] = '';
    }

    public function pageCount(): int
    {
        return count($this->pages);
    }

    /** Texto numa coordenada a partir do topo da página. */
    public function text(
        float $x,
        float $top,
        string $text,
        float $size = 10,
        bool $bold = false,
        array $color = [0.08, 0.12, 0.11]
    ): void {
        if ($text === '') {
            return;
        }

        $font = $bold ? 'F2' : 'F1';
        $y = self::HEIGHT - $top;
        [$r, $g, $b] = $this->rgb($color);

        $this->append(sprintf(
            "BT /%s %.2F Tf %.3F %.3F %.3F rg 1 0 0 1 %.2F %.2F Tm (%s) Tj ET",
            $font,
            $size,
            $r,
            $g,
            $b,
            $x,
            $y,
            $this->escape($text)
        ));
    }

    public function rect(float $x, float $top, float $width, float $height, array $color, float $radius = 0): void
    {
        [$r, $g, $b] = $this->rgb($color);
        $y = self::HEIGHT - $top - $height;

        $this->append(sprintf('%.3F %.3F %.3F rg %.2F %.2F %.2F %.2F re f', $r, $g, $b, $x, $y, $width, $height));
    }

    public function line(float $x1, float $top1, float $x2, float $top2, array $color = [0.85, 0.88, 0.86], float $width = 0.6): void
    {
        [$r, $g, $b] = $this->rgb($color);
        $this->append(sprintf(
            '%.3F %.3F %.3F RG %.2F w %.2F %.2F m %.2F %.2F l S',
            $r,
            $g,
            $b,
            $width,
            $x1,
            self::HEIGHT - $top1,
            $x2,
            self::HEIGHT - $top2
        ));
    }

    /**
     * Divide texto em linhas que cabem na largura indicada.
     *
     * @return list<string>
     */
    public function lines(string $text, float $maxWidth, float $size, bool $bold = false): array
    {
        $result = [];

        foreach (preg_split('/\R/u', $text) ?: [] as $paragraph) {
            $paragraph = trim($paragraph);

            if ($paragraph === '') {
                $result[] = '';
                continue;
            }

            $words = preg_split('/\s+/u', $paragraph) ?: [];
            $line = '';

            foreach ($words as $word) {
                $candidate = $line === '' ? $word : $line . ' ' . $word;

                if ($this->width($candidate, $size, $bold) <= $maxWidth) {
                    $line = $candidate;
                    continue;
                }

                if ($line !== '') {
                    $result[] = $line;
                    $line = '';
                }

                // Palavras maiores do que a coluna são cortadas.
                while ($this->width($word, $size, $bold) > $maxWidth && mb_strlen($word) > 1) {
                    $chunk = '';
                    $length = mb_strlen($word);
                    for ($i = 0; $i < $length; $i++) {
                        $next = $chunk . mb_substr($word, $i, 1);
                        if ($this->width($next, $size, $bold) > $maxWidth) {
                            break;
                        }
                        $chunk = $next;
                    }

                    if ($chunk === '') {
                        $chunk = mb_substr($word, 0, 1);
                    }

                    $result[] = $chunk;
                    $word = mb_substr($word, mb_strlen($chunk));
                }

                $line = $word;
            }

            if ($line !== '') {
                $result[] = $line;
            }
        }

        return $result;
    }

    public function width(string $text, float $size, bool $bold = false): float
    {
        $total = 0.0;
        $length = mb_strlen($text);

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($text, $i, 1);
            $total += $this->charWidth($char, $size, $bold);
        }

        return $total;
    }

    public function footer(int $page, string $left, string $right): void
    {
        $index = $page - 1;
        if (!isset($this->footers[$index])) {
            return;
        }

        $this->footers[$index] = $left . "\x1F" . $right;
    }

    public function output(): string
    {
        $pageCount = count($this->pages);

        // Rodapé em todas as páginas, com numeração final.
        foreach ($this->footers as $index => $footer) {
            if ($footer === '') {
                continue;
            }

            [$left, $right] = explode("\x1F", $footer, 2);
            $top = self::HEIGHT - 34;
            $this->pages[$index] .= "\n";
            $this->pages[$index] .= sprintf(
                "BT /F1 7.5 Tf 0.42 0.48 0.45 rg 1 0 0 1 48 %.2F Tm (%s) Tj ET\n",
                $top,
                $this->escape($left)
            );
            $rightWidth = $this->width($right, 7.5, false);
            $this->pages[$index] .= sprintf(
                "BT /F1 7.5 Tf 0.42 0.48 0.45 rg 1 0 0 1 %.2F %.2F Tm (%s) Tj ET\n",
                self::WIDTH - 48 - $rightWidth,
                $top,
                $this->escape($right)
            );
        }

        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        $pageObjectStart = 5;
        $contentObjectStart = $pageObjectStart + $pageCount;
        $kids = [];

        for ($i = 0; $i < $pageCount; $i++) {
            $pageObject = $pageObjectStart + $i;
            $contentObject = $contentObjectStart + $i;
            $kids[] = $pageObject . ' 0 R';
            $objects[$pageObject] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>',
                self::WIDTH,
                self::HEIGHT,
                $contentObject
            );
            $content = $this->pages[$i];
            $objects[$contentObject] = '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
        }

        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . $pageCount . ' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];

        foreach ($objects as $number => $body) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $maxObject = max(array_keys($objects));
        $pdf .= 'xref' . "\n" . '0 ' . ($maxObject + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= $maxObject; $i++) {
            if (!isset($offsets[$i])) {
                $pdf .= "0000000000 00000 f \n";
                continue;
            }
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size " . ($maxObject + 1) . " /Root 1 0 R >>\n";
        $pdf .= 'startxref' . "\n" . $xrefOffset . "\n%%EOF\n";

        return $pdf;
    }

    private function append(string $command): void
    {
        $index = count($this->pages) - 1;

        if ($index < 0) {
            throw new RuntimeException('O PDF não tem páginas.');
        }

        $this->pages[$index] .= $command . "\n";
    }

    /** @return array{0: float, 1: float, 2: float} */
    private function rgb(array $color): array
    {
        return [
            max(0.0, min(1.0, (float) ($color[0] ?? 0))),
            max(0.0, min(1.0, (float) ($color[1] ?? 0))),
            max(0.0, min(1.0, (float) ($color[2] ?? 0))),
        ];
    }

    private function escape(string $text): string
    {
        $encoded = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);

        if ($encoded === false) {
            $encoded = preg_replace('/[^\x20-\x7E]/', '?', $text) ?? $text;
        }

        return str_replace(['\\', '(', ')', "\r"], ['\\\\', '\\(', '\\)', ''], $encoded);
    }

    private function charWidth(string $char, float $size, bool $bold): float
    {
        if ($char === ' ') {
            return 0.28 * $size;
        }

        if (str_contains("il.,'!|:;", $char)) {
            return 0.26 * $size;
        }

        if (str_contains('MW@%', $char)) {
            return 0.86 * $size;
        }

        if (preg_match('/[A-ZÀ-ÖØ-Þ]/u', $char) === 1) {
            return 0.65 * $size;
        }

        if (preg_match('/[0-9]/', $char) === 1) {
            return 0.556 * $size;
        }

        return (0.48 + ($bold ? 0.035 : 0)) * $size;
    }
}
