<?php

namespace Modules\TaxClass\Database\Seeders;

use Database\Seeders\TaxClassSeeder;
use Illuminate\Database\Seeder;

class TaxClassDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            TaxClassSeeder::class,
        ]);
    }
}
