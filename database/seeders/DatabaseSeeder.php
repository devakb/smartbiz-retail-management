<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@smartbiz.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'session_version' => 0,
            ]
        );

        $this->call([
            RolesAndPermissionsSeeder::class,
            SettingsSeeder::class,
            WalkInCustomerSeeder::class,
            TaxClassSeeder::class,
            IndianCatalogSeeder::class,
        ]);
    }
}
