<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WatiraApiController;
use Illuminate\Support\Facades\Route;

// ==========================================
// مسارات المصادقة والتسجيل (عامة للزوار)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ==========================================
// مسارات لوحة التحكم وإدارة المتجر (محمية بتسجيل الدخول)
// ==========================================
Route::middleware('auth')->group(function () {
    // لوحة التحكم الرئيسية
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // إدارة الطلبات
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::get('/{id}', [OrderController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [OrderController::class, 'edit'])->name('edit');
        Route::put('/{id}', [OrderController::class, 'update'])->name('update');
        Route::delete('/{id}', [OrderController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/status', [OrderController::class, 'updateStatus'])->name('status');

        // مهام الطلب
        Route::post('/{orderId}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    });

    // إدارة المهام العامة
    Route::post('/tasks/{id}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // محاكاة ضغط العمل
    Route::get('/simulator', [SimulationController::class, 'index'])->name('simulator.index');
    Route::post('/simulator/run', [SimulationController::class, 'simulateAjax'])->name('simulator.run');

    // التنبؤ بالتأخير وإدارة المخاطر
    Route::get('/risks', [RiskController::class, 'index'])->name('risks.index');
    Route::post('/alerts/{id}/read', [RiskController::class, 'markAlertRead'])->name('alerts.read');

    // جدول العمل والتقويم
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');

    // إعدادات الورشة والمنتجات
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->name('update');
        Route::post('/products', [SettingsController::class, 'storeProduct'])->name('products.store');
        Route::delete('/products/{id}', [SettingsController::class, 'deleteProduct'])->name('products.destroy');
    });

    // الملف الشخصي وإدارة بيانات المتجر
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // الذكاء الاصطناعي (مساعد وَتيرة واستخراج الطلبات)
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::post('/extract', [AiController::class, 'extract'])->name('extract');
        Route::post('/chat', [AiController::class, 'chat'])->name('chat');
    });
});

// مسارات التوافق مع API
Route::get('/api/all', [WatiraApiController::class, 'all']);
Route::put('/api/tables/{name}', [WatiraApiController::class, 'save']);
Route::delete('/api/all', [WatiraApiController::class, 'clear']);
