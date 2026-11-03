<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Indian retail categories for demo / local development.
     *
     * @return list<array{name: string, slug: string, description: string}>
     */
    public static function catalog(): array
    {
        return [
            [
                'name' => 'Grocery & Staples',
                'slug' => 'grocery-staples',
                'description' => 'Rice, atta, dals, oils, spices and everyday cooking essentials.',
            ],
            [
                'name' => 'Snacks & Beverages',
                'slug' => 'snacks-beverages',
                'description' => 'Biscuits, namkeen, soft drinks, tea, coffee and packaged snacks.',
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::catalog() as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'parent_id' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
