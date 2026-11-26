<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Order;
use App\Models\ProjectSetting;
use App\Services\RiskPredictionService;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ScheduleService $scheduler,
        protected RiskPredictionService $riskPredictor
    ) {}

    public function index(): View
    {
        $storeId = auth()->user()->store_id;
        $settings = ProjectSetting::current($storeId);
        $today = Carbon::today();

        // عزل البيانات: جلب طلبات المتجر الحالي فقط
        $orders = Order::where('store_id', $storeId)->with('tasks')->get();

        $activeOrders = $orders->where('status', '!=', 'completed');
        $completedOrders = $orders->where('status', 'completed');
        $lateOrders = $orders->where('status', 'late');

        $plan = $this->scheduler->build($orders, $settings, $today);
        $risks = $this->riskPredictor->assessAll($orders, $settings, $today);
        $upcomingDays = $this->riskPredictor->getUpcomingDaysOverview($settings, $plan, $today, 14);

        // مؤشرات الأداء الخاصة بالمتجر
        $totalActive = $activeOrders->count();
        $totalRemainingMinutes = $activeOrders->sum('remaining_duration');
        $highRiskCount = collect($risks)->where('level', 'high')->count();
        $mediumRiskCount = collect($risks)->where('level', 'medium')->count();

        // حساب نسبة الضغط الإجمالية للأسبوعين القادمين
        $twoWeeksEnd = $today->copy()->addDays(14);
        $capacityTwoWeeks = $this->scheduler->capacityBetween($settings, $today, $twoWeeksEnd);
        $workloadRatio = $capacityTwoWeeks > 0 ? min(100, round(($totalRemainingMinutes / $capacityTwoWeeks) * 100)) : 0;

        // التنبيهات غير المقروءة للمتجر
        $alerts = Alert::where('store_id', $storeId)
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // الطلبات الأكثر حرجاً للمتجر
        $criticalOrders = $activeOrders->sortBy(function ($o) use ($risks) {
            $risk = $risks[$o->id]['level'] ?? 'low';
            $rank = match ($risk) {
                'high' => 1,
                'medium' => 2,
                default => 3,
            };
            return $rank . '_' . $o->due_date->toDateString();
        })->take(5);

        return view('dashboard', [
            'settings' => $settings,
            'today' => $today,
            'totalActive' => $totalActive,
            'totalCompleted' => $completedOrders->count(),
            'totalLate' => $lateOrders->count(),
            'totalRemainingMinutes' => $totalRemainingMinutes,
            'highRiskCount' => $highRiskCount,
            'mediumRiskCount' => $mediumRiskCount,
            'workloadRatio' => $workloadRatio,
            'alerts' => $alerts,
            'criticalOrders' => $criticalOrders,
            'risks' => $risks,
            'upcomingDays' => $upcomingDays,
            'plan' => $plan,
        ]);
    }
}
