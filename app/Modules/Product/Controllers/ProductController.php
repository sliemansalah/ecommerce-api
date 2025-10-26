<?php

namespace App\Modules\Product\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\ProductImage;
use App\Modules\Product\Requests\ProductStoreRequest;
use App\Modules\Product\Requests\ProductUpdateRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    use ApiResponse;

    /**
     * عرض جميع المنتجات
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images', 'primaryImage']);

        // فلترة حسب الفئة
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // فلترة حسب الحالة
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // فلترة المنتجات المميزة
        if ($request->has('is_featured') && $request->is_featured) {
            $query->where('is_featured', true);
        }

        // فلترة حسب حالة المخزون
        if ($request->has('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->where('stock_quantity', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', '<=', 0);
                    break;
                case 'low_stock':
                    $query->whereColumn('stock_quantity', '<=', 'min_stock_quantity');
                    break;
            }
        }

        // البحث
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // فلترة حسب نطاق السعر
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // الترتيب
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate($request->get('per_page', 15));

        return $this->successResponse($products);
    }

    /**
     * عرض منتج محدد
     */
    public function show($id)
    {
        $product = Product::with(['category', 'images'])->findOrFail($id);
        return $this->successResponse($product);
    }

    /**
     * إنشاء منتج جديد
     */
    public function store(ProductStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            
            // فصل بيانات الصور
            $images = $data['images'] ?? [];
            unset($data['images']);
            
            // إنشاء المنتج
            $product = Product::create($data);
            
            // إضافة الصور
            if (!empty($images)) {
                foreach ($images as $imageData) {
                    $product->images()->create($imageData);
                }
            }
            
            DB::commit();
            
            return $this->successResponse(
                $product->load(['category', 'images']),
                'Product created successfully',
                201
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create product: ' . $e->getMessage(), 500);
        }
    }

    /**
     * تحديث منتج
     */
    public function update(ProductUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $product = Product::findOrFail($id);
            $product->update($request->validated());
            
            DB::commit();
            
            return $this->successResponse(
                $product->load(['category', 'images']),
                'Product updated successfully'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update product: ' . $e->getMessage(), 500);
        }
    }

    /**
     * حذف منتج
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return $this->successResponse(null, 'Product deleted successfully');
    }

    /**
     * تغيير حالة التفعيل
     */
    public function toggleActive($id)
    {
        $product = Product::findOrFail($id);
        $product->is_active = !$product->is_active;
        $product->save();

        return $this->successResponse(
            $product,
            'Product status updated successfully'
        );
    }

    /**
     * تغيير حالة المنتج المميز
     */
    public function toggleFeatured($id)
    {
        $product = Product::findOrFail($id);
        $product->is_featured = !$product->is_featured;
        $product->save();

        return $this->successResponse(
            $product,
            'Product featured status updated successfully'
        );
    }

    /**
     * تحديث المخزون
     */
    public function updateStock(Request $request, $id)
    {
        $request->validate([
            'stock_quantity' => 'required|integer|min:0',
        ]);

        $product = Product::findOrFail($id);
        $product->stock_quantity = $request->stock_quantity;
        $product->save();

        return $this->successResponse(
            $product,
            'Stock updated successfully'
        );
    }

    /**
     * إضافة صور للمنتج
     */
    public function addImages(Request $request, $id)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*.image_path' => 'required|string',
            'images.*.is_primary' => 'boolean',
            'images.*.sort_order' => 'integer|min:0',
        ]);

        $product = Product::findOrFail($id);

        DB::beginTransaction();
        try {
            foreach ($request->images as $imageData) {
                // إذا كانت الصورة primary، اجعل البقية false
                if (isset($imageData['is_primary']) && $imageData['is_primary']) {
                    $product->images()->update(['is_primary' => false]);
                }
                
                $product->images()->create($imageData);
            }

            DB::commit();

            return $this->successResponse(
                $product->load('images'),
                'Images added successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to add images: ' . $e->getMessage(), 500);
        }
    }

    /**
     * حذف صورة
     */
    public function deleteImage($productId, $imageId)
    {
        $product = Product::findOrFail($productId);
        $image = $product->images()->findOrFail($imageId);
        
        $image->delete();

        return $this->successResponse(null, 'Image deleted successfully');
    }

    /**
     * تعيين صورة رئيسية
     */
    public function setPrimaryImage($productId, $imageId)
    {
        $product = Product::findOrFail($productId);
        
        DB::beginTransaction();
        try {
            // إلغاء primary من جميع الصور
            $product->images()->update(['is_primary' => false]);
            
            // تعيين الصورة المحددة كـ primary
            $image = $product->images()->findOrFail($imageId);
            $image->is_primary = true;
            $image->save();
            
            DB::commit();

            return $this->successResponse(
                $product->load('images'),
                'Primary image set successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to set primary image: ' . $e->getMessage(), 500);
        }
    }
}