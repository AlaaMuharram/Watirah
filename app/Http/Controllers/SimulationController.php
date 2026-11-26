<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductType;
use App\Models\ProjectSetting;
use App\Services\SimulationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SimulationController extends Controller
{
    public function __construct(
        protected SimulationService $simulator
    ) {}

    public function index(Request $request): View
    {
        $storeId = auth()->user()->store_id;
        $products = ProductType::where('store_id', $storeId)->get();
        $settings = ProjectSetting::current($storeId);
        $today = Carbon::today();

        $candidate = [
            'product_name' => $request->input('product_name', $products->first()?->product_name ?? 'طلب افتراضي'),
            'quantity' => (int) $request->input('quantity', 2),
            'minutes' => (int) $request->input('minutes', 180),
            'due_date' => $request->input('due_date', $today->copy()->addDays(3)->toDateString()),
        ];

        $result = null;
        if ($request->has('run')) {
            $orders = Order::where('store_id', $storeId)->get();
            $result = $this->simulator->simulate($candidate, $orders, $settings, $today);
        }

        return view('simulator.index', [
            'products' => $products,
            'settings' => $settings,
            'today' => $today,
            'candidate' => $candidate,
            'result' => $result,
        ]);
    }

    public function simulateAjax(Request $request): JsonResponse
    {
        $storeId = auth()->user()->store_id;
        $validated = $request->validate([
            'product_name' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1',
            'minutes' => 'required|integer|min:10',
            'due_date' => 'required|date',
        ]);

        $settings = ProjectSetting::current($storeId);
        $today = Carbon::today();
        $orders = Order::where('store_id', $storeId)->get();
        $result = $this->simulator->simulate($validated, $orders, $settings, $today);

        return response()->json($result);
    }
}
