<?php

namespace App\Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'slug',
        'image',
        'parent_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['name'];

    /**
     * إرجاع الاسم حسب اللغة الحالية
     */
    public function getNameAttribute()
    {
        return app()->getLocale() == 'ar' ? $this->name_ar : $this->name_en;
    }

    /**
     * الفئة الأب (Parent Category)
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * الفئات الفرعية (Child Categories)
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * المنتجات التابعة لهذه الفئة
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * التحقق إذا كانت الفئة رئيسية
     */
    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * التحقق إذا كانت لديها فئات فرعية
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * إنشاء slug تلقائياً
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name_en);
            }
        });
    }
}