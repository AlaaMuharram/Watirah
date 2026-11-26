<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SimulatorController;
use App\Http\Controllers\DelayController;
use App\Http\Controllers\AiController;

Route::get('/', [DashboardController::class,'index'])->name('dashboard');
Route::get('/orders', [OrderController::class,'index'])->name('orders.index');
Route::resource('orders', OrderController::class)->except(['index']);
Route::resource('product-types', ProductTypeController::class);
Route::get('/settings', [SettingsController::class,'index'])->name('settings.index');
Route::post('/settings', [SettingsController::class,'store'])->name('settings.store');
Route::get('/schedule', [ScheduleController::class,'index'])->name('schedule.index');
Route::get('/simulator', [SimulatorController::class,'index'])->name('simulator.index');
Route::post('/simulator/run', [SimulatorController::class,'run'])->name('simulator.run');
Route::post('/simulator/accept', [SimulatorController::class,'accept'])->name('simulator.accept');
Route::get('/delays', [DelayController::class,'index'])->name('delays.index');
Route::get('/ai', [AiController::class,'index'])->name('ai.index');
Route::post('/ai/extract', [AiController::class,'extract'])->name('ai.extract');
Route::get('/lang/{locale}', function($locale){ session(['locale'=>$locale]); app()->setLocale($locale); return redirect()->back(); })->name('lang.switch');