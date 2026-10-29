<?php

declare(strict_types=1);

namespace ObraClara\Support;

use InvalidArgumentException;

/**
 * Conversão e formatação de dinheiro.
 *
 * Toda a aplicação trabalha em CÊNTIMOS INTEIROS. Nunca se somam euros
 * em vírgula flutuante, evitando erros de arredondamento (0.1 + 0.2).
 */
final class Money
{
    /** Limite máximo aceite: 99 999 999,99 € (8 dígitos de euros). */
    public const MAX_CENTS = 9_999_999_999;

    /**
     * Aceita 650, 650,00, 650.00, 1 234,56, 1.234,56 e 1,234.56.
     *
     * Regras explícitas:
     * - com vírgula e ponto, o ÚLTIMO separador é o decimal (1.234,56 e 1,234.56);
     * - só com vírgula, é separador decimal (10,50);
     * - só com pontos, um padrão 1.234.567 é interpretado como milhares;
     * - "1.234" é 1 234 €; "12.50" é 12,50 €;
     * - são rejeitados negativos, texto, mais de duas casas decimais e overflow.
     *
     * @param bool $allowZero permite 0 (usado no orçamento base da obra)
     * @throws InvalidArgumentException com mensagem pronta para o utilizador
     */
    public static function parse(string $raw, bool $allowZero = false): int
    {
        $value = trim($raw);

        if ($value === '') {
            throw new InvalidArgumentException('Indica um valor em euros.');
        }

        // Remove espaços (incluindo não separáveis) e o símbolo do euro.
        $value = preg_replace('/[\s\x{00A0}\x{202F}€]/u', '', $value) ?? '';

        if ($value === '' || !preg_match('/^\d[\d.,]*$/', $value)) {
            throw new InvalidArgumentException('Usa apenas números, com vírgula ou ponto para os cêntimos.');
        }

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $lastComma = (int) strrpos($value, ',');
            $lastDot = (int) strrpos($value, '.');

            if ($lastComma > $lastDot) {
                $integer = str_replace('.', '', substr($value, 0, $lastComma));
                $decimals = substr($value, $lastComma + 1);
            } else {
                $integer = str_replace(',', '', substr($value, 0, $lastDot));
                $decimals = substr($value, $lastDot + 1);
            }

            if ($integer === '' || !ctype_digit($integer) || $decimals === '' || !ctype_digit($decimals) || strlen($decimals) > 2) {
                throw new InvalidArgumentException('O valor deve ter no máximo duas casas decimais.');
            }
        } elseif (str_contains($value, ',')) {
            if (substr_count($value, ',') > 1) {
                throw new InvalidArgumentException('Usa apenas uma vírgula decimal.');
            }

            [$integer, $decimals] = explode(',', $value, 2);
            $integer = str_replace('.', '', $integer);

            if ($integer === '' || !ctype_digit($integer) || !ctype_digit($decimals) || $decimals === '' || strlen($decimals) > 2) {
                throw new InvalidArgumentException('O valor deve ter no máximo duas casas decimais.');
            }
        } elseif (str_contains($value, '.')) {
            if (preg_match('/^\d{1,3}(?:\.\d{3})+$/', $value) === 1) {
                $integer = str_replace('.', '', $value);
                $decimals = '0';
            } else {
                if (substr_count($value, '.') > 1) {
                    throw new InvalidArgumentException('O valor deve ter no máximo duas casas decimais.');
                }

                [$integer, $decimals] = explode('.', $value, 2);

                if (!ctype_digit($integer) || !ctype_digit($decimals) || $decimals === '' || strlen($decimals) > 2) {
                    throw new InvalidArgumentException('O valor deve ter no máximo duas casas decimais.');
                }
            }
        } else {
            $integer = $value;
            $decimals = '0';
        }

        if ($integer === '' || strlen($integer) > 9) {
            throw new InvalidArgumentException('O valor é demasiado elevado.');
        }

        $cents = ((int) $integer) * 100 + (int) str_pad($decimals, 2, '0');

        if ($allowZero && $cents === 0) {
            return 0;
        }

        if ($cents <= 0) {
            throw new InvalidArgumentException('O valor deve ser superior a zero.');
        }

        if ($cents > self::MAX_CENTS) {
            throw new InvalidArgumentException('O valor máximo é 99 999 999,99 €.');
        }

        return $cents;
    }

    /**
     * Formata cêntimos em euros no formato pt-PT, sem usar vírgula flutuante.
     */
    public static function format(int $cents): string
    {
        $negative = $cents < 0;
        $cents = abs($cents);

        $euros = intdiv($cents, 100);
        $remainder = $cents % 100;

        return ($negative ? '−' : '')
            . number_format($euros, 0, ',', ' ')
            . ',' . str_pad((string) $remainder, 2, '0', STR_PAD_LEFT)
            . ' €';
    }

    /**
     * Valor para pré-preencher um campo de formulário (ex.: 650.00).
     */
    public static function toInput(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
