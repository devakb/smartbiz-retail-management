<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class WalkInCustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::query()->updateOrCreate(
            ['is_walk_in' => true],
            [
                'name' => 'Walk-in Customer',
                'email' => null,
                'phone' => null,
                'billing_address' => null,
                'shipping_address' => null,
                'is_active' => true,
                'outstanding_balance' => 0,
            ]
        );
    }
}
