<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'currency_code' => 'INR',
            'currency_symbol' => '₹',
            'currency_position' => 'before',
            'currency_decimals' => '2',
            'theme_color' => '#0F766E',
        ];

        foreach ($defaults as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
