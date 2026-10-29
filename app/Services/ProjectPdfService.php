<?php

declare(strict_types=1);

namespace ObraClara\Services;

use ObraClara\Support\Labels;
use ObraClara\Support\Pdf;

/**
 * Documento de gestão da obra. Usa apenas dados reais e cêntimos inteiros.
 * Não é uma fatura nem um contrato: essa limitação é impressa no documento.
 */
final class ProjectPdfService
{
    private const MARGIN = 48.0;
    private const CONTENT = 499.28;
    private const BOTTOM = 760.0;

    private const INK = [0.07, 0.12, 0.10];
    private const MUTED = [0.38, 0.44, 0.41];
    private const GREEN = [0.04, 0.28, 0.18];
    private const GREEN_SOFT = [0.91, 0.96, 0.93];
    private const LINE = [0.85, 0.88, 0.86];
    private const WHITE = [1, 1, 1];
    private const RED = [0.68, 0.14, 0.10];
    private const AMBER = [0.55, 0.36, 0.05];

    /**
     * @param array<string, mixed> $project
     * @param list<array<string, mixed>> $changes
     */
    public static function generate(array $project, array $changes): string
    {
        $pdf = new Pdf();
        $page = 1;
        self::firstHeader($pdf, $project);

        $y = 112.0;
        $y = self::paragraph($pdf, t('pdf.title'), self::MARGIN, $y, self::CONTENT, 9, 12, true, self::GREEN);
        $y += 3;
        $y = self::paragraph($pdf, (string) $project['title'], self::MARGIN, $y, self::CONTENT, 20, 25, true, self::INK);
        $y += 4;

        $y = self::metadata($pdf, $project, $y);
        $y += 18;
        $y = self::budgetBoxes($pdf, $project, $y);
        $y += 26;

        [$y, $page] = self::changesTable($pdf, $changes, $y, $page, $project);
        $y += 22;
        [$y, $page] = self::decisions($pdf, $changes, $y, $page, $project);
        $y += 16;

        if ($y > 690) {
            $pdf->addPage();
            $page++;
            self::continuationHeader($pdf, $project, $page);
            $y = 92;
        }

        self::disclaimer($pdf, $y);

        $total = $pdf->pageCount();
        for ($i = 1; $i <= $total; $i++) {
            $pdf->footer(
                $i,
                t('pdf.footer') . ' · ' . format_datetime(now()),
                t('pdf.page', ['page' => $i, 'total' => $total])
            );
        }

        return $pdf->output();
    }

    /** @param array<string, mixed> $project */
    private static function firstHeader(Pdf $pdf, array $project): void
    {
        $pdf->rect(0, 0, Pdf::WIDTH, 84, self::GREEN);
        $pdf->rect(self::MARGIN, 22, 28, 28, [0.93, 0.98, 0.95]);
        $pdf->text(self::MARGIN + 8, 42, 'OC', 11, true, self::GREEN);

        $pdf->text(self::MARGIN + 40, 32, 'ObraClara', 17, true, self::WHITE);
        $pdf->text(self::MARGIN + 40, 48, t('app.tagline'), 8.5, false, [0.78, 0.91, 0.84]);
        $pdf->text(Pdf::WIDTH - self::MARGIN - 150, 32, t('pdf.footer'), 8, false, [0.78, 0.91, 0.84]);
        $pdf->text(Pdf::WIDTH - self::MARGIN - 150, 48, format_datetime(now()), 8, false, [0.78, 0.91, 0.84]);
    }

    /** @param array<string, mixed> $project */
    private static function continuationHeader(Pdf $pdf, array $project, int $page): void
    {
        $pdf->rect(0, 0, Pdf::WIDTH, 54, self::GREEN);
        $pdf->text(self::MARGIN, 25, 'ObraClara', 12, true, self::WHITE);
        $pdf->text(self::MARGIN, 40, (string) $project['title'], 8.5, false, [0.8, 0.92, 0.86]);
        $pdf->text(Pdf::WIDTH - self::MARGIN - 60, 32, 'PDF · ' . $page, 8, false, [0.8, 0.92, 0.86]);
    }

    /**
     * @param array<string, mixed> $project
     */
    private static function metadata(Pdf $pdf, array $project, float $y): float
    {
        $column = self::CONTENT / 2;
        $rows = [
            [t('pdf.client'), (string) $project['client_name']],
            [t('pdf.status'), Labels::projectStatus((string) $project['status'])],
            [t('pdf.start'), $project['start_date'] !== null ? format_date((string) $project['start_date']) : '—'],
            [t('pdf.generated_label'), format_datetime(now())],
        ];

        foreach ($rows as $index => $row) {
            $x = self::MARGIN + (($index % 2) * $column);
            $top = $y + (intdiv($index, 2) * 35);
            $pdf->text($x, $top, $row[0], 7.5, true, self::MUTED);
            $lines = $pdf->lines($row[1], $column - 14, 9.5, false);
            $pdf->text($x, $top + 13, $lines[0] ?? '—', 9.5, false, self::INK);
        }

        return $y + 76;
    }

    /** @param array<string, mixed> $project */
    private static function budgetBoxes(Pdf $pdf, array $project, float $y): float
    {
        $gap = 10.0;
        $width = (self::CONTENT - ($gap * 2)) / 3;
        $boxes = [
            [t('pdf.budget'), money((int) $project['base_budget_cents']), self::WHITE, self::INK],
            [t('pdf.approved_total'), '+ ' . money((int) $project['approved_cents']), self::WHITE, self::INK],
            [t('pdf.updated_total'), money((int) $project['updated_budget_cents']), self::GREEN_SOFT, self::GREEN],
        ];

        foreach ($boxes as $index => $box) {
            $x = self::MARGIN + ($index * ($width + $gap));
            $pdf->rect($x, $y, $width, 62, $box[2], 4);
            $pdf->line($x, $y, $x + $width, $y, self::LINE, 0.5);
            $pdf->text($x + 10, $y + 17, $box[0], 7.5, false, self::MUTED);
            $value = $box[1];
            $size = $pdf->width($value, 14, true) > $width - 20 ? 11 : 14;
            $pdf->text($x + 10, $y + 40, $value, $size, true, $box[3]);
        }

        return $y + 62;
    }

    /**
     * @param list<array<string, mixed>> $changes
     * @param array<string, mixed> $project
     * @return array{0: float, 1: int}
     */
    private static function changesTable(Pdf $pdf, array $changes, float $y, int $page, array $project): array
    {
        $pdf->text(self::MARGIN, $y, t('pdf.changes'), 12, true, self::INK);
        $pdf->text(Pdf::WIDTH - self::MARGIN - 30, $y, (string) count($changes), 10, true, self::MUTED);
        $y += 16;

        if ($changes === []) {
            $pdf->text(self::MARGIN, $y, t('pdf.no_changes'), 9, false, self::MUTED);

            return [$y + 20, $page];
        }

        $y = self::tableHeader($pdf, $y);

        foreach ($changes as $change) {
            $titleLines = $pdf->lines((string) $change['title'], 250, 9.5, true);
            $description = $change['description'] !== null ? (string) $change['description'] : '';
            $descriptionLines = $description !== '' ? $pdf->lines($description, 250, 8, false) : [];
            $rowHeight = max(34.0, 16 + (count($titleLines) * 12) + (count($descriptionLines) * 10) + 8);

            if ($y + $rowHeight > self::BOTTOM) {
                $pdf->addPage();
                $page++;
                self::continuationHeader($pdf, $project, $page);
                $y = 78;
                $y = self::tableHeader($pdf, $y);
            }

            $pdf->line(self::MARGIN, $y + $rowHeight, Pdf::WIDTH - self::MARGIN, $y + $rowHeight, self::LINE, 0.5);
            $lineTop = $y + 13;

            foreach ($titleLines as $line) {
                $pdf->text(self::MARGIN, $lineTop, $line, 9.5, true, self::INK);
                $lineTop += 12;
            }

            foreach ($descriptionLines as $line) {
                $pdf->text(self::MARGIN, $lineTop, $line, 8, false, self::MUTED);
                $lineTop += 10;
            }

            $amount = money((int) $change['amount_cents']);
            $amountWidth = $pdf->width($amount, 9, true);
            $pdf->text(347 - $amountWidth, $y + 13, $amount, 9, true, self::INK);

            $status = Labels::changeStatus((string) $change['status']);
            $statusColor = match ($change['status']) {
                'aprovada' => self::GREEN,
                'rejeitada' => self::RED,
                'pendente' => self::AMBER,
                default => self::MUTED,
            };
            $pdf->text(424, $y + 13, $status, 8.5, true, $statusColor);

            if ($change['decided_at'] !== null) {
                $pdf->text(
                    424,
                    $y + 25,
                    format_date((string) $change['decided_at']),
                    7.5,
                    false,
                    self::MUTED
                );
            }

            $y += $rowHeight;
        }

        return [$y + 8, $page];
    }

    private static function tableHeader(Pdf $pdf, float $y): float
    {
        $pdf->rect(self::MARGIN, $y, self::CONTENT, 22, [0.95, 0.97, 0.96]);
        $pdf->text(self::MARGIN + 8, $y + 14, t('pdf.change'), 7.5, true, self::MUTED);
        $pdf->text(347 - $pdf->width(t('pdf.amount'), 7.5, true), $y + 14, t('pdf.amount'), 7.5, true, self::MUTED);
        $pdf->text(424, $y + 14, t('pdf.change_status'), 7.5, true, self::MUTED);

        return $y + 22;
    }

    /**
     * @param list<array<string, mixed>> $changes
     * @return array{0: float, 1: int}
     */
    private static function decisions(Pdf $pdf, array $changes, float $y, int $page, array $project): array
    {
        $decided = array_values(array_filter(
            $changes,
            static fn (array $change): bool => $change['decided_at'] !== null
        ));

        $pdf->text(self::MARGIN, $y, t('pdf.decisions'), 12, true, self::INK);
        $y += 18;

        if ($decided === []) {
            $pdf->text(self::MARGIN, $y, t('pdf.empty'), 9, false, self::MUTED);

            return [$y + 18, $page];
        }

        foreach ($decided as $change) {
            $noteLines = $change['decision_note'] !== null
                ? $pdf->lines((string) $change['decision_note'], self::CONTENT - 36, 8.5, false)
                : [];
            $height = 34 + (count($noteLines) * 11);

            if ($y + $height > self::BOTTOM) {
                $pdf->addPage();
                $page++;
                self::continuationHeader($pdf, $project, $page);
                $y = 80;
            }

            $pdf->rect(self::MARGIN, $y, 3, $height - 8, $change['status'] === 'aprovada' ? self::GREEN : self::RED);
            $pdf->text(self::MARGIN + 14, $y + 10, (string) $change['title'], 9.5, true, self::INK);
            $pdf->text(self::MARGIN + 14, $y + 23, t('pdf.decided_by', [
                'name' => $change['decider_name'] ?? '—',
                'date' => format_datetime((string) $change['decided_at']),
            ]), 8, false, self::MUTED);

            $lineTop = $y + 35;
            foreach ($noteLines as $line) {
                $pdf->text(self::MARGIN + 14, $lineTop, $line, 8.5, false, self::INK);
                $lineTop += 11;
            }

            $y += $height;
        }

        return [$y, $page];
    }

    private static function disclaimer(Pdf $pdf, float $y): void
    {
        $pdf->rect(self::MARGIN, $y, self::CONTENT, 42, [0.96, 0.97, 0.96]);
        $pdf->line(self::MARGIN, $y, self::MARGIN + self::CONTENT, $y, self::LINE, 0.6);
        $pdf->text(self::MARGIN + 10, $y + 16, t('pdf.disclaimer'), 7.5, false, self::MUTED);
        $pdf->text(self::MARGIN + 10, $y + 29, 'ObraClara · ' . format_datetime(now()), 7.5, false, self::MUTED);
    }

    /** @param array<string, mixed> $project */
    private static function paragraph(
        Pdf $pdf,
        string $text,
        float $x,
        float $y,
        float $width,
        float $size,
        float $leading,
        bool $bold,
        array $color
    ): float {
        foreach ($pdf->lines($text, $width, $size, $bold) as $line) {
            $pdf->text($x, $y, $line, $size, $bold, $color);
            $y += $leading;
        }

        return $y;
    }
}
