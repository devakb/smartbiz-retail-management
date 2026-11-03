<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function makePosUser(): User
{
    test()->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'session_version' => 1,
    ]);
    $user->givePermissionTo(['pos.access', 'orders.view']);

    return $user;
}

function makePosTaxClass(float $rate): ?TaxClass
{
    if ($rate <= 0) {
        return null;
    }

    return TaxClass::query()->firstOrCreate(
        ['name' => 'POS Tax '.$rate],
        ['rate' => $rate, 'is_active' => true],
    );
}

function makePosProduct(array $overrides = []): Product
{
    $category = Category::query()->create([
        'name' => 'POS Cat',
        'slug' => 'pos-cat-'.uniqid(),
        'is_active' => true,
    ]);

    $taxPercent = array_key_exists('tax_percent', $overrides)
        ? (float) $overrides['tax_percent']
        : 10.0;
    unset($overrides['tax_percent']);

    $taxClass = makePosTaxClass($taxPercent);

    return Product::query()->create(array_merge([
        'upc' => '8901001001001',
        'sku' => 'SKU-POS-'.uniqid(),
        'name' => 'POS Product',
        'category_id' => $category->id,
        'tax_class_id' => $taxClass?->id,
        'mrp' => 40,
        'selling_price' => 100,
        'min_stock_level' => 1,
        'stock_quantity' => 20,
        'is_active' => true,
    ], $overrides));
}

function posCheckoutPayload(Customer $customer, Product $product, array $overrides = []): array
{
    $token = (string) Str::uuid();

    return array_replace_recursive([
        'checkout_token' => $token,
        'customer_id' => $customer->id,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 2],
        ],
        'payment' => [
            'method' => PaymentMethod::Cash->value,
            'amount_received' => 250,
        ],
    ], $overrides);
}

function actingAsPos(User $user)
{
    return test()->actingAs($user)->withSession(['session_version' => 1]);
}

test('checkout cash success deducts stock once', function () {
    $user = makePosUser();
    $this->seed(WalkInCustomerSeeder::class);
    $customer = Customer::query()->where('is_walk_in', true)->firstOrFail();
    $product = makePosProduct(['stock_quantity' => 10, 'selling_price' => 100, 'tax_percent' => 10]);

    $payload = posCheckoutPayload($customer, $product, [
        'payment' => [
            'method' => PaymentMethod::Cash->value,
            'amount_received' => 250,
        ],
    ]);

    $response = actingAsPos($user)
        ->postJson(route('pos.checkout'), $payload);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Sale completed successfully.',
        ])
        ->assertJsonPath('data.order.invoice_number', fn ($value) => filled($value))
        ->assertJsonPath('data.print_url', fn ($value) => filled($value));

    expect((float) $product->fresh()->stock_quantity)->toBe(8.0);

    $order = Order::query()->latest('id')->firstOrFail();
    expect($order->status)->toBe(OrderStatus::Confirmed)
        ->and($order->payment_status)->toBe(PaymentStatus::Paid)
        ->and((float) $order->total)->toBe(220.0);

    $saleMovements = StockMovement::query()
        ->where('product_id', $product->id)
        ->where('type', StockMovementType::Sale)
        ->where('reference_id', $order->id)
        ->get();

    expect($saleMovements)->toHaveCount(1)
        ->and((float) $saleMovements->first()->quantity)->toBe(2.0);

    $payment = $order->payments()->first();
    expect($payment)->not->toBeNull()
        ->and($payment->method)->toBe(PaymentMethod::Cash)
        ->and((float) $payment->amount_received)->toBe(250.0)
        ->and((float) $payment->change_amount)->toBe(30.0);
});

test('checkout card with reference', function () {
    $user = makePosUser();
    $this->seed(WalkInCustomerSeeder::class);
    $customer = Customer::query()->where('is_walk_in', true)->firstOrFail();
    $product = makePosProduct(['stock_quantity' => 5, 'tax_percent' => 0, 'selling_price' => 50]);

    $payload = posCheckoutPayload($customer, $product, [
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
        'payment' => [
            'method' => PaymentMethod::Card->value,
            'reference' => 'CARD-REF-123',
        ],
    ]);

    actingAsPos($user)
        ->postJson(route('pos.checkout'), $payload)
        ->assertCreated()
        ->assertJson(['success' => true]);

    $order = Order::query()->latest('id')->firstOrFail();
    $payment = $order->payments()->first();

    expect($payment->method)->toBe(PaymentMethod::Card)
        ->and($payment->reference)->toBe('CARD-REF-123')
        ->and((float) $product->fresh()->stock_quantity)->toBe(4.0);
});

test('checkout upi with reference', function () {
    $user = makePosUser();
    $this->seed(WalkInCustomerSeeder::class);
    $customer = Customer::query()->where('is_walk_in', true)->firstOrFail();
    $product = makePosProduct(['stock_quantity' => 5, 'tax_percent' => 0, 'selling_price' => 75, 'upc' => '8901001001002']);

    $payload = posCheckoutPayload($customer, $product, [
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
        'payment' => [
            'method' => PaymentMethod::Upi->value,
            'reference' => 'UPI-TXN-999',
        ],
    ]);

    actingAsPos($user)
        ->postJson(route('pos.checkout'), $payload)
        ->assertCreated()
        ->assertJson(['success' => true]);

    $order = Order::query()->latest('id')->firstOrFail();
    $payment = $order->payments()->first();

    expect($payment->method)->toBe(PaymentMethod::Upi)
        ->and($payment->reference)->toBe('UPI-TXN-999')
        ->and((float) $product->fresh()->stock_quantity)->toBe(4.0);
});

test('oversell is blocked', function () {
    $user = makePosUser();
    $this->seed(WalkInCustomerSeeder::class);
    $customer = Customer::query()->where('is_walk_in', true)->firstOrFail();
    $product = makePosProduct(['stock_quantity' => 2, 'tax_percent' => 0]);

    $payload = posCheckoutPayload($customer, $product, [
        'items' => [['product_id' => $product->id, 'quantity' => 5]],
        'payment' => [
            'method' => PaymentMethod::Cash->value,
            'amount_received' => 1000,
        ],
    ]);

    actingAsPos($user)
        ->postJson(route('pos.checkout'), $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['items']);

    expect((float) $product->fresh()->stock_quantity)->toBe(2.0)
        ->and(Order::query()->count())->toBe(0);
});

test('duplicate checkout token is blocked', function () {
    $user = makePosUser();
    $this->seed(WalkInCustomerSeeder::class);
    $customer = Customer::query()->where('is_walk_in', true)->firstOrFail();
    $product = makePosProduct(['stock_quantity' => 10, 'tax_percent' => 0, 'selling_price' => 100]);

    $token = (string) Str::uuid();
    $payload = posCheckoutPayload($customer, $product, [
        'checkout_token' => $token,
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
        'payment' => [
            'method' => PaymentMethod::Cash->value,
            'amount_received' => 100,
        ],
    ]);

    actingAsPos($user)
        ->postJson(route('pos.checkout'), $payload)
        ->assertCreated();

    expect((float) $product->fresh()->stock_quantity)->toBe(9.0)
        ->and(Cache::has('pos:checkout:'.$token))->toBeTrue();

    actingAsPos($user)
        ->postJson(route('pos.checkout'), $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['checkout_token']);

    expect((float) $product->fresh()->stock_quantity)->toBe(9.0)
        ->and(Order::query()->count())->toBe(1)
        ->and(
            StockMovement::query()
                ->where('product_id', $product->id)
                ->where('type', StockMovementType::Sale)
                ->count()
        )->toBe(1);
});

test('inactive product cannot be sold', function () {
    $user = makePosUser();
    $this->seed(WalkInCustomerSeeder::class);
    $customer = Customer::query()->where('is_walk_in', true)->firstOrFail();
    $product = makePosProduct([
        'stock_quantity' => 10,
        'is_active' => false,
        'upc' => '8901001001999',
    ]);

    $payload = posCheckoutPayload($customer, $product, [
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
        'payment' => [
            'method' => PaymentMethod::Cash->value,
            'amount_received' => 500,
        ],
    ]);

    actingAsPos($user)
        ->postJson(route('pos.checkout'), $payload)
        ->assertStatus(422);

    expect((float) $product->fresh()->stock_quantity)->toBe(10.0)
        ->and(Order::query()->count())->toBe(0);
});

test('pos scan rejects inactive product', function () {
    $user = makePosUser();
    $product = makePosProduct([
        'upc' => '8901001001888',
        'is_active' => false,
        'stock_quantity' => 5,
    ]);

    actingAsPos($user)
        ->getJson(route('pos.scan', ['upc' => $product->upc]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['upc']);
});
