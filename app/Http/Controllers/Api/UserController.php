<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * UserController
 * 
 * هذا الـ Controller مسؤول عن إدارة المستخدمين
 * يحتوي على 5 وظائف رئيسية (CRUD):
 * 1. index - عرض جميع المستخدمين
 * 2. store - إضافة مستخدم جديد
 * 3. show - عرض مستخدم واحد
 * 4. update - تعديل مستخدم
 * 5. destroy - حذف مستخدم
 */
class UserController extends Controller
{
    use ApiResponse; // استخدام Trait للردود الموحدة

    /**
     * عرض جميع المستخدمين
     * 
     * GET /api/users
     * 
     * يرجع قائمة بجميع المستخدمين مع:
     * - معلومات المستخدم الأساسية
     * - الأدوار المرتبطة بكل مستخدم
     * - الصلاحيات المباشرة
     */
    public function index()
    {
        // جلب جميع المستخدمين مع علاقاتهم (roles و permissions)
        // eager loading يحسّن الأداء ويمنع مشكلة N+1
        $users = User::with(['roles', 'permissions'])->get();

        // إرجاع النتيجة بصيغة JSON
        return $this->successResponse($users, 'Users retrieved successfully');
    }

    /**
     * إضافة مستخدم جديد
     * 
     * POST /api/users
     * 
     * البيانات المطلوبة:
     * - name: اسم المستخدم (إجباري، نص، 3-255 حرف)
     * - email: البريد الإلكتروني (إجباري، فريد، بصيغة email صحيحة)
     * - password: كلمة المرور (إجباري، 8 أحرف على الأقل)
     * - roles: مصفوفة من IDs الأدوار (اختياري)
     */
    public function store(Request $request)
    {
        // التحقق من صحة البيانات (Validation)
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id', // التأكد من وجود الأدوار
        ]);

        // إنشاء المستخدم الجديد
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), // تشفير كلمة المرور
        ]);

        // تعيين الأدوار إذا تم إرسالها
        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        // إعادة تحميل المستخدم مع علاقاته
        $user->load(['roles', 'permissions']);

        // إرجاع النتيجة
        return $this->successResponse($user, 'User created successfully', 201);
    }

    /**
     * عرض مستخدم واحد
     * 
     * GET /api/users/{id}
     * 
     * يرجع معلومات مستخدم محدد مع أدواره وصلاحياته
     */
    public function show($id)
    {
        // البحث عن المستخدم
        $user = User::with(['roles.permissions', 'permissions'])->find($id);

        // إذا لم يُعثر على المستخدم
        if (!$user) {
            return $this->notFoundResponse('User not found');
        }

        return $this->successResponse($user);
    }

    /**
     * تعديل مستخدم
     * 
     * PUT/PATCH /api/users/{id}
     * 
     * البيانات المطلوبة:
     * - name: اسم المستخدم (اختياري)
     * - email: البريد الإلكتروني (اختياري، فريد)
     * - password: كلمة المرور الجديدة (اختياري)
     * - roles: مصفوفة من IDs الأدوار (اختياري)
     */
    public function update(Request $request, $id)
    {
        // البحث عن المستخدم
        $user = User::find($id);

        if (!$user) {
            return $this->notFoundResponse('User not found');
        }

        // التحقق من صحة البيانات
        // Rule::unique يتجاهل المستخدم الحالي عند التحقق من الـ email
        $validated = $request->validate([
            'name' => 'sometimes|string|min:3|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        // تحديث البيانات
        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // تحديث الأدوار إذا تم إرسالها
        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        // إعادة تحميل المستخدم مع علاقاته
        $user->load(['roles', 'permissions']);

        return $this->successResponse($user, 'User updated successfully');
    }

    /**
     * حذف مستخدم
     * 
     * DELETE /api/users/{id}
     * 
     * ملاحظة: لا يمكن حذف المستخدم الحالي (نفسه)
     */
    public function destroy(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->notFoundResponse('User not found');
        }

        // منع المستخدم من حذف نفسه
        if ($user->id === $request->user()->id) {
            return $this->errorResponse('You cannot delete yourself', 400);
        }

        // حذف المستخدم
        $user->delete();

        return $this->successResponse(null, 'User deleted successfully');
    }
}