<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء جميع الصلاحيات - غيّر guard إلى web
        $permissions = [
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',
            'orders.view',
            'orders.create',
            'orders.edit',
            'orders.delete',
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']  // ← غيّر من sanctum إلى web
            );
        }

        // إنشاء الأدوار - غيّر guard إلى web
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super_admin'],
            ['guard_name' => 'web']  // ← غيّر من sanctum إلى web
        );
        
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['guard_name' => 'web']  // ← غيّر من sanctum إلى web
        );
        
        $managerRole = Role::firstOrCreate(
            ['name' => 'manager'],
            ['guard_name' => 'web']  // ← غيّر من sanctum إلى web
        );
        
        $employeeRole = Role::firstOrCreate(
            ['name' => 'employee'],
            ['guard_name' => 'web']  // ← غيّر من sanctum إلى web
        );

        // إعطاء جميع الصلاحيات للـ super_admin
        $superAdminRole->syncPermissions(Permission::all());
        
        // إعطاء صلاحيات للـ admin
        $adminRole->syncPermissions([
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',
            'orders.view',
            'orders.create',
            'orders.edit',
            'users.view',
            'users.create',
        ]);
        
        // إعطاء صلاحيات للـ manager
        $managerRole->syncPermissions([
            'products.view',
            'products.create',
            'products.edit',
            'categories.view',
            'orders.view',
            'orders.create',
            'orders.edit',
            'users.view',
        ]);
        
        // إعطاء صلاحيات للـ employee
        $employeeRole->syncPermissions([
            'products.view',
            'categories.view',
            'orders.view',
            'orders.create',
        ]);

        // إنشاء المستخدمين
        
        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'),
            ]
        );
        $superAdmin->assignRole('super_admin');
        
        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin2@test.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('12345678'),
            ]
        );
        $admin->assignRole('admin');
        
        // Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@test.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('12345678'),
            ]
        );
        $manager->assignRole('manager');
        
        // Employee
        $employee = User::firstOrCreate(
            ['email' => 'employee@test.com'],
            [
                'name' => 'Employee User',
                'password' => Hash::make('12345678'),
            ]
        );
        $employee->assignRole('employee');

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Login Credentials:');
        $this->command->info('==================');
        $this->command->info('Super Admin: admin@test.com / 12345678');
        $this->command->info('Admin: admin2@test.com / 12345678');
        $this->command->info('Manager: manager@test.com / 12345678');
        $this->command->info('Employee: employee@test.com / 12345678');
    }
}