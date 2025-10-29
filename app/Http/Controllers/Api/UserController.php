<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * عرض قائمة المستخدمين
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'permissions']);

        // البحث
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // الفلترة حسب الحالة
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // الفلترة حسب الدور
        if ($request->has('role')) {
            $query->role($request->role);
        }

        // الترتيب
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        return UserResource::collection($users);
    }

    /**
     * إنشاء مستخدم جديد
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        // إضافة الأدوار
        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        // إضافة الصلاحيات
        if (isset($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        return new UserResource($user->load(['roles', 'permissions']));
    }

    /**
     * عرض مستخدم محدد
     */
    public function show(User $user)
    {
        return new UserResource($user->load(['roles', 'permissions']));
    }

    /**
     * تحديث مستخدم
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        // تحديث كلمة المرور إذا تم إدخالها
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        // تحديث الأدوار
        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        // تحديث الصلاحيات
        if (isset($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        return new UserResource($user->load(['roles', 'permissions']));
    }

    /**
     * حذف مستخدم
     */
    public function destroy(User $user)
    {
        // منع حذف المستخدم الحالي
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'لا يمكنك حذف حسابك الخاص',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'تم حذف المستخدم بنجاح',
        ], 200);
    }

    /**
     * تفعيل/تعطيل مستخدم
     */
    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        return new UserResource($user);
    }

    /**
     * الحصول على إحصائيات المستخدمين
     */
    public function stats()
    {
        return response()->json([
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'users_by_role' => User::select('roles.name', \DB::raw('count(*) as count'))
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->groupBy('roles.name')
                ->get(),
        ]);
    }
}