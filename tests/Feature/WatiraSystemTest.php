<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ProjectSetting;
use App\Models\Task;
use Tests\TestCase;

class WatiraSystemTest extends TestCase
{
    public function test_dashboard_page_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('وَتِيرَة');
        $response->assertSee('لوحة التحكم والمؤشرات الذكية');
    }

    public function test_orders_index_page_loads(): void
    {
        $response = $this->get('/orders');
        $response->assertStatus(200);
        $response->assertSee('سجل وإدارة الطلبات');
        $response->assertSee('نورة العتيبي');
    }

    public function test_order_show_page_loads_with_tasks(): void
    {
        $order = Order::first();
        $this->assertNotNull($order);

        $response = $this->get("/orders/{$order->id}");
        $response->assertStatus(200);
        $response->assertSee($order->customer_name);
        $response->assertSee('المهام التنفيذية للطلب');
    }

    public function test_can_create_new_order_with_tasks(): void
    {
        $response = $this->post('/orders', [
            'customer_name' => 'أميرة السالم',
            'customer_phone' => '0509998877',
            'product_name' => 'باقة ورد دائمة',
            'quantity' => 2,
            'received_date' => now()->toDateString(),
            'due_date' => now()->addDays(4)->toDateString(),
            'duration_mode' => 'auto',
            'description' => 'باقة باللون الأبيض والوردي لحفل تقاعد',
        ]);

        $order = Order::where('customer_name', 'أميرة السالم')->first();
        $this->assertNotNull($order);
        $response->assertRedirect("/orders/{$order->id}");

        // التأكد من إنشاء المهام التلقائية
        $this->assertGreaterThan(0, $order->tasks()->count());
    }

    public function test_toggle_task_status_updates_remaining_duration(): void
    {
        $task = Task::where('status', 'pending')->first();
        if ($task) {
            $order = $task->order;
            $oldRemaining = $order->remaining_duration;

            $response = $this->post("/tasks/{$task->id}/toggle");
            $response->assertStatus(302);

            $task->refresh();
            $this->assertEquals('completed', $task->status);
        }
    }

    public function test_simulator_page_runs_simulation(): void
    {
        $response = $this->get('/simulator?run=1&product_name=باقة+ورد+دائمة&quantity=2&minutes=180&due_date=' . now()->addDays(3)->toDateString());
        $response->assertStatus(200);
        $response->assertSee('توصية النظام');
    }

    public function test_risks_page_loads(): void
    {
        $response = $this->get('/risks');
        $response->assertStatus(200);
        $response->assertSee('التنبؤ بالتأخير وتحليل المخاطر');
    }

    public function test_schedule_page_loads(): void
    {
        $response = $this->get('/schedule');
        $response->assertStatus(200);
        $response->assertSee('جدول العمل وتوزيع السعة اليومية');
    }

    public function test_settings_page_loads(): void
    {
        $response = $this->get('/settings');
        $response->assertStatus(200);
        $response->assertSee('أوقات العمل والطاقة الإنتاجية');
    }

    public function test_ai_extractor_api(): void
    {
        $response = $this->postJson('/ai/extract', [
            'text' => 'السلام عليكم معك مشاعل التميمي أبغى باقتين ورد دائمة لحفل تخرج بعد 3 أيام'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('fields.customer_name', 'مشاعل التميمي');
        $response->assertJsonPath('fields.quantity', 2);
    }

    public function test_ai_assistant_api(): void
    {
        $response = $this->postJson('/ai/chat', [
            'question' => 'هل أستطيع استقبال طلب جديد هذا الأسبوع؟'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['question', 'decision', 'message', 'bullets']);
    }
}
