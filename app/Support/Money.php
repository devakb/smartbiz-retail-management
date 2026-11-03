<?php

namespace App\Support;

use App\Models\Setting;

class Money
{
    public static function format(float|int|string|null $amount): string
    {
        $value = (float) ($amount ?? 0);
        $decimals = (int) Setting::get('currency_decimals', 2);
        $symbol = (string) Setting::get('currency_symbol', '₹');
        $position = (string) Setting::get('currency_position', 'before');
        $formatted = number_format($value, max(0, $decimals), '.', ',');

        return $position === 'after'
            ? $formatted.$symbol
            : $symbol.$formatted;
    }

    public static function symbol(): string
    {
        return (string) Setting::get('currency_symbol', '₹');
    }

    public static function code(): string
    {
        return (string) Setting::get('currency_code', 'INR');
    }

    public static function decimals(): int
    {
        return (int) Setting::get('currency_decimals', 2);
    }
}
