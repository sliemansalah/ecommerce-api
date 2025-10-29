<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * عرض قائمة الصلاحيات
     */
    public function index(Request $request)
    {
        $query = Permission::query();

        // البحث
        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // الترتيب
        $query->orderBy('name', 'asc');

        $permissions = $query->get();

        // تجميع الصلاحيات حسب المجموعة
        if ($request->get('grouped', false)) {
            $grouped = $permissions->groupBy(function ($permission) {
                $parts = explode('-', $permission->name);
                return $parts[1] ?? 'other';
            });

            return response()->json($grouped);
        }

        return PermissionResource::collection($permissions);
    }

    /**
     * عرض صلاحية محددة
     */
    public function show(Permission $permission)
    {
        return new PermissionResource($permission);
    }
}