<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('user index requires users.view permission', function () {
    $user = User::factory()->create([
        'session_version' => 1,
    ]);

    Permission::findOrCreate('users.view');

    $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->get(route('users.index'))
        ->assertForbidden();
});

test('users with users.view can open the user index', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'session_version' => 1,
    ]);
    $user->givePermissionTo('users.view');

    $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->get(route('users.index'))
        ->assertOk();
});

test('users cannot delete themselves', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'session_version' => 1,
    ]);
    $user->givePermissionTo('users.delete');
    $user->assignRole(Role::findOrCreate('Cashier'));

    $response = $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->delete(route('users.destroy', $user), [], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['user']);

    expect(User::query()->whereKey($user->id)->exists())->toBeTrue();
});
