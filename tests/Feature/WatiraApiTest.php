<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WatiraApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_serves_the_existing_interface(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('وَتيرة | WATIRA', false)
            ->assertSee('window.WATIRA_API = location.origin', false)
            ->assertSee('js/ui/app.js', false);
    }

    public function test_empty_database_uses_the_previous_payload_shape(): void
    {
        $this->getJson('/api/all')
            ->assertOk()
            ->assertExactJson([
                'settings' => null,
                'product_types' => null,
                'orders' => null,
                'execution_logs' => [],
                'alerts' => [],
            ]);
    }

    public function test_settings_orders_and_deletes_roundtrip(): void
    {
        $this->putJson('/api/tables/settings', [
            'project_name' => 'مشروع التجربة',
            'working_days' => [0, 1, 2, 3, 4],
            'start_time' => '08:00',
            'end_time' => '16:00',
            'daily_productive_hours' => 7.5,
            'break_duration' => 30,
            'buffer_days' => 1,
        ])->assertOk()->assertJson(['ok' => true]);

        $order = [
            'id' => 'o1',
            'order_number' => 'WT-1001',
            'customer_name' => 'عميل',
            'product_name' => 'باب',
            'description' => '',
            'quantity' => 2,
            'received_date' => '2026-11-01',
            'due_date' => '2026-11-20',
            'duration_mode' => 'auto',
            'unit_duration' => null,
            'estimated_duration' => 120,
            'remaining_duration' => 120,
            'status' => 'new',
            'risk_level' => 'low',
            'notes' => '',
            'completed_at' => null,
            'created_at' => '2026-11-26T09:39:00.123Z',
            'updated_at' => '2026-11-26T09:39:00.123Z',
        ];

        $this->putJson('/api/tables/orders', [$order])->assertOk();
        $this->putJson('/api/tables/alerts', [[
            'id' => 'a1',
            'order_id' => 'o1',
            'alert_type' => 'delay',
            'alert_key' => 'delay:o1',
            'message' => 'تأخير',
            'action' => '',
            'risk_level' => 'high',
            'created_at' => '2026-11-26T09:39:00.123Z',
            'is_read' => false,
        ]])->assertOk();

        $payload = $this->getJson('/api/all')->assertOk()->json();

        $this->assertSame('مشروع التجربة', $payload['settings']['project_name']);
        $this->assertSame([0, 1, 2, 3, 4], $payload['settings']['working_days']);
        $this->assertEquals(7.5, $payload['settings']['daily_productive_hours']);
        $this->assertSame([], $payload['product_types']);
        $this->assertCount(1, $payload['orders']);
        $this->assertSame('WT-1001', $payload['orders'][0]['order_number']);
        $this->assertSame(2, $payload['orders'][0]['quantity']);
        $this->assertNull($payload['orders'][0]['unit_duration']);
        $this->assertSame('2026-11-01', $payload['orders'][0]['received_date']);
        $this->assertSame('2026-11-26T09:39:00.123Z', $payload['orders'][0]['created_at']);
        $this->assertFalse($payload['alerts'][0]['is_read']);

        $this->putJson('/api/tables/orders', [])->assertOk();
        $afterDelete = $this->getJson('/api/all')->json();
        $this->assertSame([], $afterDelete['orders']);
        $this->assertSame([], $afterDelete['alerts']);

        $this->deleteJson('/api/all')->assertOk()->assertJson(['ok' => true]);
        $this->getJson('/api/all')->assertJson(['settings' => null, 'orders' => null]);
    }

    public function test_unknown_table_is_rejected(): void
    {
        $this->putJson('/api/tables/users', [])
            ->assertStatus(400)
            ->assertJson(['error' => 'جدول غير معروف أو بيانات غير صالحة']);
    }
}
