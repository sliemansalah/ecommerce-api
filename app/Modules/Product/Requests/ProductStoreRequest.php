<?php

namespace App\Modules\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'slug' => 'nullable|string|unique:products,slug',
            'sku' => 'required|string|unique:products,sku',
            'barcode' => 'nullable|string|unique:products,barcode',
            
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_quantity' => 'nullable|integer|min:0',
            'track_stock' => 'boolean',
            
            'category_id' => 'required|exists:categories,id',
            
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
            
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            
            // للصور
            'images' => 'nullable|array',
            'images.*.image_path' => 'required|string',
            'images.*.is_primary' => 'boolean',
            'images.*.sort_order' => 'integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name_ar.required' => 'اسم المنتج بالعربي مطلوب',
            'name_en.required' => 'اسم المنتج بالإنجليزي مطلوب',
            'sku.required' => 'رمز المنتج (SKU) مطلوب',
            'sku.unique' => 'رمز المنتج مستخدم مسبقاً',
            'price.required' => 'السعر مطلوب',
            'category_id.required' => 'الفئة مطلوبة',
            'category_id.exists' => 'الفئة غير موجودة',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}