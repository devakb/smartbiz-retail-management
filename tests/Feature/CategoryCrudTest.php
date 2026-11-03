<?php

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

test('category index requires categories.view permission', function () {
    $user = User::factory()->create([
        'session_version' => 1,
    ]);

    Permission::findOrCreate('categories.view');

    $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->get(route('category.index'))
        ->assertForbidden();
});

test('users with categories.create can create a category', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'session_version' => 1,
    ]);
    $user->givePermissionTo(['categories.create', 'categories.view']);

    $parent = Category::query()->create([
        'name' => 'Parent',
        'slug' => 'parent',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->post(route('category.store'), [
            'name' => 'Electronics',
            'description' => 'Gadgets and devices',
            'parent_id' => $parent->id,
            'is_active' => 1,
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Category created successfully.',
        ]);

    $this->assertDatabaseHas('categories', [
        'name' => 'Electronics',
        'parent_id' => $parent->id,
        'is_active' => 1,
    ]);
});

test('users with categories.update can update a category', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'session_version' => 1,
    ]);
    $user->givePermissionTo(['categories.update', 'categories.view']);

    $category = Category::query()->create([
        'name' => 'Old Name',
        'slug' => 'old-name',
        'description' => 'Before',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->put(route('category.update', $category), [
            'name' => 'New Name',
            'description' => 'After',
            'parent_id' => null,
            'is_active' => 0,
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Category updated successfully.',
        ]);

    expect($category->fresh())
        ->name->toBe('New Name')
        ->description->toBe('After')
        ->is_active->toBeFalse();
});
