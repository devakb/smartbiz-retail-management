<?php

namespace App\Support;

class ThemeColor
{
    /**
     * Build CSS custom properties for TailAdmin brand tokens from a hex primary.
     *
     * @return array<string, string>
     */
    public static function cssVariables(?string $hex = null): array
    {
        $base = self::normalize($hex ?? '#465fff');

        [$r, $g, $b] = self::hexToRgb($base);

        return [
            '--color-brand-25' => self::mix($r, $g, $b, 255, 255, 255, 0.94),
            '--color-brand-50' => self::mix($r, $g, $b, 255, 255, 255, 0.90),
            '--color-brand-100' => self::mix($r, $g, $b, 255, 255, 255, 0.80),
            '--color-brand-200' => self::mix($r, $g, $b, 255, 255, 255, 0.65),
            '--color-brand-300' => self::mix($r, $g, $b, 255, 255, 255, 0.45),
            '--color-brand-400' => self::mix($r, $g, $b, 255, 255, 255, 0.25),
            '--color-brand-500' => $base,
            '--color-brand-600' => self::mix($r, $g, $b, 0, 0, 0, 0.12),
            '--color-brand-700' => self::mix($r, $g, $b, 0, 0, 0, 0.24),
            '--color-brand-800' => self::mix($r, $g, $b, 0, 0, 0, 0.36),
            '--color-brand-900' => self::mix($r, $g, $b, 0, 0, 0, 0.48),
            '--color-brand-950' => self::mix($r, $g, $b, 0, 0, 0, 0.64),
        ];
    }

    public static function inlineStyle(?string $hex = null): string
    {
        $lines = [];

        foreach (self::cssVariables($hex) as $property => $value) {
            $lines[] = $property.': '.$value.';';
        }

        return implode(' ', $lines);
    }

    protected static function normalize(string $hex): string
    {
        $hex = trim($hex);

        if (preg_match('/^#([A-Fa-f0-9]{3})$/', $hex, $matches) === 1) {
            $chars = str_split($matches[1]);

            return '#'.$chars[0].$chars[0].$chars[1].$chars[1].$chars[2].$chars[2];
        }

        if (preg_match('/^#([A-Fa-f0-9]{6})$/', $hex) === 1) {
            return strtolower($hex);
        }

        return '#465fff';
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    protected static function hexToRgb(string $hex): array
    {
        $value = ltrim($hex, '#');

        return [
            hexdec(substr($value, 0, 2)),
            hexdec(substr($value, 2, 2)),
            hexdec(substr($value, 4, 2)),
        ];
    }

    protected static function mix(int $r, int $g, int $b, int $tr, int $tg, int $tb, float $amount): string
    {
        $nr = (int) round($r + ($tr - $r) * $amount);
        $ng = (int) round($g + ($tg - $g) * $amount);
        $nb = (int) round($b + ($tb - $b) * $amount);

        return sprintf('#%02x%02x%02x', $nr, $ng, $nb);
    }
}
