<?php

namespace Modules\Product\Database\Seeders;

use Database\Seeders\IndianCatalogSeeder;
use Illuminate\Database\Seeder;

class ProductDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Prefer IndianCatalogSeeder for a full India demo catalog
     * (tax classes + categories + brands + products).
     */
    public function run(): void
    {
        $this->call([
            IndianCatalogSeeder::class,
        ]);
    }
}
