<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use ApiResponse;

    /**
     * عرض كل المستخدمين
     */
    public function index()
    {
        $users = User::with('roles', 'permissions')->paginate(15);
        return $this->successResponse($users);
    }

    /**
     * عرض مستخدم محدد
     */
    public function show($id)
    {
        $user = User::with('roles', 'permissions')->findOrFail($id);
        return $this->successResponse($user);
    }

    /**
     * إنشاء مستخدم جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // إسناد الأدوار
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        // إسناد الصلاحيات المباشرة
        if ($request->has('permissions')) {
            $user->syncPermissions($request->permissions);
        }

        return $this->successResponse(
            $user->load('roles', 'permissions'),
            'User created successfully',
            201
        );
    }

    /**
     * تحديث مستخدم
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return $this->successResponse(
            $user->load('roles', 'permissions'),
            'User updated successfully'
        );
    }

    /**
     * حذف مستخدم
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // منع حذف نفسك
        if (auth()->id() == $user->id) {
            return $this->errorResponse('You cannot delete yourself', 400);
        }

        $user->delete();

        return $this->successResponse(null, 'User deleted successfully');
    }

    /**
     * إسناد أدوار لمستخدم
     */
    public function assignRoles(Request $request, $id)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::findOrFail($id);
        $user->syncRoles($request->roles);

        return $this->successResponse(
            $user->load('roles'),
            'Roles assigned successfully'
        );
    }

    /**
     * إزالة دور من مستخدم
     */
    public function removeRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::findOrFail($id);
        $user->removeRole($request->role);

        return $this->successResponse(
            $user->load('roles'),
            'Role removed successfully'
        );
    }

    /**
     * إسناد صلاحيات مباشرة لمستخدم
     */
    public function assignPermissions(Request $request, $id)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $user = User::findOrFail($id);
        $user->syncPermissions($request->permissions);

        return $this->successResponse(
            $user->load('permissions'),
            'Permissions assigned successfully'
        );
    }

    /**
     * إزالة صلاحية من مستخدم
     */
    public function removePermission(Request $request, $id)
    {
        $request->validate([
            'permission' => 'required|exists:permissions,name',
        ]);

        $user = User::findOrFail($id);
        $user->revokePermissionTo($request->permission);

        return $this->successResponse(
            $user->load('permissions'),
            'Permission removed successfully'
        );
    }
}