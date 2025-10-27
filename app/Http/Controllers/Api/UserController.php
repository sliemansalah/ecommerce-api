<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * عرض قائمة المستخدمين مع Pagination و Filters
     * 
     * GET /api/users
     * 
     * Query Parameters:
     * - page: رقم الصفحة (default: 1)
     * - per_page: عدد العناصر في الصفحة (default: 10)
     * - search: البحث في الاسم والبريد
     * - role_id: تصفية حسب الدور
     * - sort_by: الترتيب حسب (name, email, created_at)
     * - sort_order: اتجاه الترتيب (asc, desc)
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $roleId = $request->input('role_id');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        // Query Builder
        $query = User::with(['roles.permissions']);

        // البحث في الاسم والبريد
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // التصفية حسب الدور
        if ($roleId) {
            $query->whereHas('roles', function ($q) use ($roleId) {
                $q->where('roles.id', $roleId);
            });
        }

        // الترتيب
        $allowedSortColumns = ['name', 'email', 'created_at'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $users = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Users retrieved successfully',
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ]
        ], 200);
    }

    /**
     * عرض مستخدم واحد
     * 
     * GET /api/users/{id}
     */
    public function show($id)
    {
        $user = User::with(['roles.permissions'])->find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $user
        ], 200);
    }

    /**
     * إنشاء مستخدم جديد
     * 
     * POST /api/users
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
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

        // إضافة الأدوار
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        // إعادة تحميل العلاقات
        $user->load(['roles.permissions']);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * تحديث مستخدم
     * 
     * PUT /api/users/{id}
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // تحديث الأدوار
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        // إعادة تحميل العلاقات
        $user->load(['roles.permissions']);

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user
        ], 200);
    }

    /**
     * حذف مستخدم
     * 
     * DELETE /api/users/{id}
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // منع حذف المستخدم الحالي
        if ($user->id === auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot delete yourself'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully'
        ], 200);
    }

    /**
     * إحصائيات المستخدمين
     * 
     * GET /api/users/stats
     */
    public function stats()
    {
        $totalUsers = User::count();
        $activeUsers = User::whereNotNull('email_verified_at')->count();
        $recentUsers = User::where('created_at', '>=', now()->subDays(30))->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'recent_users' => $recentUsers,
            ]
        ], 200);
    }
}