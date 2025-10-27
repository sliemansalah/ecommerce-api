<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * RoleController
 * 
 * هذا الـ Controller مسؤول عن إدارة الأدوار (Roles)
 * الدور يحتوي على مجموعة من الصلاحيات (Permissions)
 * مثلاً: دور "Admin" له صلاحيات: products.view, products.create, etc.
 */
class RoleController extends Controller
{
    use ApiResponse;

    /**
     * عرض جميع الأدوار
     * 
     * GET /api/roles
     * 
     * يرجع قائمة بجميع الأدوار مع صلاحيات كل دور
     */
    public function index()
    {
        // جلب جميع الأدوار مع صلاحياتها
        $roles = Role::with('permissions')->get();

        // إضافة عدد المستخدمين لكل دور
        $roles->each(function ($role) {
            $role->users_count = $role->users()->count();
        });

        return $this->successResponse($roles, 'Roles retrieved successfully');
    }

    /**
     * إضافة دور جديد
     * 
     * POST /api/roles
     * 
     * البيانات المطلوبة:
     * - name: اسم الدور (إجباري، فريد)
     * - permissions: مصفوفة من IDs الصلاحيات (اختياري)
     */
    public function store(Request $request)
    {
        // التحقق من صحة البيانات
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // إنشاء الدور الجديد
        // guard_name هو 'web' لأننا نستخدم web guard
        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        // تعيين الصلاحيات للدور
        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        // إعادة تحميل الدور مع صلاحياته
        $role->load('permissions');

        return $this->successResponse($role, 'Role created successfully', 201);
    }

    /**
     * عرض دور واحد
     * 
     * GET /api/roles/{id}
     */
    public function show($id)
    {
        $role = Role::with('permissions')->find($id);

        if (!$role) {
            return $this->notFoundResponse('Role not found');
        }

        // إضافة عدد المستخدمين
        $role->users_count = $role->users()->count();

        return $this->successResponse($role);
    }

    /**
     * تعديل دور
     * 
     * PUT/PATCH /api/roles/{id}
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->notFoundResponse('Role not found');
        }

        // منع تعديل الأدوار الأساسية (super_admin)
        if ($role->name === 'super_admin') {
            return $this->errorResponse('Cannot modify super_admin role', 403);
        }

        // التحقق من صحة البيانات
        $validated = $request->validate([
            'name' => 'sometimes|string|unique:roles,name,' . $role->id . '|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // تحديث اسم الدور
        if (isset($validated['name'])) {
            $role->name = $validated['name'];
            $role->save();
        }

        // تحديث الصلاحيات
        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        // إعادة تحميل الدور
        $role->load('permissions');

        return $this->successResponse($role, 'Role updated successfully');
    }

    /**
     * حذف دور
     * 
     * DELETE /api/roles/{id}
     * 
     * ملاحظات:
     * - لا يمكن حذف الأدوار الأساسية (super_admin, admin)
     * - لا يمكن حذف دور له مستخدمين
     */
    public function destroy($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->notFoundResponse('Role not found');
        }

        // منع حذف الأدوار الأساسية
        if (in_array($role->name, ['super_admin', 'admin'])) {
            return $this->errorResponse('Cannot delete system roles', 403);
        }

        // التحقق من عدم وجود مستخدمين بهذا الدور
        if ($role->users()->count() > 0) {
            return $this->errorResponse('Cannot delete role with assigned users', 400);
        }

        // حذف الدور
        $role->delete();

        return $this->successResponse(null, 'Role deleted successfully');
    }

    /**
     * عرض جميع الصلاحيات المتاحة
     * 
     * GET /api/permissions
     * 
     * هذه الـ endpoint مفيدة لعرض جميع الصلاحيات
     * عند إنشاء أو تعديل دور
     */
    public function permissions()
    {
        // جلب جميع الصلاحيات
        $permissions = Permission::all();

        // تجميع الصلاحيات حسب النوع (products, categories, etc.)
        $grouped = $permissions->groupBy(function ($permission) {
            // استخراج الجزء الأول من اسم الصلاحية
            // مثلاً: "products.view" -> "products"
            return explode('.', $permission->name)[0];
        });

        return $this->successResponse($grouped, 'Permissions retrieved successfully');
    }
}
