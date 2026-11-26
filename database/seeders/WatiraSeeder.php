<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\ExecutionLog;
use App\Models\Order;
use App\Models\ProductType;
use App\Models\ProjectSetting;
use App\Models\Store;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WatiraSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        $addWorkDays = function (Carbon $date, int $days) {
            $d = $date->copy();
            $added = 0;
            $step = $days >= 0 ? 1 : -1;
            $target = abs($days);
            while ($added < $target) {
                $d->addDays($step);
                if (in_array($d->dayOfWeek, [0, 1, 2, 3, 4])) {
                    $added++;
                }
            }
            return $d;
        };

        // ==========================================
        // 1. المتجر الأول: ورود ليان
        // ==========================================
        $store1 = Store::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'ورود ليان — ورود دائمة وهدايا يدوية',
                'phone' => '0501234567',
                'email' => 'layan@watira.com',
                'description' => 'مشروع منزلي متخصص في صناعة الورود الدائمة من أعواد الشانيل وتنسيق بوكسات الهدايا والتوزيعات.',
            ]
        );

        // مستخدم متجر ليان
        User::updateOrCreate(
            ['email' => 'layan@watira.com'],
            [
                'name' => 'ليان العتيبي',
                'phone' => '0501234567',
                'store_id' => $store1->id,
                'password' => Hash::make('password123'),
            ]
        );

        // إعدادات متجر ليان
        ProjectSetting::updateOrCreate(
            ['store_id' => $store1->id],
            [
                'id' => 1,
                'store_id' => $store1->id,
                'project_name' => $store1->name,
                'working_days' => [0, 1, 2, 3, 4], // الأحد - الخميس
                'start_time' => '09:00',
                'end_time' => '17:00',
                'daily_productive_hours' => 5.0,
                'break_duration' => 60,
                'buffer_days' => 1,
            ]
        );

        // منتجات متجر ليان
        $products1 = [
            ['id' => 'p1', 'store_id' => $store1->id, 'product_name' => 'باقة ورد دائمة', 'estimated_unit_duration' => 90, 'notes' => 'باقة من أعواد الشانيل مع تغليف حريري'],
            ['id' => 'p2', 'store_id' => $store1->id, 'product_name' => 'وردة منفردة', 'estimated_unit_duration' => 15, 'notes' => 'وردة واحدة بساق طويلة وغلاف حماية'],
            ['id' => 'p3', 'store_id' => $store1->id, 'product_name' => 'بوكس هدايا', 'estimated_unit_duration' => 120, 'notes' => 'بوكس مع ورود صغيرة وبطاقة إهداء فاخرة'],
            ['id' => 'p4', 'store_id' => $store1->id, 'product_name' => 'توزيعات مناسبات', 'estimated_unit_duration' => 20, 'notes' => 'وردة مصغّرة مغلّفة للتوزيع في المناسبات'],
            ['id' => 'p5', 'store_id' => $store1->id, 'product_name' => 'لوحة ديكور يدوية', 'estimated_unit_duration' => 180, 'notes' => 'لوحة ورد دائم جدارية للتعليق'],
        ];

        foreach ($products1 as $p) {
            ProductType::updateOrCreate(['id' => $p['id']], $p);
        }

        // طلبات متجر ليان
        $orderData1 = [
            [
                'id' => 'o1',
                'store_id' => $store1->id,
                'order_number' => 'WT-1001',
                'customer_name' => 'نورة العتيبي',
                'customer_phone' => '0501234567',
                'product_name' => 'باقة ورد دائمة',
                'description' => '6 باقات بألوان الباستيل لحفل تخرج، تغليف كريمي وشريط ذهبي',
                'quantity' => 6,
                'unit_duration' => 90,
                'estimated_duration' => 540,
                'remaining_duration' => 0,
                'status' => 'completed',
                'risk_level' => 'low',
                'received_date' => $today->copy()->subDays(9)->toDateString(),
                'due_date' => $addWorkDays($today, -3)->toDateString(),
                'completed_at' => $addWorkDays($today, -4)->setTime(14, 0),
                'notes' => 'سُلّم الطلب في الموعد ونال إعجاب العميلة',
                'tasks' => [
                    ['title' => 'قص وتشكيل أعواد الشانيل للورود الستة', 'minutes' => 240, 'status' => 'completed', 'priority' => 'high'],
                    ['title' => 'تجميع السيقان وتثبيت الأوراق الخضراء', 'minutes' => 120, 'status' => 'completed', 'priority' => 'medium'],
                    ['title' => 'التغليف بالورق الكريمي والشريط الذهبي', 'minutes' => 120, 'status' => 'completed', 'priority' => 'medium'],
                    ['title' => 'فحص الجودة وإعداد كرت الإهداء والتسليم', 'minutes' => 60, 'status' => 'completed', 'priority' => 'low'],
                ]
            ],
            [
                'id' => 'o2',
                'store_id' => $store1->id,
                'order_number' => 'WT-1002',
                'customer_name' => 'سارة القحطاني',
                'customer_phone' => '0559876543',
                'product_name' => 'بوكس هدايا',
                'description' => '3 بوكسات لموظفات الشركة: ورود صغيرة مع بطاقة شكر مخصصة',
                'quantity' => 3,
                'unit_duration' => 120,
                'estimated_duration' => 360,
                'remaining_duration' => 180,
                'status' => 'in_progress',
                'risk_level' => 'medium',
                'received_date' => $today->copy()->subDays(5)->toDateString(),
                'due_date' => $addWorkDays($today, 1)->toDateString(),
                'completed_at' => null,
                'notes' => 'تم تجهيز بوكسين وبقي بوكس واحد ونصف',
                'tasks' => [
                    ['title' => 'تجهيز البوكس الأول بالورود وبطاقة الشكر', 'minutes' => 120, 'status' => 'completed', 'priority' => 'high'],
                    ['title' => 'تجهيز البوكس الثاني وتنسيقه', 'minutes' => 120, 'status' => 'completed', 'priority' => 'high'],
                    ['title' => 'تجهيز البوكس الثالث المتبقي وإنهاء التغليف', 'minutes' => 120, 'status' => 'in_progress', 'priority' => 'high'],
                ]
            ],
            [
                'id' => 'o3',
                'store_id' => $store1->id,
                'order_number' => 'WT-1003',
                'customer_name' => 'مها الشمري',
                'customer_phone' => '0543210987',
                'product_name' => 'توزيعات مناسبات',
                'description' => '33 وردة مصغّرة لحفل استقبال مولودة، شريط وردي مع تاقات الاسم',
                'quantity' => 33,
                'unit_duration' => 20,
                'estimated_duration' => 660,
                'remaining_duration' => 660,
                'status' => 'waiting',
                'risk_level' => 'high',
                'received_date' => $today->copy()->subDays(2)->toDateString(),
                'due_date' => $addWorkDays($today, 4)->toDateString(),
                'completed_at' => null,
                'notes' => 'بانتظار تأكيد العميل على درجات اللون الوردي النهائي',
                'tasks' => [
                    ['title' => 'اعتماد درجات اللون الوردي وطباعة تاقات الاسم', 'minutes' => 60, 'status' => 'pending', 'priority' => 'high'],
                    ['title' => 'صناعة الورود المصغّرة (33 قطعة)', 'minutes' => 450, 'status' => 'pending', 'priority' => 'high'],
                    ['title' => 'تغليف التوزيعات وربط الشريط الوردي', 'minutes' => 150, 'status' => 'pending', 'priority' => 'medium'],
                ]
            ],
            [
                'id' => 'o4',
                'store_id' => $store1->id,
                'order_number' => 'WT-1004',
                'customer_name' => 'هند الغامدي',
                'customer_phone' => '0567891234',
                'product_name' => 'باقة ورد دائمة',
                'description' => 'باقتان بتدرجات الأحمر لهدية زواج راقية',
                'quantity' => 2,
                'unit_duration' => 90,
                'estimated_duration' => 180,
                'remaining_duration' => 120,
                'status' => 'late',
                'risk_level' => 'high',
                'received_date' => $today->copy()->subDays(10)->toDateString(),
                'due_date' => $addWorkDays($today, -1)->toDateString(),
                'completed_at' => null,
                'notes' => 'بقي التغليف النهائي وبطاقة الإهداء الخاصة، والطلب تجاوز موعده',
                'tasks' => [
                    ['title' => 'صناعة الورود الحمراء وتنسيق الباقتين', 'minutes' => 120, 'status' => 'completed', 'priority' => 'high'],
                    ['title' => 'تغليف الباقتين بالورق الفاخر وإرفاق الكرت', 'minutes' => 60, 'status' => 'in_progress', 'priority' => 'high'],
                ]
            ],
            [
                'id' => 'o5',
                'store_id' => $store1->id,
                'order_number' => 'WT-1005',
                'customer_name' => 'لينا المطيري',
                'customer_phone' => '0534567890',
                'product_name' => 'باقة ورد دائمة',
                'description' => '4 باقات بنفسجي مع تغليف أبيض لذكرى تخرج',
                'quantity' => 4,
                'unit_duration' => 90,
                'estimated_duration' => 360,
                'remaining_duration' => 360,
                'status' => 'new',
                'risk_level' => 'medium',
                'received_date' => $today->copy()->subDays(1)->toDateString(),
                'due_date' => $addWorkDays($today, 2)->toDateString(),
                'completed_at' => null,
                'notes' => 'طلب جديد يحتاج بدء العمل مبكراً نظراً لضغط الجدول',
                'tasks' => [
                    ['title' => 'تجهيز أعواد الشانيل البنفسجية والأوراق', 'minutes' => 60, 'status' => 'pending', 'priority' => 'medium'],
                    ['title' => 'صناعة الورود وتنسيق 4 باقات', 'minutes' => 220, 'status' => 'pending', 'priority' => 'high'],
                    ['title' => 'تغليف الباقات بالورق الأبيض', 'minutes' => 80, 'status' => 'pending', 'priority' => 'medium'],
                ]
            ],
            [
                'id' => 'o6',
                'store_id' => $store1->id,
                'order_number' => 'WT-1006',
                'customer_name' => 'عبدالله الحربي',
                'customer_phone' => '0512349876',
                'product_name' => 'بوكس هدايا',
                'description' => 'بوكسان لعيد زواج، ورد أحمر دائم وشوكولاتة مدمجة',
                'quantity' => 2,
                'unit_duration' => 120,
                'estimated_duration' => 240,
                'remaining_duration' => 240,
                'status' => 'new',
                'risk_level' => 'low',
                'received_date' => $today->toDateString(),
                'due_date' => $addWorkDays($today, 3)->toDateString(),
                'completed_at' => null,
                'notes' => 'الشوكولاتة يوفرها العميل لاستكمال التنسيق',
                'tasks' => [
                    ['title' => 'صناعة باقة الورد الأحمر المصغر', 'minutes' => 140, 'status' => 'pending', 'priority' => 'medium'],
                    ['title' => 'تنسيق البوكس ودمج الشوكولاتة والتغليف', 'minutes' => 100, 'status' => 'pending', 'priority' => 'medium'],
                ]
            ],
            [
                'id' => 'o7',
                'store_id' => $store1->id,
                'order_number' => 'WT-1007',
                'customer_name' => 'جود السبيعي',
                'customer_phone' => '0578901234',
                'product_name' => 'وردة منفردة',
                'description' => '12 وردة منفردة بألوان متدرجة مع تغليف أنيق لكل وردة',
                'quantity' => 12,
                'unit_duration' => 15,
                'estimated_duration' => 180,
                'remaining_duration' => 180,
                'status' => 'waiting',
                'risk_level' => 'low',
                'received_date' => $today->copy()->subDays(3)->toDateString(),
                'due_date' => $addWorkDays($today, 6)->toDateString(),
                'completed_at' => null,
                'notes' => 'بانتظار وصول نوع محدد من أوراق التغليف الشفافة',
                'tasks' => [
                    ['title' => 'صناعة 12 وردة منفردة', 'minutes' => 120, 'status' => 'pending', 'priority' => 'low'],
                    ['title' => 'تغليف كل وردة بشكل منفصل مع بطاقة شكر', 'minutes' => 60, 'status' => 'pending', 'priority' => 'low'],
                ]
            ],
            [
                'id' => 'o8',
                'store_id' => $store1->id,
                'order_number' => 'WT-1008',
                'customer_name' => 'فاطمة الزهراني',
                'customer_phone' => '0590123456',
                'product_name' => 'لوحة ديكور يدوية',
                'description' => 'لوحة ورد دائم لصالة المنزل، مقاس 50×70، ألوان رمادي وأبيض',
                'quantity' => 1,
                'unit_duration' => 180,
                'estimated_duration' => 180,
                'remaining_duration' => 180,
                'status' => 'new',
                'risk_level' => 'low',
                'received_date' => $today->copy()->subDays(1)->toDateString(),
                'due_date' => $addWorkDays($today, 8)->toDateString(),
                'completed_at' => null,
                'notes' => 'ألوان متناسقة مع الصالة الحديثة',
                'tasks' => [
                    ['title' => 'تجهيز الإطار الخشبي والخلفية القماشية', 'minutes' => 45, 'status' => 'pending', 'priority' => 'low'],
                    ['title' => 'صناعة الورود وتثبيتها بتنسيق فني متدرج', 'minutes' => 105, 'status' => 'pending', 'priority' => 'medium'],
                    ['title' => 'تثبيت اللوحة واللمسات النهائية وتغليف الشحن', 'minutes' => 30, 'status' => 'pending', 'priority' => 'low'],
                ]
            ],
        ];

        foreach ($orderData1 as $row) {
            $tasks = $row['tasks'] ?? [];
            unset($row['tasks']);

            $order = Order::updateOrCreate(['id' => $row['id']], $row);

            Task::where('order_id', $order->id)->delete();
            foreach ($tasks as $idx => $t) {
                Task::create([
                    'id' => 't_' . $order->id . '_' . ($idx + 1),
                    'order_id' => $order->id,
                    'title' => $t['title'],
                    'duration_minutes' => $t['minutes'],
                    'status' => $t['status'],
                    'priority' => $t['priority'],
                    'sort_order' => $idx + 1,
                    'completed_at' => $t['status'] === 'completed' ? now()->subDays(1) : null,
                ]);
            }

            ExecutionLog::where('order_id', $order->id)->delete();
            ExecutionLog::create([
                'id' => 'log_' . $order->id . '_1',
                'order_id' => $order->id,
                'old_status' => null,
                'new_status' => 'new',
                'changed_at' => Carbon::parse($order->received_date)->setTime(9, 30),
                'notes' => 'تم استلام وتسجيل الطلب في متجر ورود ليان',
            ]);
        }

        Alert::where('store_id', $store1->id)->delete();
        Alert::create([
            'id' => 'alt_1',
            'store_id' => $store1->id,
            'order_id' => 'o4',
            'alert_type' => 'overdue',
            'alert_key' => 'order_overdue_o4',
            'message' => 'الطلب WT-1004 (هند الغامدي) تجاوز موعد التسليم ولا يزال متبقياً 120 دقيقة عمل.',
            'action' => 'تواصل مع العميلة لتحديد موعد بديل وإعطاء الأولوية القصوى لإنجاز التغليف.',
            'risk_level' => 'high',
            'is_read' => false,
            'created_at' => now()->subHours(4),
        ]);

        // ==========================================
        // 2. المتجر الثاني التجريبي (لعزل البيانات): خزف وفخار ياسمين
        // ==========================================
        $store2 = Store::updateOrCreate(
            ['id' => 2],
            [
                'name' => 'خزف وفخار ياسمين — فنون الفخار اليدوي',
                'phone' => '0555555555',
                'email' => 'yasmeen@watira.com',
                'description' => 'ورشة متخصصة في صناعة الأكواب والفناجين والمزهريات الفخارية المشغولة يدوياً وتلوينها بالفرن الحراري.',
            ]
        );

        User::updateOrCreate(
            ['email' => 'yasmeen@watira.com'],
            [
                'name' => 'ياسمين المحمدي',
                'phone' => '0555555555',
                'store_id' => $store2->id,
                'password' => Hash::make('password123'),
            ]
        );

        ProjectSetting::updateOrCreate(
            ['store_id' => $store2->id],
            [
                'id' => 2,
                'store_id' => $store2->id,
                'project_name' => $store2->name,
                'working_days' => [0, 1, 2, 3, 4, 5], // الأحد - الجمعة
                'start_time' => '10:00',
                'end_time' => '18:00',
                'daily_productive_hours' => 6.0,
                'break_duration' => 60,
                'buffer_days' => 2,
            ]
        );

        ProductType::updateOrCreate(
            ['id' => 'p_pottery_1'],
            [
                'id' => 'p_pottery_1',
                'store_id' => $store2->id,
                'product_name' => 'طقم فناجين فخارية يدوية',
                'estimated_unit_duration' => 150,
                'notes' => 'طقم من 4 فناجين مع تشكيل وتلوين وحرق بالفرن',
            ]
        );

        $orderPottery = Order::updateOrCreate(
            ['id' => 'o_pottery_1'],
            [
                'id' => 'o_pottery_1',
                'store_id' => $store2->id,
                'order_number' => 'YP-2001',
                'customer_name' => 'رغد الشريف',
                'customer_phone' => '0566667788',
                'product_name' => 'طقم فناجين فخارية يدوية',
                'description' => 'طقم فناجين قهوة بلون أزرق بحري مع كتابة اسم الإهداء',
                'quantity' => 1,
                'unit_duration' => 150,
                'estimated_duration' => 150,
                'remaining_duration' => 150,
                'status' => 'new',
                'risk_level' => 'low',
                'received_date' => $today->toDateString(),
                'due_date' => $addWorkDays($today, 5)->toDateString(),
                'completed_at' => null,
                'notes' => 'مخصص لمتجر ياسمين فقط',
            ]
        );

        Task::create([
            'id' => 't_pottery_1',
            'order_id' => $orderPottery->id,
            'title' => 'عجن وتشكيل الطين على العجلة الدوارة',
            'duration_minutes' => 60,
            'status' => 'pending',
            'priority' => 'high',
            'sort_order' => 1,
        ]);
    }
}
