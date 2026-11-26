<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProjectSetting;
use Carbon\Carbon;

class AiAssistantService
{
    public function __construct(
        protected ScheduleService $scheduler,
        protected RiskPredictionService $riskPredictor,
        protected SimulationService $simulator
    ) {}

    public function ask(string $question, ?int $storeId = null): array
    {
        $storeId = $storeId ?: auth()->user()?->store_id;
        $settings = ProjectSetting::current($storeId);
        $today = Carbon::today();
        $orders = Order::when($storeId, fn($q) => $q->where('store_id', $storeId))->get();

        $plan = $this->scheduler->build($orders, $settings, $today);
        $risks = $this->riskPredictor->assessAll($orders, $settings, $today);
        $activeOrders = $orders->where('status', '!=', 'completed');

        $q = trim($question);

        if (mb_stripos($q, 'استقبال طلب') !== false || mb_stripos($q, 'هذا الأسبوع') !== false) {
            return $this->handleAcceptOrderThisWeek($orders, $settings, $today, $plan);
        }

        if (mb_stripos($q, 'أكثر الأيام') !== false || mb_stripos($q, 'ازدحام') !== false) {
            return $this->handleBusiestDays($settings, $plan, $today);
        }

        if (mb_stripos($q, 'معرضة للتأخير') !== false || mb_stripos($q, 'تأخير') !== false || mb_stripos($q, 'خطر') !== false) {
            return $this->handleDelayedOrders($risks, $activeOrders);
        }

        if (mb_stripos($q, 'سبب') !== false || mb_stripos($q, 'ضغط العمل') !== false) {
            return $this->handleWorkloadCauses($activeOrders, $settings, $plan, $today);
        }

        if (mb_stripos($q, 'الموعد المناسب') !== false || mb_stripos($q, 'موعد آمن') !== false) {
            return $this->handleSuggestedDate($orders, $settings, $today);
        }

        return $this->handleProjectSummary($activeOrders, $settings, $plan, $risks);
    }

    protected function handleAcceptOrderThisWeek($orders, $settings, Carbon $today, array $plan): array
    {
        $weekEnd = $today->copy()->endOfWeek(Carbon::THURSDAY);
        if ($weekEnd->lt($today)) {
            $weekEnd->addWeek();
        }

        $freeMinutes = $this->scheduler->capacityBetween($settings, $today, $weekEnd);
        $usedMinutes = 0;
        foreach ($plan['orders'] as $o) {
            $usedMinutes += $o['rem'];
        }

        $sim = $this->simulator->simulate([
            'product_name' => 'طلب افتراضي متوسط',
            'minutes' => 120,
            'due_date' => $weekEnd->toDateString(),
        ], $orders, $settings, $today);

        return [
            'question' => 'هل أستطيع استقبال طلب جديد هذا الأسبوع؟',
            'tone' => $sim['decision_color'],
            'decision' => $sim['decision_label'],
            'message' => "نتيجة المحاكاة لطلب متوسط (ساعتان) بموعد نهاية الأسبوع ({$weekEnd->toDateString()}): «{$sim['decision_label']}».",
            'bullets' => [
                "الوقت الإنتاجي المتاح حتى نهاية الأسبوع: {$freeMinutes} دقيقة، والالتزامات الحالية تستهلك جزءاً منه.",
                "نسبة استغلال الطاقة الإنتاجية ستصل إلى {$sim['utilization_after']}% في حال قبول الطلب.",
                $sim['summary'],
                "أقرب موعد تسليم آمن دون ضغط يوصى به: " . ($sim['safe_suggestion']['recommended_date'] ?? 'بداية الأسبوع القادم'),
            ],
        ];
    }

    protected function handleBusiestDays($settings, array $plan, Carbon $today): array
    {
        $overview = $this->riskPredictor->getUpcomingDaysOverview($settings, $plan, $today, 14);
        $busyDays = collect($overview)
            ->filter(fn($d) => $d['is_working'] && $d['load'] > 0)
            ->sortByDesc('load_percentage')
            ->take(3);

        $bullets = [];
        foreach ($busyDays as $d) {
            $bullets[] = "يوم {$d['day_name']} ({$d['date']}): محجوز بنسبة {$d['load_percentage']}% ({$d['load']} دقيقة من أصل {$d['capacity']} دقيقة إنتاجية).";
        }

        return [
            'question' => 'ما أكثر الأيام ازدحاماً؟',
            'tone' => 'amber',
            'decision' => 'توزيع عبء العمل',
            'message' => 'الأيام الأكثر ضغطاً وازدحاماً بالمهام خلال الفترة القادمة هي:',
            'bullets' => !empty($bullets) ? $bullets : ['لا توجد أيام مضغوطة حالياً، والجدول به مساحات تشغيلية كافية.'],
        ];
    }

    protected function handleDelayedOrders(array $risks, $activeOrders): array
    {
        $high = [];
        $medium = [];

        foreach ($risks as $r) {
            if ($r['level'] === 'high') {
                $high[] = ($r['order']->customer_name ?? 'طلب') . " ({$r['order']->order_number}): " . ($r['reasons'][0] ?? 'خطر تأخير مرتفع');
            } elseif ($r['level'] === 'medium') {
                $medium[] = ($r['order']->customer_name ?? 'طلب') . " ({$r['order']->order_number}): " . ($r['reasons'][0] ?? 'هامش أمان ضيق');
            }
        }

        $all = array_merge($high, $medium);

        return [
            'question' => 'ما الطلبات المعرضة للتأخير؟',
            'tone' => !empty($high) ? 'rose' : (!empty($medium) ? 'amber' : 'emerald'),
            'decision' => !empty($high) ? 'تدخل عاجل مطلوب' : 'متابعة دورية',
            'message' => !empty($all) ? "تم رصد " . count($all) . " طلبات تتطلب الانتباه والمتابعة الحثيثة:" : "ممتاز! جميع الطلبات الحالية تسير وفق الجدول المحدد دون أي مؤشرات تأخير.",
            'bullets' => !empty($all) ? $all : ['القدرة الإنتاجية اليومية تغطي متطلبات العمل المتبقية بهوامش أمان مريحة.'],
        ];
    }

    protected function handleWorkloadCauses($activeOrders, $settings, array $plan, Carbon $today): array
    {
        $totalRem = $activeOrders->sum('remaining_duration');
        $dailyCap = $this->scheduler->dailyCapacityMinutes($settings);
        $daysNeeded = $dailyCap > 0 ? ceil($totalRem / $dailyCap) : 0;
        $lateCount = $activeOrders->where('status', 'late')->count();

        $top = $activeOrders->sortByDesc('remaining_duration')->take(3);
        $bullets = [
            "إجمالي العمل المتبقي في ورشة متجرك هو {$totalRem} دقيقة (حوالي " . round($totalRem / 60, 1) . " ساعات عمل فعلية).",
            "بمعدل إنتاجيتك اليومية ({$dailyCap} دقيقة)، تحتاج الورشة إلى قرابة {$daysNeeded} أيام عمل متواصلة بدون توقف.",
        ];

        if ($lateCount > 0) {
            $bullets[] = "يوجد {$lateCount} طلب متأخر بالفعل يستنزف وقتاً من جدول الأيام الحالية لتعويض التأخير.";
        }

        foreach ($top as $o) {
            $bullets[] = "طلب العميل {$o->customer_name} ({$o->product_name}) يمثل عبئاً بمقدار {$o->remaining_duration} دقيقة متبقية.";
        }

        return [
            'question' => 'ما سبب ارتفاع ضغط العمل؟',
            'tone' => 'amber',
            'decision' => 'تحليل الضغط والجدولة',
            'message' => 'يرجع ضغط العمل إلى تراكم ساعات التنفيذ المطلوبة وتزامن مواعيد التسليم في فترات متقاربة:',
            'bullets' => $bullets,
        ];
    }

    protected function handleSuggestedDate($orders, $settings, Carbon $today): array
    {
        $avgDuration = (int) ($orders->where('status', '!=', 'completed')->avg('estimated_duration') ?: 120);
        $safe = $this->simulator->suggestSafeDate($orders, $settings, $today, $avgDuration);

        return [
            'question' => 'ما الموعد المناسب لتسليم طلب جديد؟',
            'tone' => 'emerald',
            'decision' => 'اقتراح موعد آمن',
            'message' => "بناءً على حجم الطلبات المتوسط الحالي ({$avgDuration} دقيقة):",
            'bullets' => [
                "أقرب تاريخ ممكن نظرياً لإنجاز الطلب: " . ($safe['earliest_date'] ?? 'غير متاح'),
                "الموعد الموصى به للعميل (بعد احتساب هامش أمان {$safe['buffer_days']} يوم عمل): " . ($safe['recommended_date'] ?? 'غير متاح'),
                "هذا الموعد يضمن عدم الضغط على مواعيد طلبات العملاء الحاليين ويمنحك مرونة كافية لتفادي الطوارئ.",
            ],
        ];
    }

    protected function handleProjectSummary($activeOrders, $settings, array $plan, array $risks): array
    {
        $totalActive = $activeOrders->count();
        $totalMinutes = $activeOrders->sum('remaining_duration');
        $lateCount = $activeOrders->where('status', 'late')->count();
        $highRiskCount = collect($risks)->where('level', 'high')->count();

        return [
            'question' => 'لخّص حالة المشروع',
            'tone' => $highRiskCount > 0 ? 'rose' : 'blue',
            'decision' => 'تقرير الحالة التشغيلية للمتجر',
            'message' => "متجرك يضم حالياً {$totalActive} طلبات نشطة بإجمالي {$totalMinutes} دقيقة عمل متبقية.",
            'bullets' => [
                "الطلبات المتأخرة حالياً: {$lateCount}",
                "الطلبات المصنفة بخطر تأخير مرتفع: {$highRiskCount}",
                "ساعات العمل اليومية المنتجة المحددة في الإعدادات: " . round($this->scheduler->dailyCapacityMinutes($settings) / 60, 1) . " ساعات.",
                "يُنصح باستخدام محاكي ضغط العمل قبل إعطاء موعد نهائي لأي عميل جديد.",
            ],
        ];
    }
}
