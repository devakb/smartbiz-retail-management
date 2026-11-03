<?php

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

test('settings update requires settings.manage permission', function () {
    $user = User::factory()->create([
        'session_version' => 1,
    ]);

    Permission::findOrCreate('settings.manage');

    $response = $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->put(route('settings.update'), [
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_position' => 'before',
            'currency_decimals' => 2,
            'theme_color' => '#112233',
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

    $response->assertForbidden();
});

test('users with settings.manage can update settings', function () {
    $this->seed(SettingsSeeder::class);
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'session_version' => 1,
    ]);
    $user->givePermissionTo('settings.manage');

    $response = $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->put(route('settings.update'), [
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_position' => 'after',
            'currency_decimals' => 3,
            'theme_color' => '#112233',
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Settings updated successfully.',
        ]);

    expect(Setting::get('currency_code'))->toBe('USD')
        ->and(Setting::get('currency_symbol'))->toBe('$')
        ->and(Setting::get('currency_position'))->toBe('after')
        ->and(Setting::get('currency_decimals'))->toBe('3')
        ->and(Setting::get('theme_color'))->toBe('#112233');
});

test('settings page requires settings.manage permission', function () {
    $user = User::factory()->create([
        'session_version' => 1,
    ]);

    Permission::findOrCreate('settings.manage');

    $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->get(route('settings.edit'))
        ->assertForbidden();
});
