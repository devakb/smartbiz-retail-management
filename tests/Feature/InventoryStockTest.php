<?php

use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeInventoryUser(array $permissions = ['inventory.view', 'inventory.adjust']): User
{
    test()->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'session_version' => 1,
    ]);
    $user->givePermissionTo($permissions);

    return $user;
}

function makeStockProduct(array $overrides = []): Product
{
    $category = Category::query()->create([
        'name' => 'Stock Cat',
        'slug' => 'stock-cat-'.uniqid(),
        'is_active' => true,
    ]);

    return Product::query()->create(array_merge([
        'upc' => '555666777888',
        'sku' => 'SKU-STOCK-'.uniqid(),
        'name' => 'Stock Product',
        'category_id' => $category->id,
        'mrp' => 10,
        'selling_price' => 20,
        'min_stock_level' => 5,
        'stock_quantity' => 10,
        'is_active' => true,
    ], $overrides));
}

test('inventory index lists products', function () {
    $user = makeInventoryUser(['inventory.view']);
    $product = makeStockProduct(['stock_quantity' => 12]);

    $response = $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->get(route('inventory.index'));

    $response->assertOk()
        ->assertSee($product->name)
        ->assertSee('12');
});

test('inventory update sets stock and creates adjustment movement', function () {
    $user = makeInventoryUser();
    $product = makeStockProduct(['stock_quantity' => 10, 'min_stock_level' => 5]);

    $response = $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->put(route('inventory.update', $product), [
            'stock_quantity' => 18,
            'min_stock_level' => 4,
            'reason' => 'Cycle count correction',
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Inventory updated successfully.',
        ]);

    $fresh = $product->fresh();
    expect((float) $fresh->stock_quantity)->toBe(18.0)
        ->and((float) $fresh->min_stock_level)->toBe(4.0);

    $movement = StockMovement::query()->where('product_id', $product->id)->latest('id')->first();

    expect($movement)->not->toBeNull()
        ->and($movement->type)->toBe(StockMovementType::Adjustment)
        ->and((float) $movement->quantity_before)->toBe(10.0)
        ->and((float) $movement->quantity_after)->toBe(18.0)
        ->and($movement->reason)->toBe('Cycle count correction');
});

test('inventory update requires reason', function () {
    $user = makeInventoryUser();
    $product = makeStockProduct(['stock_quantity' => 8]);

    $response = $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->put(route('inventory.update', $product), [
            'stock_quantity' => 5,
            'min_stock_level' => 2,
            'reason' => '',
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['reason']);

    expect((float) $product->fresh()->stock_quantity)->toBe(8.0);
});
