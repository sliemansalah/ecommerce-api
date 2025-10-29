<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //  // إنشاء 5 مستخدمين
        // User::factory(5)->create()->each(function ($user) {
        //     // لكل مستخدم، أنشئ 3 مقالات
        //     Post::factory(3)->create([
        //         'user_id' => $user->id,
        //     ]);
        // });

        //   // مستخدم تجريبي خاص
        // $testUser = User::factory()->create([
        //     'name' => 'موسى',
        //     'email' => 'test@example.com',
        // ]);

        // // مقالات المستخدم التجريبي
        // Post::factory(5)->create([
        //     'user_id' => $testUser->id,
        // ]);

        Category::create(['name' => 'تقنية', 'slug' => 'tech', 'description' => 'مقالات تقنية']);
        Category::create(['name' => 'رياضة', 'slug' => 'sports', 'description' => 'أخبار رياضية']);


    }
}
