<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // إعادة تعيين الـ cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // إنشاء الصلاحيات
        $permissions = [
            // Users
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            
            // Roles
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
            
            // Products
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',
            
            // Orders
            'view-orders',
            'create-orders',
            'edit-orders',
            'delete-orders',
            
            // Reports
            'view-reports',
            'export-reports',
            
            // Settings
            'manage-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // إنشاء الأدوار
        
        // 1. Super Admin - كل الصلاحيات
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // 2. Admin - معظم الصلاحيات
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view-users', 'create-users', 'edit-users',
            'view-roles',
            'view-products', 'create-products', 'edit-products', 'delete-products',
            'view-orders', 'create-orders', 'edit-orders',
            'view-reports', 'export-reports',
        ]);

        // 3. Manager - صلاحيات إدارية محدودة
        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo([
            'view-users',
            'view-products', 'edit-products',
            'view-orders', 'edit-orders',
            'view-reports',
        ]);

        // 4. Employee - صلاحيات محدودة جداً
        $employee = Role::create(['name' => 'employee']);
        $employee->givePermissionTo([
            'view-products',
            'view-orders',
        ]);

        // 5. Customer - صلاحيات العملاء
        $customer = Role::create(['name' => 'customer']);
        $customer->givePermissionTo([
            'view-products',
            'create-orders',
            'view-orders',
        ]);

        // إنشاء مستخدمين تجريبيين
        
        // Super Admin
        $superAdminUser = User::create([
            'name' => 'سوبر أدمن',
            'email' => 'superadmin@example.com',
            'phone' => '0599000001',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $superAdminUser->assignRole('super-admin');

        // Admin
        $adminUser = User::create([
            'name' => 'أحمد محمد',
            'email' => 'admin@example.com',
            'phone' => '0599000002',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $adminUser->assignRole('admin');

        // Manager
        $managerUser = User::create([
            'name' => 'سارة علي',
            'email' => 'manager@example.com',
            'phone' => '0599000003',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $managerUser->assignRole('manager');

        // Employee
        $employeeUser = User::create([
            'name' => 'محمد خالد',
            'email' => 'employee@example.com',
            'phone' => '0599000004',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $employeeUser->assignRole('employee');

        // Customer
        $customerUser = User::create([
            'name' => 'فاطمة أحمد',
            'email' => 'customer@example.com',
            'phone' => '0599000005',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $customerUser->assignRole('customer');
    }
}