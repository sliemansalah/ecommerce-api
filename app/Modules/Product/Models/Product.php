<?php

namespace App\Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'slug',
        'sku',
        'barcode',
        'price',
        'cost_price',
        'discount_price',
        'discount_type',
        'discount_value',
        'stock_quantity',
        'min_stock_quantity',
        'track_stock',
        'category_id',
        'is_active',
        'is_featured',
        'sort_order',
        'weight',
        'length',
        'width',
        'height',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_stock_quantity' => 'integer',
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
    ];

    protected $appends = ['name', 'final_price', 'in_stock', 'stock_status'];

    /**
     * إرجاع الاسم حسب اللغة
     */
    public function getNameAttribute()
    {
        return app()->getLocale() == 'ar' ? $this->name_ar : $this->name_en;
    }

    /**
     * حساب السعر النهائي بعد الخصم
     */
    public function getFinalPriceAttribute()
    {
        if ($this->discount_price) {
            return $this->discount_price;
        }

        if ($this->discount_value && $this->discount_type) {
            if ($this->discount_type === 'fixed') {
                return max(0, $this->price - $this->discount_value);
            } elseif ($this->discount_type === 'percentage') {
                return $this->price - ($this->price * ($this->discount_value / 100));
            }
        }

        return $this->price;
    }

    /**
     * التحقق من توفر المخزون
     */
    public function getInStockAttribute()
    {
        if (!$this->track_stock) {
            return true;
        }
        return $this->stock_quantity > 0;
    }

    /**
     * حالة المخزون
     */
    public function getStockStatusAttribute()
    {
        if (!$this->track_stock) {
            return 'unlimited';
        }

        if ($this->stock_quantity <= 0) {
            return 'out_of_stock';
        }

        if ($this->stock_quantity <= $this->min_stock_quantity) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * الفئة
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * الصور
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * الصورة الرئيسية
     */
    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * تقليل المخزون
     */
    public function decreaseStock(int $quantity): bool
    {
        if (!$this->track_stock) {
            return true;
        }

        if ($this->stock_quantity < $quantity) {
            return false;
        }

        $this->decrement('stock_quantity', $quantity);
        return true;
    }

    /**
     * زيادة المخزون
     */
    public function increaseStock(int $quantity): void
    {
        if ($this->track_stock) {
            $this->increment('stock_quantity', $quantity);
        }
    }

    /**
     * إنشاء slug تلقائياً
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name_en);
            }
            
            // حساب السعر بعد الخصم
            if ($product->discount_value && $product->discount_type) {
                if ($product->discount_type === 'fixed') {
                    $product->discount_price = max(0, $product->price - $product->discount_value);
                } elseif ($product->discount_type === 'percentage') {
                    $product->discount_price = $product->price - ($product->price * ($product->discount_value / 100));
                }
            }
        });

        static::updating(function ($product) {
            // إعادة حساب السعر عند التحديث
            if ($product->isDirty(['price', 'discount_value', 'discount_type'])) {
                if ($product->discount_value && $product->discount_type) {
                    if ($product->discount_type === 'fixed') {
                        $product->discount_price = max(0, $product->price - $product->discount_value);
                    } elseif ($product->discount_type === 'percentage') {
                        $product->discount_price = $product->price - ($product->price * ($product->discount_value / 100));
                    }
                } else {
                    $product->discount_price = null;
                }
            }
        });
    }
}