<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\TaxClass;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\WalkInCustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeCustomerOrderUser(array $permissions = [
    'customers.manage',
    'orders.view',
    'orders.create',
    'orders.update',
    'orders.cancel',
]): User
{
    test()->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'session_version' => 1,
    ]);
    $user->givePermissionTo($permissions);

    return $user;
}

function makeOrderTaxClass(float $rate): ?TaxClass
{
    if ($rate <= 0) {
        return null;
    }

    return TaxClass::query()->firstOrCreate(
        ['name' => 'Order Tax '.$rate],
        ['rate' => $rate, 'is_active' => true],
    );
}

function makeOrderProduct(array $overrides = []): Product
{
    $category = Category::query()->create([
        'name' => 'Order Cat',
        'slug' => 'order-cat-'.uniqid(),
        'is_active' => true,
    ]);

    $taxPercent = array_key_exists('tax_percent', $overrides)
        ? (float) $overrides['tax_percent']
        : 10.0;
    unset($overrides['tax_percent']);

    $taxClass = makeOrderTaxClass($taxPercent);

    return Product::query()->create(array_merge([
        'upc' => '123456789012',
        'sku' => 'SKU-ORD-'.uniqid(),
        'name' => 'Order Product',
        'category_id' => $category->id,
        'tax_class_id' => $taxClass?->id,
        'mrp' => 50,
        'selling_price' => 100,
        'min_stock_level' => 1,
        'stock_quantity' => 20,
        'is_active' => true,
    ], $overrides));
}

test('can create a customer', function () {
    $user = makeCustomerOrderUser();

    $response = $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->post(route('customer.store'), [
            'name' => 'Acme Retail',
            'email' => 'acme@example.com',
            'phone' => '9999999999',
            'billing_address' => '123 Main St',
            'shipping_address' => '123 Main St',
            'is_active' => 1,
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Customer created successfully.',
        ]);

    $customer = Customer::query()->where('email', 'acme@example.com')->first();

    expect($customer)->not->toBeNull()
        ->and($customer->name)->toBe('Acme Retail')
        ->and($customer->is_walk_in)->toBeFalse()
        ->and($customer->is_active)->toBeTrue();
});

test('cannot delete walk-in customer', function () {
    $user = makeCustomerOrderUser();
    $this->seed(WalkInCustomerSeeder::class);

    $walkIn = Customer::query()->where('is_walk_in', true)->firstOrFail();

    $response = $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->delete(route('customer.destroy', $walkIn), [], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['customer']);

    expect(Customer::query()->whereKey($walkIn->id)->exists())->toBeTrue();
});

test('create order deducts stock once', function () {
    $user = makeCustomerOrderUser();
    $this->seed(WalkInCustomerSeeder::class);
    $customer = Customer::query()->where('is_walk_in', true)->firstOrFail();
    $product = makeOrderProduct(['stock_quantity' => 10, 'selling_price' => 100, 'tax_percent' => 10]);

    $response = $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->post(route('order.store'), [
            'customer_id' => $customer->id,
            'status' => OrderStatus::Confirmed->value,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

    $response->assertCreated()->assertJson(['success' => true]);

    expect((float) $product->fresh()->stock_quantity)->toBe(8.0);

    $order = Order::query()->latest('id')->first();
    expect($order)->not->toBeNull()
        ->and($order->status)->toBe(OrderStatus::Confirmed)
        ->and($order->invoice_number)->not->toBeNull();

    $saleMovements = StockMovement::query()
        ->where('product_id', $product->id)
        ->where('type', StockMovementType::Sale)
        ->where('reference_id', $order->id)
        ->get();

    expect($saleMovements)->toHaveCount(1)
        ->and((float) $saleMovements->first()->quantity)->toBe(2.0);

    // Confirming again / re-create path should not double-deduct for same order.
    $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->put(route('order.status', $order), [
            'status' => OrderStatus::Confirmed->value,
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->assertOk();

    expect((float) $product->fresh()->stock_quantity)->toBe(8.0)
        ->and(
            StockMovement::query()
                ->where('product_id', $product->id)
                ->where('type', StockMovementType::Sale)
                ->where('reference_id', $order->id)
                ->count()
        )->toBe(1);
});

test('cancel reverses stock', function () {
    $user = makeCustomerOrderUser();
    $this->seed(WalkInCustomerSeeder::class);
    $customer = Customer::query()->where('is_walk_in', true)->firstOrFail();
    $product = makeOrderProduct(['stock_quantity' => 15, 'selling_price' => 50, 'tax_percent' => 0]);

    $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->post(route('order.store'), [
            'customer_id' => $customer->id,
            'status' => OrderStatus::Confirmed->value,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->assertCreated();

    expect((float) $product->fresh()->stock_quantity)->toBe(12.0);

    $order = Order::query()->latest('id')->firstOrFail();

    $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->post(route('order.cancel', $order), [], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and((float) $product->fresh()->stock_quantity)->toBe(15.0);

    expect(
        StockMovement::query()
            ->where('product_id', $product->id)
            ->where('type', StockMovementType::SaleReversal)
            ->where('reference_id', $order->id)
            ->count()
    )->toBe(1);
});

test('order totals are computed server-side and ignore tampered client total', function () {
    $user = makeCustomerOrderUser();
    $this->seed(WalkInCustomerSeeder::class);
    $customer = Customer::query()->where('is_walk_in', true)->firstOrFail();
    $product = makeOrderProduct([
        'stock_quantity' => 10,
        'selling_price' => 100,
        'tax_percent' => 10,
    ]);

    // Client tries to force a tiny total and fake unit prices.
    $response = $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->post(route('order.store'), [
            'customer_id' => $customer->id,
            'status' => OrderStatus::Confirmed->value,
            'subtotal' => 1,
            'tax_amount' => 0,
            'total' => 1,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 1,
                    'line_total' => 1,
                ],
            ],
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

    $response->assertCreated();

    $order = Order::query()->latest('id')->firstOrFail();
    $item = $order->items()->first();

    // 2 * 100 = 200 subtotal; tax 10% = 20; total = 220
    expect((float) $item->unit_price)->toBe(100.0)
        ->and((float) $item->line_total)->toBe(220.0)
        ->and((float) $order->subtotal)->toBe(200.0)
        ->and((float) $order->tax_amount)->toBe(20.0)
        ->and((float) $order->total)->toBe(220.0)
        ->and($order->payment_status)->toBe(PaymentStatus::Unpaid);
});
