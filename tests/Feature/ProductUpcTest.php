<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeProductUser(array $permissions = ['products.create', 'products.view', 'products.update']): User
{
    test()->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'session_version' => 1,
    ]);
    $user->givePermissionTo($permissions);

    return $user;
}

function makeCategory(): Category
{
    return Category::query()->create([
        'name' => 'General',
        'slug' => 'general-'.uniqid(),
        'is_active' => true,
    ]);
}

test('upc uniqueness create fails on duplicate', function () {
    $user = makeProductUser();
    $category = makeCategory();

    Product::query()->create([
        'upc' => '123456789012',
        'sku' => 'SKU-EXISTING',
        'name' => 'Existing Product',
        'category_id' => $category->id,
        'mrp' => 10,
        'selling_price' => 15,
        'stock_quantity' => 0,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->post(route('product.store'), [
            'upc' => '123456789012',
            'sku' => 'SKU-NEW',
            'name' => 'Duplicate UPC Product',
            'category_id' => $category->id,
            'mrp' => 5,
            'selling_price' => 8,
            'is_active' => 1,
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['upc']);
});

test('upc check endpoint returns available vs exists', function () {
    $user = makeProductUser(['products.view', 'products.create']);
    $category = makeCategory();

    Product::query()->create([
        'upc' => '998877665544',
        'sku' => 'SKU-CHECK',
        'name' => 'Checked Product',
        'category_id' => $category->id,
        'mrp' => 10,
        'selling_price' => 15,
        'stock_quantity' => 0,
        'is_active' => true,
    ]);

    $available = $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->getJson(route('product.check-upc', ['upc' => '111222333444']));

    $available->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'available')
        ->assertJsonPath('data.upc', '111222333444');

    $exists = $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->getJson(route('product.check-upc', ['upc' => ' 9988 7766 5544 ']));

    $exists->assertOk()
        ->assertJsonPath('data.status', 'exists')
        ->assertJsonPath('data.upc', '998877665544')
        ->assertJsonPath('data.product.name', 'Checked Product');
});
