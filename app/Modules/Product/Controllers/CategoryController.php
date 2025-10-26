<?php

namespace App\Modules\Product\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\Models\Category;
use App\Modules\Product\Requests\CategoryStoreRequest;
use App\Modules\Product\Requests\CategoryUpdateRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * عرض جميع الفئات
     */
    public function index(Request $request)
    {
        $query = Category::with(['parent', 'children']);

        // فلترة حسب الحالة
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // فلترة الفئات الرئيسية فقط
        if ($request->has('parent_only') && $request->parent_only) {
            $query->whereNull('parent_id');
        }

        // البحث
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('sort_order')->paginate(15);

        return $this->successResponse($categories);
    }

    /**
     * عرض فئة محددة
     */
    public function show($id)
    {
        $category = Category::with(['parent', 'children'])->findOrFail($id);
        return $this->successResponse($category);
    }

    /**
     * إنشاء فئة جديدة
     */
    public function store(CategoryStoreRequest $request)
    {
        $category = Category::create($request->validated());
        
        return $this->successResponse(
            $category->load(['parent', 'children']),
            'Category created successfully',
            201
        );
    }

    /**
     * تحديث فئة
     */
    public function update(CategoryUpdateRequest $request, $id)
    {
        $category = Category::findOrFail($id);
        $category->update($request->validated());

        return $this->successResponse(
            $category->load(['parent', 'children']),
            'Category updated successfully'
        );
    }

    /**
     * حذف فئة (Soft Delete)
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // التحقق من وجود فئات فرعية
        if ($category->hasChildren()) {
            return $this->errorResponse(
                'Cannot delete category with subcategories',
                400
            );
        }

        $category->delete();

        return $this->successResponse(null, 'Category deleted successfully');
    }

    /**
     * استعادة فئة محذوفة
     */
    public function restore($id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->restore();

        return $this->successResponse(
            $category,
            'Category restored successfully'
        );
    }

    /**
     * حذف نهائي
     */
    public function forceDestroy($id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->forceDelete();

        return $this->successResponse(null, 'Category permanently deleted');
    }

    /**
     * عرض الفئات المحذوفة
     */
    public function trashed()
    {
        $categories = Category::onlyTrashed()
            ->with(['parent'])
            ->paginate(15);

        return $this->successResponse($categories);
    }

    /**
     * تغيير حالة التفعيل
     */
    public function toggleActive($id)
    {
        $category = Category::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();

        return $this->successResponse(
            $category,
            'Category status updated successfully'
        );
    }
}