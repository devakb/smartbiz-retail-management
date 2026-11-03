<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',
            'brands.view',
            'brands.create',
            'brands.update',
            'brands.delete',
            'tax-classes.view',
            'tax-classes.create',
            'tax-classes.update',
            'tax-classes.delete',
            'inventory.view',
            'inventory.adjust',
            'orders.view',
            'orders.create',
            'orders.update',
            'orders.cancel',
            'orders.delete',
            'customers.manage',
            'offers.manage',
            'reports.view',
            'pos.access',
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $superAdmin = Role::findOrCreate('Super Admin');
        $admin = Role::findOrCreate('Admin');
        $inventoryManager = Role::findOrCreate('Inventory Manager');
        $supervisor = Role::findOrCreate('Supervisor');
        $cashier = Role::findOrCreate('Cashier');

        $superAdmin->syncPermissions(Permission::all());

        $admin->syncPermissions([
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',
            'brands.view',
            'brands.create',
            'brands.update',
            'brands.delete',
            'tax-classes.view',
            'tax-classes.create',
            'tax-classes.update',
            'tax-classes.delete',
            'inventory.view',
            'inventory.adjust',
            'orders.view',
            'orders.create',
            'orders.update',
            'orders.cancel',
            'orders.delete',
            'customers.manage',
            'offers.manage',
            'reports.view',
            'pos.access',
            'settings.manage',
        ]);

        $inventoryManager->syncPermissions([
            'products.view',
            'products.create',
            'products.update',
            'categories.view',
            'categories.create',
            'categories.update',
            'brands.view',
            'brands.create',
            'brands.update',
            'tax-classes.view',
            'tax-classes.create',
            'tax-classes.update',
            'inventory.view',
            'inventory.adjust',
            'reports.view',
        ]);

        $supervisor->syncPermissions([
            'products.view',
            'categories.view',
            'brands.view',
            'tax-classes.view',
            'inventory.view',
            'orders.view',
            'orders.create',
            'orders.update',
            'orders.cancel',
            'orders.delete',
            'customers.manage',
            'offers.manage',
            'reports.view',
            'pos.access',
        ]);

        $cashier->syncPermissions([
            'products.view',
            'categories.view',
            'brands.view',
            'tax-classes.view',
            'inventory.view',
            'orders.view',
            'orders.create',
            'customers.manage',
            'pos.access',
        ]);

        $adminUser = User::query()->where('email', 'admin@smartbiz.test')->first();
        if ($adminUser) {
            $adminUser->syncRoles([$superAdmin]);
        }
    }
}
