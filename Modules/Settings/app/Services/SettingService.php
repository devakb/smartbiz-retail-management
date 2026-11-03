<?php

namespace Modules\Settings\Services;

use App\Models\Setting;

class SettingService
{
    /**
     * @return array{
     *     currency_code: string,
     *     currency_symbol: string,
     *     currency_position: string,
     *     currency_decimals: string,
     *     theme_color: string
     * }
     */
    public function all(): array
    {
        return [
            'currency_code' => (string) Setting::get('currency_code', 'INR'),
            'currency_symbol' => (string) Setting::get('currency_symbol', '₹'),
            'currency_position' => (string) Setting::get('currency_position', 'before'),
            'currency_decimals' => (string) Setting::get('currency_decimals', '2'),
            'theme_color' => (string) Setting::get('theme_color', '#465fff'),
        ];
    }

    /**
     * @param  array{
     *     currency_code: string,
     *     currency_symbol: string,
     *     currency_position: string,
     *     currency_decimals: int|string,
     *     theme_color: string
     * }  $data
     * @return array<string, string>
     */
    public function update(array $data): array
    {
        Setting::setMany([
            'currency_code' => $data['currency_code'],
            'currency_symbol' => $data['currency_symbol'],
            'currency_position' => $data['currency_position'],
            'currency_decimals' => (string) $data['currency_decimals'],
            'theme_color' => $data['theme_color'],
        ]);

        return $this->all();
    }
}
