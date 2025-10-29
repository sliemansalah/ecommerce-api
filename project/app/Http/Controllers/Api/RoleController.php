<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    // public function __constractor(){
    //     dd('dsa');
    // }
    public function index(Request $request)
    {   
        try {
            $query = Role::query();
            
            // إضافة العدادات
            $query->withCount(['permissions', 'users']);

            // البحث
            if ($request->has('search') && $request->search) {
                $query->where('name', 'like', "%{$request->search}%");
            }

            // الترتيب
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination أو Get All
            if ($request->has('per_page') && $request->per_page) {
                $perPage = (int) $request->get('per_page', 15);
                $roles = $query->paginate($perPage);
                return RoleResource::collection($roles);
            } else {
                $roles = $query->get();
                return RoleResource::collection($roles);
            }

        } catch (\Exception $e) {
            \Log::error('Error in RoleController@index: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'حدث خطأ أثناء جلب الأدوار',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    public function store(StoreRoleRequest $request)
    {
        try {
            $role = Role::create(['name' => $request->name]);

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            return new RoleResource($role->load('permissions'));
        } catch (\Exception $e) {
            \Log::error('Error in RoleController@store: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'فشل في إنشاء الدور',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    public function show(Role $role)
    {
        try {
            return new RoleResource($role->load('permissions'));
        } catch (\Exception $e) {
            \Log::error('Error in RoleController@show: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'فشل في جلب الدور',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        try {
            if ($role->name === 'super-admin') {
                return response()->json([
                    'message' => 'لا يمكن تعديل دور Super Admin',
                ], 403);
            }

            if ($request->has('name')) {
                $role->update(['name' => $request->name]);
            }

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            return new RoleResource($role->load('permissions'));
        } catch (\Exception $e) {
            \Log::error('Error in RoleController@update: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'فشل في تحديث الدور',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    public function destroy(Role $role)
    {
        try {
            $protectedRoles = ['super-admin', 'admin', 'customer'];
            
            if (in_array($role->name, $protectedRoles)) {
                return response()->json([
                    'message' => 'لا يمكن حذف هذا الدور',
                ], 403);
            }

            $role->delete();

            return response()->json([
                'message' => 'تم حذف الدور بنجاح',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in RoleController@destroy: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'فشل في حذف الدور',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }
}