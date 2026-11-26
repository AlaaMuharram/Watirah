<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Order;
use App\Models\ProjectSetting;
use App\Services\RiskPredictionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RiskController extends Controller
{
    public function __construct(
        protected RiskPredictionService $riskPredictor
    ) {}

    public function index(): View
    {
        $storeId = auth()->user()->store_id;
        $settings = ProjectSetting::current($storeId);
        $today = Carbon::today();

        // عزل طلبات المتجر
        $orders = Order::where('store_id', $storeId)->with('tasks')->get();
        $risks = $this->riskPredictor->assessAll($orders, $settings, $today);

        $highRisks = collect($risks)->where('level', 'high');
        $mediumRisks = collect($risks)->where('level', 'medium');
        $lowRisks = collect($risks)->where('level', 'low');

        // عزل تنبيهات المتجر
        $alerts = Alert::where('store_id', $storeId)->with('order')->orderBy('created_at', 'desc')->get();

        return view('risks.index', [
            'risks' => $risks,
            'highRisks' => $highRisks,
            'mediumRisks' => $mediumRisks,
            'lowRisks' => $lowRisks,
            'alerts' => $alerts,
            'today' => $today,
        ]);
    }

    public function markAlertRead(string $id): RedirectResponse
    {
        $storeId = auth()->user()->store_id;
        $alert = Alert::where('store_id', $storeId)->findOrFail($id);
        $alert->update(['is_read' => true]);

        return back()->with('success', 'تم تمييز التنبيه كمقروء.');
    }
}
