<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Services\WorkloadService;
use App\Models\ProjectSetting;

class AppServiceProvider extends ServiceProvider {
    public function register(): void { $this->app->singleton(WorkloadService::class); }
    public function boot(): void {
        Schema::defaultStringLength(191);
        view()->composer('*', function($v){
            $u = auth()->user();
            $locale = session('locale', $u?->projectSetting?->locale ?? 'ar');
            app()->setLocale($locale);
            $v->with('currentLocale',$locale);
        });
    }
}