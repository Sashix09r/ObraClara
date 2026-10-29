<?php

declare(strict_types=1);

namespace ObraClara\Support;

/**
 * Internacionalização PT/EN com um único dicionário por idioma.
 * Nenhuma regra de negócio depende do idioma: apenas a apresentação.
 */
final class Lang
{
    public const LOCALES = ['pt', 'en'];

    private static string $locale = 'pt';

    /** @var array<string, array<string, string>>|null */
    private static ?array $dictionaries = null;

    public static function boot(): void
    {
        $requested = $_GET['lang'] ?? null;

        if (is_string($requested) && in_array($requested, self::LOCALES, true)) {
            $_SESSION['locale'] = $requested;
        }

        $session = $_SESSION['locale'] ?? null;
        self::$locale = is_string($session) && in_array($session, self::LOCALES, true) ? $session : 'pt';
    }

    public static function setLocale(string $locale): void
    {
        if (!in_array($locale, self::LOCALES, true)) {
            return;
        }

        self::$locale = $locale;
        $_SESSION['locale'] = $locale;
    }

    public static function locale(): string
    {
        return self::$locale;
    }

    /** Código BCP-47 para o atributo HTML lang. */
    public static function htmlLocale(): string
    {
        return self::$locale === 'en' ? 'en-GB' : 'pt-PT';
    }

    /** @param array<string, string|int> $params */
    public static function t(string $key, array $params = []): string
    {
        $dictionaries = self::dictionaries();
        $text = $dictionaries[self::$locale][$key]
            ?? $dictionaries['pt'][$key]
            ?? $key;

        if ($params === []) {
            return $text;
        }

        $replace = [];
        foreach ($params as $name => $value) {
            $replace[':' . $name] = (string) $value;
        }

        return strtr($text, $replace);
    }

    public static function has(string $key): bool
    {
        $dictionaries = self::dictionaries();

        return isset($dictionaries[self::$locale][$key]) || isset($dictionaries['pt'][$key]);
    }

    /** @return array<string, string> */
    public static function dictionary(string $locale): array
    {
        $dictionaries = self::dictionaries();

        return $dictionaries[$locale] ?? [];
    }

    /** @return array<string, array<string, string>> */
    private static function dictionaries(): array
    {
        if (self::$dictionaries !== null) {
            return self::$dictionaries;
        }

        $dictionaries = [];
        foreach (self::LOCALES as $locale) {
            $path = OBRACLARA_ROOT . '/lang/' . $locale . '.php';
            $dictionaries[$locale] = is_file($path) ? (array) require $path : [];
        }

        return self::$dictionaries = $dictionaries;
    }
}
