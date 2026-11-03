<?php

namespace Database\Seeders;

use App\Models\TaxClass;
use Illuminate\Database\Seeder;

class TaxClassSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                'name' => 'GST 0%',
                'rate' => 0,
                'description' => 'Exempt / nil-rated GST',
                'is_active' => true,
            ],
            [
                'name' => 'GST 5%',
                'rate' => 5,
                'description' => 'GST at 5%',
                'is_active' => true,
            ],
            [
                'name' => 'GST 12%',
                'rate' => 12,
                'description' => 'GST at 12%',
                'is_active' => true,
            ],
            [
                'name' => 'GST 18%',
                'rate' => 18,
                'description' => 'GST at 18%',
                'is_active' => true,
            ],
        ];

        foreach ($defaults as $taxClass) {
            TaxClass::query()->updateOrCreate(
                ['name' => $taxClass['name']],
                $taxClass
            );
        }
    }
}
