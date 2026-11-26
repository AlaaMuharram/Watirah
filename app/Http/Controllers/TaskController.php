<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Task;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function store(Request $request, string $orderId): RedirectResponse
    {
        $storeId = auth()->user()->store_id;
        // التحقق من ملكية المتجر للطلب
        $order = Order::where('store_id', $storeId)->findOrFail($orderId);

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'duration_minutes' => 'required|integer|min:5|max:1440',
            'priority' => 'required|in:low,medium,high',
            'notes' => 'nullable|string|max:500',
        ]);

        $maxSort = $order->tasks()->max('sort_order') ?? 0;

        Task::create([
            'id' => 't_' . Str::random(10),
            'order_id' => $order->id,
            'title' => $validated['title'],
            'duration_minutes' => $validated['duration_minutes'],
            'status' => 'pending',
            'priority' => $validated['priority'],
            'sort_order' => $maxSort + 1,
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->orderService->recalculateRemaining($order);

        return back()->with('success', 'تم إضافة المهمة التنفيذية بنجاح.');
    }

    public function toggle(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $storeId = auth()->user()->store_id;
        $task = Task::findOrFail($id);
        $order = $task->order;

        // التحقق من ملكية المتجر
        if ($order->store_id !== $storeId) {
            abort(403, 'غير مصرح بالوصول لهذا الطلب.');
        }

        if ($task->status === 'completed') {
            $task->update([
                'status' => 'pending',
                'completed_at' => null,
            ]);
        } else {
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        $this->orderService->recalculateRemaining($order);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'status' => $task->status,
                'remaining' => $order->fresh()->remaining_duration,
            ]);
        }

        return back()->with('success', "تم تحديث حالة المهمة '{$task->title}'.");
    }

    public function destroy(string $id): RedirectResponse
    {
        $storeId = auth()->user()->store_id;
        $task = Task::findOrFail($id);
        $order = $task->order;

        if ($order->store_id !== $storeId) {
            abort(403, 'غير مصرح بالوصول لهذا الطلب.');
        }

        $task->delete();
        $this->orderService->recalculateRemaining($order);

        return back()->with('success', 'تم حذف المهمة.');
    }
}
