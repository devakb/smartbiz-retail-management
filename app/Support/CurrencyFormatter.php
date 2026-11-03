<?php

namespace App\Support;

/**
 * Alias helper for money formatting via application settings.
 */
class CurrencyFormatter
{
    public static function format(float|int|string|null $amount): string
    {
        return Money::format($amount);
    }
}
