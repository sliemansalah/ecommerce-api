<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // مستخدم تجريبي - Admin
        User::create([
            'name' => 'أحمد محمد',
            'email' => 'admin@example.com',
            'phone' => '0599123456',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        // مستخدم تجريبي - User
        User::create([
            'name' => 'سارة علي',
            'email' => 'sara@example.com',
            'phone' => '0599654321',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        // مستخدم معطل
        User::create([
            'name' => 'محمد خالد',
            'email' => 'mohammad@example.com',
            'phone' => '0599111222',
            'password' => Hash::make('password'),
            'is_active' => false,
        ]);
    }
}