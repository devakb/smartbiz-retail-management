<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Popular Indian / India-focused FMCG brands for demo / local development.
     *
     * @return list<array{name: string, slug: string, description: string}>
     */
    public static function catalog(): array
    {
        return [
            [
                'name' => 'Amul',
                'slug' => 'amul',
                'description' => 'Gujarat Cooperative Milk Marketing Federation — dairy and beverages.',
            ],
            [
                'name' => 'Britannia',
                'slug' => 'britannia',
                'description' => 'Indian bakery brand — biscuits, breads and dairy snacks.',
            ],
            [
                'name' => 'Parle',
                'slug' => 'parle',
                'description' => 'Parle Products — biscuits, sweets and confectionery.',
            ],
            [
                'name' => 'Nestlé',
                'slug' => 'nestle',
                'description' => 'Nestlé India — Maggi, coffee, dairy and packaged foods.',
            ],
            [
                'name' => 'ITC',
                'slug' => 'itc',
                'description' => 'ITC Limited — Aashirvaad, Sunfeast, Bingo and Yippee.',
            ],
            [
                'name' => 'Tata Consumer',
                'slug' => 'tata-consumer',
                'description' => 'Tata Consumer Products — Tata Salt, Tata Tea and Himalayan.',
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::catalog() as $brand) {
            Brand::query()->updateOrCreate(
                ['slug' => $brand['slug']],
                [
                    'name' => $brand['name'],
                    'description' => $brand['description'],
                    'logo' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
