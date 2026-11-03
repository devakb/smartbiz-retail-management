<?php

use App\Helpers\MenuHelper;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('groups sidebar items into the expected sections for super admin', function () {
    $user = User::factory()->create();
    $role = Role::findOrCreate('Super Admin');
    $user->assignRole($role);
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    // Super Admin Gate::before may grant all; ensure permissions exist from seeder context
    $this->actingAs($user);

    $groups = MenuHelper::getMenuGroups();
    $titles = array_column($groups, 'title');

    expect($titles)->toContain(
        'Overview',
        'Product Management',
        'Sales & Customers',
        'Marketing',
        'Analytics',
        'System Management',
        'Account',
    );

    $flat = collect($groups)->flatMap(fn (array $g) => collect($g['items'])->pluck('name'))->all();

    expect($flat)->toBe([
        'Dashboard',
        'Categories',
        'Brands',
        'Tax Classes',
        'Products',
        'Inventory',
        'Orders',
        'POS',
        'Customers',
        'Offers',
        'Reports',
        'Users',
        'Settings',
        'Profile',
    ]);
});


it('hides empty product management group when user lacks catalog permissions', function () {
    Permission::findOrCreate('pos.access');

    $user = User::factory()->create();
    $user->givePermissionTo('pos.access');

    $this->actingAs($user);

    $groups = MenuHelper::getMenuGroups();
    $titles = array_column($groups, 'title');

    expect($titles)->toContain('Overview', 'Sales & Customers', 'Account')
        ->and($titles)->not->toContain('Product Management', 'Marketing', 'Analytics', 'System Management');

    $salesItems = collect($groups)->firstWhere('title', 'Sales & Customers')['items'] ?? [];

    expect(collect($salesItems)->pluck('name')->all())->toBe(['POS']);
});
