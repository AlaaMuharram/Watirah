<?php

namespace App\Services;

use App\Models\ExecutionLog;
use App\Models\Order;
use App\Models\ProductType;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Str;

class OrderService
{
    public function generateOrderNumber(?int $storeId = null): string
    {
        $storeId = $storeId ?: auth()->user()?->store_id;

        $last = Order::when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$last) {
            return 'WT-1001';
        }

        if (preg_match('/WT-(\d+)/', $last->order_number, $m)) {
            return 'WT-' . ((int) $m[1] + 1);
        }

        return 'WT-' . rand(1000, 9999);
    }

    public function createOrder(array $data): Order
    {
        $storeId = $data['store_id'] ?? auth()->user()?->store_id;
        $id = 'o_' . Str::random(10);
        $orderNumber = $this->generateOrderNumber($storeId);

        $quantity = max(1, (int) ($data['quantity'] ?? 1));
        $mode = $data['duration_mode'] ?? 'auto';

        $unitDuration = null;
        if ($mode === 'auto') {
            $product = ProductType::when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->where('product_name', $data['product_name'])
                ->first();
            $unitDuration = $product ? $product->estimated_unit_duration : 60;
            $estimated = $unitDuration * $quantity;
        } else {
            $unitDuration = isset($data['unit_duration']) ? (int) $data['unit_duration'] : 60;
            $estimated = (int) ($data['estimated_duration'] ?? ($unitDuration * $quantity));
        }

        $remaining = $estimated;

        $order = Order::create([
            'id' => $id,
            'store_id' => $storeId,
            'order_number' => $orderNumber,
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'product_name' => $data['product_name'],
            'description' => $data['description'] ?? null,
            'quantity' => $quantity,
            'received_date' => $data['received_date'] ?? Carbon::today()->toDateString(),
            'due_date' => $data['due_date'],
            'duration_mode' => $mode,
            'unit_duration' => $unitDuration,
            'estimated_duration' => $estimated,
            'remaining_duration' => $remaining,
            'status' => 'new',
            'risk_level' => 'low',
            'notes' => $data['notes'] ?? null,
        ]);

        ExecutionLog::create([
            'id' => 'log_' . Str::random(10),
            'order_id' => $order->id,
            'old_status' => null,
            'new_status' => 'new',
            'changed_at' => now(),
            'notes' => 'تم استلام وتسجيل الطلب',
        ]);

        $this->createDefaultTasks($order);

        return $order;
    }

    public function updateStatus(Order $order, string $newStatus, ?string $notes = null): Order
    {
        $oldStatus = $order->status;
        if ($oldStatus === $newStatus) {
            return $order;
        }

        $updates = ['status' => $newStatus];
        if ($newStatus === 'completed') {
            $updates['completed_at'] = now();
            $updates['remaining_duration'] = 0;
            $order->tasks()->where('status', '!=', 'completed')->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        $order->update($updates);

        ExecutionLog::create([
            'id' => 'log_' . Str::random(10),
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_at' => now(),
            'notes' => $notes ?: "تغيير حالة الطلب من {$order->status_label} إلى " . (match ($newStatus) {
                'new' => 'جديد',
                'waiting' => 'بالانتظار',
                'in_progress' => 'قيد التنفيذ',
                'completed' => 'مكتمل',
                'late' => 'متأخر',
                default => $newStatus
            }),
        ]);

        return $order;
    }

    public function recalculateRemaining(Order $order): void
    {
        $tasks = $order->tasks;
        if ($tasks->count() > 0) {
            $remaining = $tasks->where('status', '!=', 'completed')->sum('duration_minutes');
            $order->update(['remaining_duration' => $remaining]);
            if ($remaining === 0 && $order->status !== 'completed') {
                $this->updateStatus($order, 'completed', 'اكتملت جميع المهام المحددة');
            }
        }
    }

    protected function createDefaultTasks(Order $order): void
    {
        $totalMinutes = $order->estimated_duration;
        $t1Minutes = (int) round($totalMinutes * 0.5);
        $t2Minutes = (int) round($totalMinutes * 0.3);
        $t3Minutes = max(15, $totalMinutes - $t1Minutes - $t2Minutes);

        $defaults = [
            ['title' => 'تجهيز المواد وتصنيع ' . $order->product_name, 'minutes' => $t1Minutes, 'priority' => 'high'],
            ['title' => 'التنسيق واللمسات الجمالية', 'minutes' => $t2Minutes, 'priority' => 'medium'],
            ['title' => 'التغليف الفاخر وبطاقة الإهداء وفحص الجودة', 'minutes' => $t3Minutes, 'priority' => 'low'],
        ];

        foreach ($defaults as $i => $item) {
            Task::create([
                'id' => 't_' . Str::random(10),
                'order_id' => $order->id,
                'title' => $item['title'],
                'duration_minutes' => $item['minutes'],
                'status' => 'pending',
                'priority' => $item['priority'],
                'sort_order' => $i + 1,
            ]);
        }
    }
}
