<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. إنشاء جدول المتاجر
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('phone', 50)->nullable()->unique();
            $table->string('email', 191)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. تعديل جدول المستخدمين
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->cascadeOnDelete();
            $table->string('phone', 50)->nullable()->unique()->after('email');
        });

        // 3. تعديل جدول إعدادات المشروع ليرتبط بالمتجر
        Schema::table('project_settings', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->cascadeOnDelete();
            $table->index('store_id', 'idx_settings_store');
        });

        // 4. تعديل جدول أنواع المنتجات
        Schema::table('product_types', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->cascadeOnDelete();
            $table->index('store_id', 'idx_product_store');
        });

        // 5. تعديل جدول الطلبات
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->cascadeOnDelete();
            $table->index('store_id', 'idx_order_store');
        });

        // 6. تعديل جدول التنبيهات
        Schema::table('alerts', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->cascadeOnDelete();
            $table->index('store_id', 'idx_alert_store');
        });
    }

    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });

        Schema::table('product_types', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });

        Schema::table('project_settings', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn(['store_id', 'phone']);
        });

        Schema::dropIfExists('stores');
    }
};
