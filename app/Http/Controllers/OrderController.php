<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductType;
use App\Models\ProjectSetting;
use App\Services\OrderService;
use App\Services\RiskPredictionService;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected ScheduleService $scheduler,
        protected RiskPredictionService $riskPredictor
    ) {}

    public function index(Request $request): View
    {
        $storeId = auth()->user()->store_id;
        $status = $request->query('status');
        $risk = $request->query('risk');
        $search = $request->query('search');

        // عزل استعلام الطلبات للمتجر الحالي
        $query = Order::where('store_id', $storeId)->with('tasks')->orderBy('due_date', 'asc');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($risk && $risk !== 'all') {
            $query->where('risk_level', $risk);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(15)->withQueryString();
        $settings = ProjectSetting::current($storeId);
        $risks = $this->riskPredictor->assessAll(Order::where('store_id', $storeId)->get(), $settings, Carbon::today());

        $counts = [
            'all' => Order::where('store_id', $storeId)->count(),
            'new' => Order::where('store_id', $storeId)->where('status', 'new')->count(),
            'in_progress' => Order::where('store_id', $storeId)->where('status', 'in_progress')->count(),
            'waiting' => Order::where('store_id', $storeId)->where('status', 'waiting')->count(),
            'late' => Order::where('store_id', $storeId)->where('status', 'late')->count(),
            'completed' => Order::where('store_id', $storeId)->where('status', 'completed')->count(),
        ];

        return view('orders.index', [
            'orders' => $orders,
            'risks' => $risks,
            'counts' => $counts,
            'currentStatus' => $status ?? 'all',
            'currentRisk' => $risk ?? 'all',
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        $storeId = auth()->user()->store_id;
        $products = ProductType::where('store_id', $storeId)->get();
        $nextOrderNumber = $this->orderService->generateOrderNumber();
        $defaultDue = Carbon::today()->addDays(3)->toDateString();

        return view('orders.create', [
            'products' => $products,
            'nextOrderNumber' => $nextOrderNumber,
            'defaultDue' => $defaultDue,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $storeId = auth()->user()->store_id;

        $validated = $request->validate([
            'customer_name' => 'required|string|max:200',
            'customer_phone' => 'nullable|string|max:50',
            'product_name' => 'required|string|max:200',
            'quantity' => 'required|integer|min:1',
            'received_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:received_date',
            'duration_mode' => 'required|in:auto,manual',
            'unit_duration' => 'nullable|integer|min:1',
            'estimated_duration' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['store_id'] = $storeId;
        $order = $this->orderService->createOrder($validated);

        return redirect()->route('orders.show', $order->id)
            ->with('success', "تم إضافة الطلب {$order->order_number} بنجاح!");
    }

    public function show(string $id): View
    {
        $storeId = auth()->user()->store_id;
        // منع أي متجر من الوصول إلى طلبات متجر آخر
        $order = Order::where('store_id', $storeId)->with(['tasks', 'executionLogs', 'alerts'])->findOrFail($id);
        $settings = ProjectSetting::current($storeId);
        $today = Carbon::today();

        $allOrders = Order::where('store_id', $storeId)->get();
        $risks = $this->riskPredictor->assessAll($allOrders, $settings, $today);
        $orderRisk = $risks[$order->id] ?? null;

        return view('orders.show', [
            'order' => $order,
            'orderRisk' => $orderRisk,
            'settings' => $settings,
        ]);
    }

    public function edit(string $id): View
    {
        $storeId = auth()->user()->store_id;
        $order = Order::where('store_id', $storeId)->findOrFail($id);
        $products = ProductType::where('store_id', $storeId)->get();

        return view('orders.edit', [
            'order' => $order,
            'products' => $products,
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $storeId = auth()->user()->store_id;
        $order = Order::where('store_id', $storeId)->findOrFail($id);

        $validated = $request->validate([
            'customer_name' => 'required|string|max:200',
            'customer_phone' => 'nullable|string|max:50',
            'product_name' => 'required|string|max:200',
            'quantity' => 'required|integer|min:1',
            'received_date' => 'required|date',
            'due_date' => 'required|date',
            'status' => 'required|in:new,waiting,in_progress,completed,late',
            'description' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validated['status'] !== $order->status) {
            $this->orderService->updateStatus($order, $validated['status'], 'تحديث الحالة من صفحة التعديل');
        }

        $order->update($validated);

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'تم تحديث بيانات الطلب بنجاح.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $storeId = auth()->user()->store_id;
        $order = Order::where('store_id', $storeId)->findOrFail($id);
        $num = $order->order_number;
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', "تم حذف الطلب {$num} بنجاح.");
    }

    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        $storeId = auth()->user()->store_id;
        $order = Order::where('store_id', $storeId)->findOrFail($id);
        $status = $request->validate([
            'status' => 'required|in:new,waiting,in_progress,completed,late',
        ])['status'];

        $this->orderService->updateStatus($order, $status, $request->input('notes'));

        return back()->with('success', "تم تحديث حالة الطلب إلى {$order->status_label}.");
    }
}
