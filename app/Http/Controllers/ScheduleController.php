<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProjectSetting;
use App\Services\RiskPredictionService;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\View\View;

class ScheduleController extends Controller
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

        // عزل طلبات المتجر
        $orders = Order::where('store_id', $storeId)->with('tasks')->get();
        $plan = $this->scheduler->build($orders, $settings, $today);
        $days = $this->riskPredictor->getUpcomingDaysOverview($settings, $plan, $today, 21);

        $orderMap = [];
        foreach ($orders as $o) {
            $orderMap[$o->id] = $o;
        }

        return view('schedule.index', [
            'settings' => $settings,
            'today' => $today,
            'plan' => $plan,
            'days' => $days,
            'orderMap' => $orderMap,
        ]);
    }
}
