<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->getDisplayName(),
            'group' => $this->getGroup(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    // Helper لعرض الاسم بالعربية
    private function getDisplayName(): string
    {
        $names = [
            // Users
            'view-users' => 'عرض المستخدمين',
            'create-users' => 'إنشاء مستخدمين',
            'edit-users' => 'تعديل المستخدمين',
            'delete-users' => 'حذف المستخدمين',
            
            // Roles
            'view-roles' => 'عرض الأدوار',
            'create-roles' => 'إنشاء أدوار',
            'edit-roles' => 'تعديل الأدوار',
            'delete-roles' => 'حذف الأدوار',
            
            // Products
            'view-products' => 'عرض المنتجات',
            'create-products' => 'إنشاء منتجات',
            'edit-products' => 'تعديل المنتجات',
            'delete-products' => 'حذف المنتجات',
            
            // Orders
            'view-orders' => 'عرض الطلبات',
            'create-orders' => 'إنشاء طلبات',
            'edit-orders' => 'تعديل الطلبات',
            'delete-orders' => 'حذف الطلبات',
            
            // Reports
            'view-reports' => 'عرض التقارير',
            'export-reports' => 'تصدير التقارير',
            
            // Settings
            'manage-settings' => 'إدارة الإعدادات',
        ];

        return $names[$this->name] ?? $this->name;
    }

    // Helper لتجميع الصلاحيات
    private function getGroup(): string
    {
        $groups = [
            'users' => 'المستخدمين',
            'roles' => 'الأدوار',
            'products' => 'المنتجات',
            'orders' => 'الطلبات',
            'reports' => 'التقارير',
            'settings' => 'الإعدادات',
        ];

        $prefix = explode('-', $this->name)[1] ?? '';
        return $groups[$prefix] ?? 'أخرى';
    }
}