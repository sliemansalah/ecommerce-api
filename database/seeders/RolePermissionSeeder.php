<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // إنشاء الصلاحيات (Permissions)
        $permissions = [
            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            
            // Product Management
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            
            // Category Management
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',
            
            // Order Management
            'orders.view',
            'orders.create',
            'orders.edit',
            'orders.delete',
            'orders.approve',
            
            // Accounting
            'accounting.view',
            'accounting.create',
            'accounting.edit',
            'accounting.delete',
            'accounting.reports',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // إنشاء الأدوار (Roles)
        
        // Super Admin - كل الصلاحيات
        $superAdmin = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - صلاحيات إدارية
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo([
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            'orders.view', 'orders.edit', 'orders.approve',
            'accounting.view', 'accounting.reports',
        ]);

        // Accountant - صلاحيات محاسبية
        $accountant = Role::create(['name' => 'accountant', 'guard_name' => 'web']);
        $accountant->givePermissionTo([
            'accounting.view',
            'accounting.create',
            'accounting.edit',
            'accounting.reports',
            'orders.view',
        ]);

        // Customer - زبون عادي
        $customer = Role::create(['name' => 'customer', 'guard_name' => 'web']);
        $customer->givePermissionTo([
            'products.view',
            'categories.view',
            'orders.view',
            'orders.create',
        ]);
    }
}