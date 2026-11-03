<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('product create form shows add brand and category actions when permitted', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'session_version' => 1,
    ]);
    $user->givePermissionTo([
        'products.create',
        'products.view',
        'brands.create',
        'categories.create',
    ]);

    $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->get(route('product.create'))
        ->assertOk()
        ->assertSee('Add Category', false)
        ->assertSee('Add Brand', false)
        ->assertSee('id="quick-add-category-form"', false)
        ->assertSee('id="quick-add-brand-form"', false)
        ->assertSee(route('category.store'), false)
        ->assertSee(route('brand.store'), false);
});

test('product create form hides add brand and category actions without create permissions', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'session_version' => 1,
    ]);
    $user->givePermissionTo([
        'products.create',
        'products.view',
    ]);

    $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->get(route('product.create'))
        ->assertOk()
        ->assertDontSee('id="quick-add-category-form"', false)
        ->assertDontSee('id="quick-add-brand-form"', false);
});
