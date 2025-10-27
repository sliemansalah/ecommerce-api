<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * تسجيل الدخول
     * 
     * POST /api/login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // محاولة تسجيل الدخول
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();
        
        // تحميل العلاقات
        $user->load(['roles.permissions']);
        
        // إنشاء Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => $user,
            ]
        ], 200);
    }

    /**
     * التسجيل
     * 
     * POST /api/register
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // إضافة دور افتراضي
        $user->assignRole('employee');

        // تحميل العلاقات
        $user->load(['roles.permissions']);

        // إنشاء Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful',
            'data' => [
                'token' => $token,
                'user' => $user,
            ]
        ], 201);
    }

    /**
     * تسجيل الخروج
     * 
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        // حذف جميع tokens المستخدم
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout successful'
        ], 200);
    }

    /**
     * الحصول على المستخدم الحالي
     * 
     * GET /api/me
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load(['roles.permissions']);

        return response()->json([
            'status' => 'success',
            'data' => $user
        ], 200);
    }
}