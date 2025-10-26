<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            
            // السعر والخصم
            $table->decimal('price', 10, 2);
            $table->decimal('cost_price', 10, 2)->nullable()->comment('سعر التكلفة');
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();
            
            // المخزون
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock_quantity')->default(0)->comment('الحد الأدنى للتنبيه');
            $table->boolean('track_stock')->default(true);
            
            // العلاقات
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            
            // الحالة
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false)->comment('منتج مميز');
            $table->integer('sort_order')->default(0);
            
            // المواصفات
            $table->decimal('weight', 8, 2)->nullable()->comment('الوزن بالكيلو');
            $table->decimal('length', 8, 2)->nullable()->comment('الطول بالسم');
            $table->decimal('width', 8, 2)->nullable()->comment('العرض بالسم');
            $table->decimal('height', 8, 2)->nullable()->comment('الارتفاع بالسم');
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index('category_id');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};