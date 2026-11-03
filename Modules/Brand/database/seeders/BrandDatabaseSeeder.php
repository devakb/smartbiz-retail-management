<?php

namespace Modules\Brand\Database\Seeders;

use Database\Seeders\BrandSeeder;
use Illuminate\Database\Seeder;

class BrandDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            BrandSeeder::class,
        ]);
    }
}
