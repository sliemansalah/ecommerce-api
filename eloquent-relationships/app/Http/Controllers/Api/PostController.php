<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    /**
     * الحصول على جميع المقالات
     */
    public function index(): JsonResponse
    {
        $posts = Post::with('user')->get();
        
        return response()->json([
            'success' => true,
            'data' => $posts,
        ]);
    }

    /**
     * الحصول على مقال محدد
     */
    public function show(Post $post): JsonResponse
    {
        $post->load('user');
        
        return response()->json([
            'success' => true,
            'data' => $post,
        ]);
    }

    /**
     * الحصول على مقالات مستخدم محدد
     */
    public function userPosts(User $user): JsonResponse
    {
        $posts = $user->posts;
        
        return response()->json([
            'success' => true,
            'data' => $posts,
            'user' => $user->only(['id', 'name', 'email']),
        ]);
    }
    public function latest(): JsonResponse
    {
        $posts = Post::with('user')
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();
            return response()->json([
                'success' => true,
                'data' => $posts,
            ]);
    }
}