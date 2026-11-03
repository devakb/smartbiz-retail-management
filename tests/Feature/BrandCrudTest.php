<?php

use App\Models\Brand;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

test('brand index requires brands.view permission', function () {
    $user = User::factory()->create([
        'session_version' => 1,
    ]);

    Permission::findOrCreate('brands.view');

    $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->get(route('brand.index'))
        ->assertForbidden();
});

test('users with brands.create can create a brand', function () {
    Storage::fake('public');
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'session_version' => 1,
    ]);
    $user->givePermissionTo(['brands.create', 'brands.view']);

    $logo = UploadedFile::fake()->image('acme.png', 100, 100);

    $response = $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->post(route('brand.store'), [
            'name' => 'Acme',
            'description' => 'Quality goods',
            'logo' => $logo,
            'is_active' => 1,
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Brand created successfully.',
        ]);

    $brand = Brand::query()->where('name', 'Acme')->first();

    expect($brand)->not->toBeNull()
        ->and($brand->description)->toBe('Quality goods')
        ->and($brand->is_active)->toBeTrue()
        ->and($brand->logo)->not->toBeNull();

    Storage::disk('public')->assertExists($brand->logo);
});
