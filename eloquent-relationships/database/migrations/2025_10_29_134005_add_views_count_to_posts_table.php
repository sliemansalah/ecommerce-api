<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // في الـ migration
public function up(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->integer('views_count')->default(0)->after('content');
    });
}

public function down(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->dropColumn('views_count');
    });
}
};
