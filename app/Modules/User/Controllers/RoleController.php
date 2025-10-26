<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    use ApiResponse;

    /**
     * عرض كل الأدوار
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return $this->successResponse($roles);
    }

    /**
     * عرض دور محدد
     */
    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return $this->successResponse($role);
    }

    /**
     * إنشاء دور جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return $this->successResponse(
            $role->load('permissions'),
            'Role created successfully',
            201
        );
    }

    /**
     * تحديث دور
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|unique:roles,name,' . $role->id,
        ]);

        if ($request->has('name')) {
            $role->name = $request->name;
            $role->save();
        }

        return $this->successResponse(
            $role->load('permissions'),
            'Role updated successfully'
        );
    }

    /**
     * حذف دور
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return $this->successResponse(null, 'Role deleted successfully');
    }

    /**
     * إسناد صلاحيات لدور
     */
    public function assignPermissions(Request $request, $id)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::findOrFail($id);
        $role->syncPermissions($request->permissions);

        return $this->successResponse(
            $role->load('permissions'),
            'Permissions assigned to role successfully'
        );
    }

    /**
     * عرض كل الصلاحيات
     */
    public function permissions()
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });
        
        return $this->successResponse($permissions);
    }

    /**
     * إنشاء صلاحية جديدة
     */
    public function storePermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $permission = Permission::create(['name' => $request->name]);

        return $this->successResponse($permission, 'Permission created successfully', 201);
    }
}