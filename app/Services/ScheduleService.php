<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProjectSetting;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ScheduleService
{
    /**
     * هل اليوم المحدد هو يوم عمل حسب إعدادات المشروع؟
     */
    public function isWorkingDay(ProjectSetting $settings, Carbon|string $date): bool
    {
        $d = is_string($date) ? Carbon::parse($date) : $date->copy();
        $workingDays = $settings->working_days ?? [0, 1, 2, 3, 4];
        return in_array($d->dayOfWeek, $workingDays);
    }

    /**
     * السعة اليومية بالدقائق
     */
    public function dailyCapacityMinutes(ProjectSetting $settings): int
    {
        $hours = (float) ($settings->daily_productive_hours ?? 5.0);
        return (int) round($hours * 60);
    }

    /**
     * حساب السعة لليوم المحدد بالدقائق
     */
    public function dayCapacity(ProjectSetting $settings, Carbon|string $date): int
    {
        return $this->isWorkingDay($settings, $date) ? $this->dailyCapacityMinutes($settings) : 0;
    }

    /**
     * حساب السعة الإنتاجية المتاحة بين تاريخين (شاملة)
     */
    public function capacityBetween(ProjectSetting $settings, Carbon|string $from, Carbon|string $to): int
    {
        $start = is_string($from) ? Carbon::parse($from) : $from->copy();
        $end = is_string($to) ? Carbon::parse($to) : $to->copy();

        if ($end->lt($start)) {
            return 0;
        }

        $total = 0;
        $period = CarbonPeriod::create($start, $end);
        foreach ($period as $dt) {
            $total += $this->dayCapacity($settings, $dt);
        }

        return $total;
    }

    /**
     * يوم العمل القادم (إذا كان اليوم الحالي يوم عمل يعيده نفسه، وإلا يبحث عن التالي)
     */
    public function nextWorkingDay(ProjectSetting $settings, Carbon|string $date): Carbon
    {
        $d = is_string($date) ? Carbon::parse($date) : $date->copy();
        for ($i = 0; $i < 60; $i++) {
            if ($this->isWorkingDay($settings, $d)) {
                return $d;
            }
            $d->addDay();
        }
        return $d;
    }

    /**
     * إضافة عدد من أيام العمل
     */
    public function addWorkingDays(ProjectSetting $settings, Carbon|string $date, int $days): Carbon
    {
        $d = is_string($date) ? Carbon::parse($date) : $date->copy();
        if ($days === 0) {
            return $this->nextWorkingDay($settings, $d);
        }

        $step = $days > 0 ? 1 : -1;
        $target = abs($days);
        $added = 0;

        while ($added < $target) {
            $d->addDays($step);
            if ($this->isWorkingDay($settings, $d)) {
                $added++;
            }
        }

        return $d;
    }

    /**
     * بناء خطة الجدولة لجميع الطلبات وتوزيع الدقائق على الأيام
     * خوارزمية الجدولة: Earliest Due Date First
     */
    public function build(iterable $orders, ProjectSetting $settings, ?Carbon $today = null): array
    {
        $today = $today ? $today->copy()->startOfDay() : Carbon::today();
        $dailyCap = $this->dailyCapacityMinutes($settings);

        // تصفية الطلبات النشطة (التي تملك عملاً متبقياً وليست مكتملة)
        $active = [];
        foreach ($orders as $o) {
            $rem = (int) ($o['remaining_duration'] ?? $o->remaining_duration ?? 0);
            $status = $o['status'] ?? $o->status ?? 'new';
            if ($status !== 'completed' && $rem > 0) {
                $active[] = [
                    'id' => (string) ($o['id'] ?? $o->id),
                    'order_number' => (string) ($o['order_number'] ?? $o->order_number ?? ''),
                    'customer_name' => (string) ($o['customer_name'] ?? $o->customer_name ?? ''),
                    'due' => is_string($o['due_date'] ?? $o->due_date) ? ($o['due_date'] ?? $o->due_date) : ($o['due_date'] ?? $o->due_date)->toDateString(),
                    'received' => is_string($o['received_date'] ?? $o->received_date) ? ($o['received_date'] ?? $o->received_date) : ($o['received_date'] ?? $o->received_date)->toDateString(),
                    'rem' => $rem,
                    'status' => $status,
                ];
            }
        }

        // ترتيب الطلبات: الأقرب موعداً أولاً
        usort($active, function ($a, $b) {
            if ($a['due'] === $b['due']) {
                return strcmp($a['id'], $b['id']);
            }
            return strcmp($a['due'], $b['due']);
        });

        $byDay = [];       // [ 'YYYY-MM-DD' => [ ['order_id', 'minutes'] ] ]
        $dayLoad = [];     // [ 'YYYY-MM-DD' => total_minutes ]
        $finishDate = [];  // [ order_id => 'YYYY-MM-DD' ]
        $isLate = [];      // [ order_id => bool ]

        $currentDay = $this->nextWorkingDay($settings, $today);
        $currentDateStr = $currentDay->toDateString();

        foreach ($active as $item) {
            $id = $item['id'];
            $dueStr = $item['due'];
            $needed = $item['rem'];

            // يبدأ الجدولة من اليوم الحالي أو تاريخ الاستلام أيهما أحدث
            $searchDay = $today->copy();
            if ($item['received'] > $today->toDateString()) {
                $searchDay = Carbon::parse($item['received']);
            }
            $searchDay = $this->nextWorkingDay($settings, $searchDay);

            $lastWorkDay = null;

            while ($needed > 0) {
                $dayKey = $searchDay->toDateString();
                if ($this->isWorkingDay($settings, $searchDay)) {
                    $used = $dayLoad[$dayKey] ?? 0;
                    $space = max(0, $dailyCap - $used);

                    if ($space > 0) {
                        $take = min($needed, $space);
                        $dayLoad[$dayKey] = $used + $take;
                        $byDay[$dayKey][] = [
                            'order_id' => $id,
                            'minutes' => $take,
                        ];
                        $needed -= $take;
                        $lastWorkDay = $dayKey;
                    }
                }

                if ($needed > 0) {
                    $searchDay->addDay();
                    $searchDay = $this->nextWorkingDay($settings, $searchDay);
                }
            }

            $finishDate[$id] = $lastWorkDay;
            $isLate[$id] = ($lastWorkDay === null || $lastWorkDay > $dueStr);
        }

        return [
            'orders' => $active,
            'by_day' => $byDay,
            'day_load' => $dayLoad,
            'finish_dates' => $finishDate,
            'is_late' => $isLate,
        ];
    }
}
