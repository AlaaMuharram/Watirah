<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProjectSetting;
use Carbon\Carbon;

class SimulationService
{
    public const NEW_ORDER_ID = '__sim_new__';

    public function __construct(
        protected ScheduleService $scheduler,
        protected RiskPredictionService $riskPredictor
    ) {}

    /**
     * تشغيل محاكاة ضغط العمل
     *
     * @param array $candidate ['product_name' => '', 'quantity' => 1, 'minutes' => 120, 'due_date' => 'YYYY-MM-DD']
     */
    public function simulate(array $candidate, ?iterable $orders = null, ?ProjectSetting $settings = null, ?Carbon $today = null): array
    {
        $settings = $settings ?? ProjectSetting::current();
        $today = $today ? $today->copy()->startOfDay() : Carbon::today();
        $orders = $orders ?? Order::all();

        $minutes = (int) ($candidate['minutes'] ?? 60);
        $dueStr = $candidate['due_date'] ?? $today->copy()->addDays(3)->toDateString();
        $product = $candidate['product_name'] ?? 'طلب جديد افتراضي';

        // 1. الخطة الحالية قبل إضافة الطلب
        $planBefore = $this->scheduler->build($orders, $settings, $today);
        $risksBefore = $this->riskPredictor->assessAll($orders, $settings, $today);

        // 2. تجهيز كائن الطلب الافتراضي
        $virtualOrder = [
            'id' => self::NEW_ORDER_ID,
            'order_number' => 'افتراضي',
            'customer_name' => 'طلب المحاكاة الافتراضي',
            'product_name' => $product,
            'remaining_duration' => $minutes,
            'estimated_duration' => $minutes,
            'received_date' => $today->toDateString(),
            'due_date' => $dueStr,
            'status' => 'new',
        ];

        // 3. الخطة بعد إضافة الطلب
        $ordersAfter = collect($orders)->map(function ($o) {
            return is_array($o) ? $o : $o->toArray();
        })->push($virtualOrder);

        $planAfter = $this->scheduler->build($ordersAfter, $settings, $today);
        $risksAfter = $this->riskPredictor->assessAll($ordersAfter, $settings, $today);

        // 4. قياس الاستغلال والضغط في نافذة المقارنة
        $furthestDue = $dueStr;
        $activeOrders = collect($orders)->filter(fn($o) => ($o['status'] ?? $o->status) !== 'completed');
        foreach ($activeOrders as $o) {
            $d = is_string($o['due_date'] ?? $o->due_date) ? ($o['due_date'] ?? $o->due_date) : ($o['due_date'] ?? $o->due_date)->toDateString();
            if ($d > $furthestDue) {
                $furthestDue = $d;
            }
        }

        $windowCapacity = $this->scheduler->capacityBetween($settings, $today->toDateString(), $furthestDue);
        $totalWorkBefore = $activeOrders->sum(fn($o) => (int) ($o['remaining_duration'] ?? $o->remaining_duration));
        $totalWorkAfter = $totalWorkBefore + $minutes;

        $utilBefore = $windowCapacity > 0 ? round(($totalWorkBefore / $windowCapacity) * 100) : 0;
        $utilAfter = $windowCapacity > 0 ? round(($totalWorkAfter / $windowCapacity) * 100) : 100;

        // 5. الطلبات الحالية المتأثرة
        $affectedOrders = [];
        $riskRanks = ['low' => 1, 'medium' => 2, 'high' => 3];

        foreach ($activeOrders as $o) {
            $id = (string) ($o['id'] ?? $o->id);
            $fb = $planBefore['finish_dates'][$id] ?? null;
            $fa = $planAfter['finish_dates'][$id] ?? null;

            $rb = $risksBefore[$id]['level'] ?? 'low';
            $ra = $risksAfter[$id]['level'] ?? 'low';

            $wasLate = $planBefore['is_late'][$id] ?? false;
            $isNowLate = $planAfter['is_late'][$id] ?? false;

            $pushed = ($fb && $fa && $fa > $fb);
            $rankIncreased = ($riskRanks[$ra] ?? 1) > ($riskRanks[$rb] ?? 1);
            $newlyLate = (!$wasLate && $isNowLate);

            if ($pushed || $rankIncreased || $newlyLate) {
                $shiftDays = ($fb && $fa) ? Carbon::parse($fa)->diffInDays(Carbon::parse($fb)) : 0;
                $affectedOrders[] = [
                    'id' => $id,
                    'order_number' => $o['order_number'] ?? $o->order_number,
                    'customer_name' => $o['customer_name'] ?? $o->customer_name,
                    'finish_before' => $fb,
                    'finish_after' => $fa,
                    'shift_days' => $shiftDays,
                    'risk_before' => $rb,
                    'risk_after' => $ra,
                    'newly_late' => $newlyLate,
                ];
            }
        }

        // 6. هل يتأخر الطلب الجديد نفسه؟
        $newFinish = $planAfter['finish_dates'][self::NEW_ORDER_ID] ?? null;
        $newIsLate = ($newFinish === null || $newFinish > $dueStr);

        // 7. اقتراح موعد تسليم آمن بديل
        $safeSuggestion = $this->suggestSafeDate($orders, $settings, $today, $minutes);

        // 8. تحديد القرار الذكي والتوصية
        $decision = 'yes';
        $decisionLabel = 'قبول آمن';
        $decisionColor = 'emerald';
        $summary = '';

        if ($newIsLate || count(array_filter($affectedOrders, fn($x) => $x['newly_late'])) > 0) {
            $decision = 'no';
            $decisionLabel = 'يُفضل الاعتذار أو تأجيل الموعد';
            $decisionColor = 'rose';
            if ($newIsLate) {
                $summary = "الطلب الجديد لن ينتهي قبل موعده المطلوب ({$dueStr})؛ أقرب موعد واقعي لإنجازه هو {$newFinish}.";
            } else {
                $summary = "قبول الطلب في هذا الموعد سيتسبب في تأخير طلبات عملاء أخرى كانت في موعدها.";
            }
        } elseif ($utilAfter >= 85 || count($affectedOrders) > 0) {
            $decision = 'caution';
            $decisionLabel = 'قبول بحذر';
            $decisionColor = 'amber';
            $summary = "يمكن قبول الطلب لكنه يرفع نسبة استغلال الطاقة الإنتاجية إلى {$utilAfter}% وينقل بعض الطلبات إلى هامش أمان أضيق.";
        } else {
            $summary = "يتسع جدول العمل للطلب الجديد تماماً دون التأثير على مواعيد تسليم أي طلبات أخرى.";
        }

        // 9. تفاصيل الأيام القادمة قبل وبعد
        $daysComparison = [];
        $curr = $today->copy();
        $dailyCap = $this->scheduler->dailyCapacityMinutes($settings);

        for ($i = 0; $i < 14; $i++) {
            $k = $curr->toDateString();
            $isWork = $this->scheduler->isWorkingDay($settings, $curr);
            $cap = $isWork ? $dailyCap : 0;
            $loadB = $planBefore['day_load'][$k] ?? 0;
            $loadA = $planAfter['day_load'][$k] ?? 0;

            $pctB = $cap > 0 ? round(($loadB / $cap) * 100) : 0;
            $pctA = $cap > 0 ? round(($loadA / $cap) * 100) : 0;

            $daysComparison[] = [
                'date' => $k,
                'is_working' => $isWork,
                'capacity' => $cap,
                'load_before' => $loadB,
                'load_after' => $loadA,
                'pct_before' => $pctB,
                'pct_after' => $pctA,
                'increased' => ($loadA > $loadB),
            ];
            $curr->addDay();
        }

        return [
            'decision' => $decision,
            'decision_label' => $decisionLabel,
            'decision_color' => $decisionColor,
            'summary' => $summary,
            'candidate' => [
                'product_name' => $product,
                'minutes' => $minutes,
                'due_date' => $dueStr,
                'finish_date' => $newFinish,
                'is_late' => $newIsLate,
            ],
            'utilization_before' => $utilBefore,
            'utilization_after' => $utilAfter,
            'total_minutes_before' => $totalWorkBefore,
            'total_minutes_after' => $totalWorkAfter,
            'window_capacity' => $windowCapacity,
            'window_end_date' => $furthestDue,
            'affected_orders' => $affectedOrders,
            'safe_suggestion' => $safeSuggestion,
            'days_comparison' => $daysComparison,
        ];
    }

    /**
     * اقتراح أقرب موعد تسليم آمن يتسع للطلب دون تأخير أي طلبات أخرى
     */
    public function suggestSafeDate(iterable $orders, ProjectSetting $settings, Carbon $today, int $minutes): ?array
    {
        $planBefore = $this->scheduler->build($orders, $settings, $today);
        $curr = $this->scheduler->nextWorkingDay($settings, $today);

        $foundDate = null;
        $foundFinish = null;

        for ($i = 0; $i < 60; $i++) {
            $candidateDue = $curr->toDateString();
            $testVirtual = [
                'id' => self::NEW_ORDER_ID,
                'order_number' => 'افتراضي',
                'customer_name' => 'افتراضي',
                'product_name' => 'افتراضي',
                'remaining_duration' => $minutes,
                'estimated_duration' => $minutes,
                'received_date' => $today->toDateString(),
                'due_date' => $candidateDue,
                'status' => 'new',
            ];

            $ordersWithTest = collect($orders)->map(fn($o) => is_array($o) ? $o : $o->toArray())->push($testVirtual);
            $testPlan = $this->scheduler->build($ordersWithTest, $settings, $today);

            $finish = $testPlan['finish_dates'][self::NEW_ORDER_ID] ?? null;
            $newOk = ($finish !== null && $finish <= $candidateDue);

            $noExistingDelayed = true;
            foreach ($orders as $o) {
                $id = (string) ($o['id'] ?? $o->id);
                $wasLate = $planBefore['is_late'][$id] ?? false;
                $isLateNow = $testPlan['is_late'][$id] ?? false;
                if (!$wasLate && $isLateNow) {
                    $noExistingDelayed = false;
                    break;
                }
            }

            if ($newOk && $noExistingDelayed) {
                $foundDate = $candidateDue;
                $foundFinish = $finish;
                break;
            }

            $curr->addDay();
            $curr = $this->scheduler->nextWorkingDay($settings, $curr);
        }

        if (!$foundDate) {
            return null;
        }

        // تطبيق هامش الأمان (Buffer days)
        $buffer = max(0, (int) ($settings->buffer_days ?? 1));
        $bufferedDate = $this->scheduler->addWorkingDays($settings, $foundDate, $buffer)->toDateString();

        return [
            'earliest_date' => $foundDate,
            'finish_date' => $foundFinish,
            'recommended_date' => $bufferedDate,
            'buffer_days' => $buffer,
        ];
    }
}
