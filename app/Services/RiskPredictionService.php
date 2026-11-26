<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProjectSetting;
use Carbon\Carbon;

class RiskPredictionService
{
    public function __construct(
        protected ScheduleService $scheduler
    ) {}

    /**
     * تقييم مخاطر التأخير لجميع الطلبات وتحديد أسباب ومستوى الخطر
     */
    public function assessAll(?iterable $orders = null, ?ProjectSetting $settings = null, ?Carbon $today = null): array
    {
        $settings = $settings ?? ProjectSetting::current();
        $today = $today ? $today->copy()->startOfDay() : Carbon::today();
        $orders = $orders ?? Order::with('tasks')->get();

        $plan = $this->scheduler->build($orders, $settings, $today);

        $results = [];
        $activeMap = [];
        foreach ($orders as $o) {
            $k = is_array($o) ? ($o['id'] ?? '') : ($o->id ?? '');
            if ($k) {
                $activeMap[$k] = $o;
            }
        }

        $todayStr = $today->toDateString();

        foreach ($plan['orders'] as $item) {
            $id = $item['id'];
            $dueStr = $item['due'];
            $receivedStr = $item['received'];
            $rem = $item['rem'];
            $order = $activeMap[$id] ?? null;

            // حساب الطلب التراكمي: مجموع الدقائق المطلوبة لكل الطلبات التي موعدها <= موعد هذا الطلب
            $cumDemand = 0;
            $competing = 0;
            foreach ($plan['orders'] as $other) {
                if ($other['due'] <= $dueStr) {
                    $cumDemand += $other['rem'];
                    if ($other['id'] !== $id && $other['rem'] > 0) {
                        $competing++;
                    }
                }
            }

            // السعة المتاحة من اليوم (أو تاريخ الاستلام) حتى موعد التسليم
            $startStr = max($receivedStr, $todayStr);
            $available = ($dueStr < $startStr) ? 0 : $this->scheduler->capacityBetween($settings, $startStr, $dueStr);

            $slack = $available - $cumDemand;
            $ratio = $available > 0 ? ($cumDemand / $available) : ($cumDemand > 0 ? 999.0 : 0.0);
            $finish = $plan['finish_dates'][$id] ?? null;

            $diffDays = Carbon::parse($dueStr)->diffInDays($today, false); // سالب إذا كان بالماضي
            $isOverdue = $dueStr < $todayStr;
            $willMiss = !$isOverdue && ($finish === null || $finish > $dueStr);

            $level = 'low';
            $reasons = [];
            $actions = [];

            if ($isOverdue) {
                $level = 'high';
                $daysAgo = abs((int) $diffDays);
                $reasons[] = "تجاوز موعد التسليم المحدد ({$dueStr}) منذ {$daysAgo} يوم، ومتبقي من العمل {$rem} دقيقة.";
                $actions[] = "تواصل مع العميل لإبلاغه بالموعد الجديد وإعادة جدولة التسليم.";
                $actions[] = "منح هذا الطلب الأولوية القصوى للتنفيذ فوراً قبل أي عمل آخر.";
            } elseif ($willMiss) {
                $level = 'high';
                $deficit = max(0, $cumDemand - $available);
                $reasons[] = "الوقت الإنتاجي المتاح حتى موعد التسليم ({$dueStr}) هو {$available} دقيقة، بينما الطلب مع الالتزامات المتزامنة يتطلب {$cumDemand} دقيقة (عجز قدره {$deficit} دقيقة).";
                if ($finish) {
                    $reasons[] = "أقرب إنجاز متوقع حسب جدول العمل الحالي هو {$finish} (أي بعد الموعد المتفق عليه).";
                }
                if ($competing > 0) {
                    $reasons[] = "يتنافس على نفس الفترة مع {$competing} طلبات أخرى مستحقة في نفس الموعد أو قبله.";
                }
                $actions[] = "إعادة ترتيب أولويات المهام وتقديم هذا الطلب، أو زيادة ساعات العمل الإنتاجية بمقدار {$deficit} دقيقة.";
                $actions[] = "التواصل الاستباقي مع العميل لاقتراح موعد تسليم آمن بديل (حوالي {$finish}).";
                $actions[] = "تجنب قبول أي طلبات عاجلة إضافية خلال هذه الفترة.";
            } else {
                if ($ratio >= 0.8) {
                    $level = 'medium';
                    $pct = round($ratio * 100);
                    $reasons[] = "الجدول ممتلئ بنسبة عالية: المطلوب استغلال {$pct}% من السعة المتاحة حتى موعد التسليم، والهامش المتبقي {$slack} دقيقة فقط.";
                    if ($competing > 0) {
                        $reasons[] = "يتزامن مع {$competing} طلبات أخرى في نفس نافذة التنفيذ.";
                    }
                }

                if (abs((int) $diffDays) <= 1 && in_array($item['status'], ['new', 'waiting']) && $rem > 0) {
                    $level = 'medium';
                    $reasons[] = "موعد التسليم غداً أو بعد غد والطلب لا يزال بحالة '{$item['status']}' ولم يبدأ التنفيذ بعد.";
                }

                if ($level === 'medium') {
                    $actions[] = "البدء بتنفيذ المهام المتبقية لهذا الطلب مبكراً لضمان توفير هامش أمان.";
                    $actions[] = "إجراء محاكاة ضغط عمل قبل استقبال أي طلبات جديدة بنفس تاريخ الاستحقاق.";
                } else {
                    $reasons[] = "الوقت الإنتاجي المتاح ({$available} دقيقة) يغطي متطلبات العمل ({$cumDemand} دقيقة) مع هامش أمان كافٍ ({$slack} دقيقة).";
                    $actions[] = "الوضع مستقر؛ الاستمرار في التنفيذ وفق الجدولة الحالية.";
                }
            }

            $results[$id] = [
                'id' => $id,
                'order' => $order,
                'level' => $level,
                'reasons' => $reasons,
                'actions' => $actions,
                'remaining_minutes' => $rem,
                'cumulative_demand' => $cumDemand,
                'available_capacity' => $available,
                'slack_minutes' => $slack,
                'capacity_ratio' => $ratio,
                'expected_finish' => $finish,
                'is_overdue' => $isOverdue,
                'will_miss_deadline' => $willMiss,
                'competing_orders' => $competing,
            ];

            // تحديث مستوى الخطر في قاعدة البيانات إذا كان محفوظاً
            if ($order instanceof Order && $order->risk_level !== $level) {
                $order->update(['risk_level' => $level]);
            }
        }

        return $results;
    }

    /**
     * حساب العجز اليومي وحالة الأيام القادمة
     */
    public function getUpcomingDaysOverview(ProjectSetting $settings, array $plan, ?Carbon $today = null, int $daysCount = 14): array
    {
        $today = $today ? $today->copy()->startOfDay() : Carbon::today();
        $dailyCap = $this->scheduler->dailyCapacityMinutes($settings);

        $days = [];
        $curr = $today->copy();
        $guard = 0;

        while (count($days) < $daysCount && $guard++ < 60) {
            $isWork = $this->scheduler->isWorkingDay($settings, $curr);
            $key = $curr->toDateString();
            $load = $plan['day_load'][$key] ?? 0;
            $items = $plan['by_day'][$key] ?? [];

            $cap = $isWork ? $dailyCap : 0;
            $pct = $cap > 0 ? round(($load / $cap) * 100) : 0;

            $status = 'off';
            if ($isWork) {
                if ($pct >= 100) {
                    $status = 'full';
                } elseif ($pct >= 80) {
                    $status = 'busy';
                } else {
                    $status = 'available';
                }
            }

            $days[] = [
                'date' => $key,
                'day_name' => $this->arabicDayName($curr->dayOfWeek),
                'is_working' => $isWork,
                'capacity' => $cap,
                'load' => $load,
                'load_percentage' => $pct,
                'status' => $status,
                'items_count' => count($items),
            ];

            $curr->addDay();
        }

        return $days;
    }

    private function arabicDayName(int $dayOfWeek): string
    {
        return match ($dayOfWeek) {
            0 => 'الأحد',
            1 => 'الإثنين',
            2 => 'الثلاثاء',
            3 => 'الأربعاء',
            4 => 'الخميس',
            5 => 'الجمعة',
            6 => 'السبت',
            default => '',
        };
    }
}
