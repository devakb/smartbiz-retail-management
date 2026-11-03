<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeReportUser(array $permissions = ['reports.view']): User
{
    test()->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'session_version' => 1,
    ]);
    $user->givePermissionTo($permissions);

    return $user;
}

function makeLowStockProduct(array $overrides = []): Product
{
    $category = Category::query()->create([
        'name' => 'Report Cat',
        'slug' => 'report-cat-'.uniqid(),
        'is_active' => true,
    ]);

    return Product::query()->create(array_merge([
        'upc' => '990011223344',
        'sku' => 'SKU-REP-'.uniqid(),
        'name' => 'Low Stock Widget',
        'category_id' => $category->id,
        'mrp' => 25,
        'selling_price' => 50,
        'min_stock_level' => 10,
        'stock_quantity' => 3,
        'is_active' => true,
    ], $overrides));
}

test('dashboard requires auth', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

test('authenticated user can view dashboard widgets', function () {
    $user = makeReportUser([]);

    $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('SmartBiz Dashboard')
        ->assertSee('Products')
        ->assertSee('Monthly sales')
        ->assertSee('Inventory value');
});

test('sales report requires reports.view', function () {
    $user = makeReportUser([]);

    $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->get(route('report.sales'))
        ->assertForbidden();
});

test('sales report is accessible with reports.view', function () {
    $user = makeReportUser(['reports.view']);
    $customer = Customer::query()->create([
        'name' => 'Report Customer',
        'is_walk_in' => false,
        'is_active' => true,
    ]);

    Order::query()->create([
        'order_number' => 'ORD-REP-1',
        'invoice_number' => 'INV-REP-1',
        'customer_id' => $customer->id,
        'user_id' => $user->id,
        'status' => OrderStatus::Confirmed,
        'payment_status' => PaymentStatus::Paid,
        'subtotal' => 100,
        'discount_amount' => 0,
        'tax_amount' => 10,
        'total' => 110,
    ]);

    $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->get(route('report.sales'))
        ->assertOk()
        ->assertSee('Sales Report')
        ->assertSee('ORD-REP-1');
});

test('low stock report returns expected product', function () {
    $user = makeReportUser(['reports.view']);
    $low = makeLowStockProduct([
        'name' => 'Critical Low Item',
        'stock_quantity' => 2,
        'min_stock_level' => 8,
    ]);
    makeLowStockProduct([
        'name' => 'Healthy Stock Item',
        'upc' => '990011223355',
        'sku' => 'SKU-REP-OK-'.uniqid(),
        'stock_quantity' => 50,
        'min_stock_level' => 5,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->get(route('report.low-stock'));

    $response->assertOk()
        ->assertSee('Critical Low Item')
        ->assertViewHas('rows', function ($rows) {
            $names = collect($rows->items())->pluck('name');

            return $names->contains('Critical Low Item')
                && ! $names->contains('Healthy Stock Item');
        });

    expect($low->fresh()->stock_quantity)->toBeLessThanOrEqual($low->min_stock_level);
});
