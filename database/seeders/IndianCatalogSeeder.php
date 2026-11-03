<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeds an India-focused demo catalog:
 * 2 categories, 6 brands, 50+ products (INR MRP / GST tax classes).
 *
 * Usage:
 *   php artisan db:seed --class=IndianCatalogSeeder
 *
 * Safe to re-run (uses updateOrCreate on slug / sku).
 */
class IndianCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TaxClassSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
