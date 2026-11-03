<?php

namespace Modules\Category\Database\Seeders;

use Database\Seeders\CategorySeeder;
use Illuminate\Database\Seeder;

class CategoryDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
        ]);
    }
}
