<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ProductType;
use App\Models\ProjectSetting;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreAuthAndIsolationTest extends TestCase
{
    /**
     * سيناريو 1: تسجيل متجر جديد بنجاح وتوجيهه للوحة التحكم
     */
    public function test_1_can_register_new_store_successfully(): void
    {
        $uniqueEmail = 'craft_' . time() . '@test.com';
        $uniquePhone = '05' . rand(10000000, 99999999);

        $response = $this->post('/register', [
            'name' => 'أحمد النجار',
            'store_name' => 'روائع الخشب والمشغولات',
            'email' => $uniqueEmail,
            'phone' => $uniquePhone,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('stores', [
            'name' => 'روائع الخشب والمشغولات',
            'phone' => $uniquePhone,
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'أحمد النجار',
            'email' => $uniqueEmail,
            'phone' => $uniquePhone,
        ]);

        // التحقق من أنه تم تسجيل دخوله تلقائياً
        $this->assertAuthenticated();
    }

    /**
     * سيناريو 2: منع التسجيل ببريد إلكتروني أو هاتف مستخدم مسبقاً
     */
    public function test_2_cannot_register_with_duplicate_email_or_phone(): void
    {
        // استخدام بريد متجر ليان المسجل مسبقاً
        $response = $this->post('/register', [
            'name' => 'مستخدم مكرر',
            'store_name' => 'متجر مكرر',
            'email' => 'layan@watira.com',
            'phone' => '0599999999',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * سيناريو 3: تسجيل الدخول ببيانات صحيحة
     */
    public function test_3_can_login_with_correct_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'layan@watira.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('ليان العتيبي', auth()->user()->name);
    }

    /**
     * سيناريو 4: رفض بيانات الدخول الخاطئة وإظهار رسالة خطأ
     */
    public function test_4_rejects_invalid_login_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'layan@watira.com',
            'password' => 'wrong_password_123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * سيناريو 5: تسجيل الخروج بنجاح
     */
    public function test_5_can_logout_successfully(): void
    {
        $user = User::where('email', 'layan@watira.com')->first();
        $this->actingAs($user);

        $response = $this->post('/logout');
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * سيناريو 6: منع الزائر غير المسجل من الوصول للوحة التحكم وإعادة توجيهه للدخول
     */
    public function test_6_guest_cannot_access_dashboard_or_orders(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));

        $ordersResponse = $this->get('/orders');
        $ordersResponse->assertRedirect(route('login'));
    }

    /**
     * سيناريو 7: عزل البيانات التام بين المتاجر (Multi-Tenant Isolation)
     */
    public function test_7_store_cannot_view_or_access_other_store_data(): void
    {
        $userLayan = User::where('email', 'layan@watira.com')->first();
        $userYasmeen = User::where('email', 'yasmeen@watira.com')->first();

        $this->assertNotNull($userLayan);
        $this->assertNotNull($userYasmeen);
        $this->assertNotEquals($userLayan->store_id, $userYasmeen->store_id);

        // 1. الدخول بحساب متجر ياسمين
        $this->actingAs($userYasmeen);

        // طلبات متجر ياسمين يجب ألا تظهر فيها طلبات متجر ليان
        $response = $this->get('/orders');
        $response->assertStatus(200);
        $response->assertSee('YP-2001'); // طلب ياسمين
        $response->assertDontSee('WT-1001'); // طلب ليان

        // 2. محاولة ياسمين الوصول المباشر عبر الـ URL لتفاصيل طلب يخص متجر ليان
        $otherOrder = Order::where('store_id', $userLayan->store_id)->first();
        $unauthorizedResponse = $this->get("/orders/{$otherOrder->id}");

        // يجب أن يرفض الطلب ويعيد 404 Not Found لأن الطلب لا ينتمي لمتجرها
        $unauthorizedResponse->assertStatus(404);
    }
}
