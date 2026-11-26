<x-app-layout>
    <x-slot name="title">جدول العمل</x-slot>
    <x-slot name="headerTitle">جدول العمل وتوزيع السعة اليومية</x-slot>

    <!-- نظرة عامة على جدول العمل -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-800">توزيع ساعات العمل والمهام على الأيام</h3>
                <p class="text-xs text-slate-400">القدرة اليومية المعتمدة: {{ round($settings->daily_productive_hours, 1) }} ساعات إنتاجية ({{ round($settings->daily_productive_hours * 60) }} دقيقة)</p>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> متاح</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> مزدحم (80%+)</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span> ممتلئ بالكامل</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span> عطلة أسبوعية</span>
            </div>
        </div>

        <!-- شبكة الأيام (21 يوم قادم) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-3 mt-6">
            @foreach($days as $d)
                @php
                    $items = $plan['by_day'][$d['date']] ?? [];
                @endphp
                <div class="p-3.5 rounded-xl border {{ $d['is_working'] ? ($d['load_percentage'] >= 100 ? 'bg-rose-50/50 border-rose-200' : ($d['load_percentage'] >= 80 ? 'bg-amber-50/50 border-amber-200' : 'bg-white border-slate-200')) : 'bg-slate-50 border-slate-100 opacity-60' }} flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-bold text-xs text-slate-800">{{ $d['day_name'] }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ substr($d['date'], 5) }}</span>
                        </div>

                        @if($d['is_working'])
                            <div class="space-y-1 mb-2">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-slate-500">المحجوز:</span>
                                    <span class="font-bold {{ $d['load_percentage'] >= 100 ? 'text-rose-600' : 'text-slate-800' }}">
                                        {{ $d['load'] }} / {{ $d['capacity'] }} د
                                    </span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $d['load_percentage'] >= 100 ? 'bg-rose-600' : ($d['load_percentage'] >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, $d['load_percentage']) }}%"></div>
                                </div>
                            </div>
                        @else
                            <span class="text-[11px] text-slate-400 font-medium block my-2">عطلة</span>
                        @endif
                    </div>

                    <!-- الطلبات المجدولة في هذا اليوم -->
                    @if(count($items) > 0)
                        <div class="pt-2 border-t border-slate-100 space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 block">الطلبات المجدولة:</span>
                            @foreach($items as $it)
                                @php $ord = $orderMap[$it['order_id']] ?? null; @endphp
                                @if($ord)
                                    <div class="text-[10px] bg-white p-1 rounded border border-slate-200/60 truncate" title="{{ $ord->customer_name }}">
                                        <span class="font-bold text-brand-700">{{ $ord->order_number }}</span>
                                        <span class="text-slate-600">({{ $it['minutes'] }} د)</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
