<x-app-layout>
    <x-slot name="title">التنبؤ بالتأخير وإدارة المخاطر</x-slot>
    <x-slot name="headerTitle">التنبؤ بالتأخير وتحليل المخاطر</x-slot>

    <!-- نظرة عامة وتنبيهات ذكية -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <div class="bg-rose-50 border border-rose-200 p-5 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-rose-600 block mb-1">طلبات عالية الخطورة (حرجة)</span>
                <h3 class="text-2xl font-black text-rose-800">{{ $highRisks->count() }}</h3>
                <span class="text-[11px] text-rose-600">تجاوزت الموعد أو يوجد عجز زمني</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center">
                <i data-lucide="alert-octagon" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-amber-50 border border-amber-200 p-5 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-amber-600 block mb-1">طلبات متوسطة الخطورة</span>
                <h3 class="text-2xl font-black text-amber-800">{{ $mediumRisks->count() }}</h3>
                <span class="text-[11px] text-amber-600">هامش الأمان ضيق أو موعدها قريب</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-emerald-50 border border-emerald-200 p-5 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-emerald-600 block mb-1">طلبات منخفضة الخطورة</span>
                <h3 class="text-2xl font-black text-emerald-800">{{ $lowRisks->count() }}</h3>
                <span class="text-[11px] text-emerald-600">الوقت المتاح يغطي العمل بهامش مريح</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- قائمة تفاصيل الطلبات ذات الخطورة مع الشرح الإجرائي -->
    <div class="space-y-6">
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <h3 class="font-bold text-slate-800 text-base mb-4 pb-3 border-b border-slate-100 flex items-center justify-between">
                <span>تحليل تفصيلي لمخاطر كل طلب</span>
                <span class="text-xs text-slate-400">مبني على خوارزمية السعة المتاحة والطلب التراكمي</span>
            </h3>

            <div class="space-y-4">
                @forelse($risks as $r)
                    @php $order = $r['order']; @endphp
                    @if($order)
                        <div class="p-5 rounded-2xl border {{ $r['level'] === 'high' ? 'border-rose-200 bg-rose-50/30' : ($r['level'] === 'medium' ? 'border-amber-200 bg-amber-50/20' : 'border-slate-200 bg-white') }} transition-all">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full {{ $r['level'] === 'high' ? 'bg-rose-600' : ($r['level'] === 'medium' ? 'bg-amber-500' : 'bg-emerald-500') }}"></div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-sm">
                                            {{ $order->customer_name }}
                                            <span class="text-xs text-slate-400 font-mono">({{ $order->order_number }})</span>
                                        </h4>
                                        <span class="text-xs text-slate-500">{{ $order->product_name }} &bull; موعد التسليم: <strong class="font-mono {{ $order->due_date->isPast() ? 'text-rose-600' : '' }}">{{ $order->due_date->format('Y-m-d') }}</strong></span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-bold px-3 py-1 rounded-full {{ $r['level'] === 'high' ? 'bg-rose-100 text-rose-800' : ($r['level'] === 'medium' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                        خطر {{ $r['level'] === 'high' ? 'مرتفع' : ($r['level'] === 'medium' ? 'متوسط' : 'منخفض') }}
                                    </span>
                                    <a href="{{ route('orders.show', $order->id) }}" class="text-xs font-bold text-brand-600 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg transition-colors">
                                        عرض الطلب
                                    </a>
                                </div>
                            </div>

                            <!-- الإحصائيات الفنية للطلب -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3 bg-white/70 rounded-xl border border-slate-100 text-xs mb-3">
                                <div>
                                    <span class="text-slate-400 block text-[10px]">العمل المتبقي</span>
                                    <span class="font-bold text-slate-700">{{ $r['remaining_minutes'] }} دقيقة</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px]">السعة المتاحة للموعد</span>
                                    <span class="font-bold text-slate-700">{{ $r['available_capacity'] }} دقيقة</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px]">الطلب التراكمي</span>
                                    <span class="font-bold text-slate-700">{{ $r['cumulative_demand'] }} دقيقة</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px]">هامش الأمان</span>
                                    <span class="font-bold {{ $r['slack_minutes'] < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $r['slack_minutes'] }} دقيقة</span>
                                </div>
                            </div>

                            <!-- أسباب التوقع والتوصيات -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                                <div>
                                    <span class="font-bold text-slate-700 block mb-1 text-[11px]">أسباب تقدير الخطر:</span>
                                    <ul class="space-y-1 text-slate-600 list-disc list-inside">
                                        @foreach($r['reasons'] as $reason)
                                            <li>{{ $reason }}</li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div>
                                    <span class="font-bold text-slate-700 block mb-1 text-[11px]">التوصيات والإجراءات المقترحة:</span>
                                    <ul class="space-y-1 text-slate-600">
                                        @foreach($r['actions'] as $act)
                                            <li class="flex items-start gap-1.5">
                                                <i data-lucide="check" class="w-3.5 h-3.5 text-brand-600 shrink-0 mt-0.5"></i>
                                                <span>{{ $act }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        لا توجد طلبات نشطة في الوقت الحالي.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
