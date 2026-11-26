<x-app-layout>
    <x-slot name="title">محاكاة ضغط العمل</x-slot>
    <x-slot name="headerTitle">محاكاة ضغط العمل والقرارات الذكية</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- لوحة التحكم في المحاكاة (Simulation Input Form) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm h-fit">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
                <i data-lucide="play-circle" class="w-5 h-5 text-brand-600"></i>
                <h3 class="font-bold text-slate-800 text-base">بيانات الطلب الافتراضي</h3>
            </div>
            <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                اختبر إضافة طلب جديد افتراضياً لمعرفة أثره على جدول ورشتك الحالية قبل أن تقطعه بوعد مع العميل.
            </p>

            <form method="GET" action="{{ route('simulator.index') }}" class="space-y-4">
                <input type="hidden" name="run" value="1">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">المنتج المتوقع</label>
                    <select name="product_name" id="sim_product" onchange="updateSimMinutes()" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white focus:outline-none focus:border-brand-500">
                        @foreach($products as $p)
                            <option value="{{ $p->product_name }}" data-min="{{ $p->estimated_unit_duration }}" {{ $candidate['product_name'] === $p->product_name ? 'selected' : '' }}>
                                {{ $p->product_name }} ({{ $p->estimated_unit_duration }} د/قطعة)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">الكمية</label>
                        <input type="number" name="quantity" id="sim_qty" min="1" value="{{ $candidate['quantity'] }}" oninput="updateSimMinutes()"
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">المدة الإجمالية (د)</label>
                        <input type="number" name="minutes" id="sim_min" min="10" value="{{ $candidate['minutes'] }}"
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">موعد التسليم المطلوب</label>
                    <input type="date" name="due_date" value="{{ $candidate['due_date'] }}" required
                           class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                </div>

                <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs shadow-sm transition-all flex items-center justify-center gap-2">
                    <i data-lucide="cpu" class="w-4 h-4"></i>
                    <span>تشغيل المحاكاة الآن</span>
                </button>
            </form>
        </div>

        <!-- نتائج وتحليل المحاكاة (Simulation Results) -->
        <div class="lg:col-span-2 space-y-6">
            @if($result)
                <!-- كرت النتيجة الرئيسية والقرار الذكي -->
                <div class="p-6 rounded-2xl border shadow-sm {{ $result['decision'] === 'yes' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : ($result['decision'] === 'caution' ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-rose-50 border-rose-200 text-rose-900') }}">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <i data-lucide="{{ $result['decision'] === 'yes' ? 'check-circle-2' : ($result['decision'] === 'caution' ? 'alert-triangle' : 'x-circle') }}" class="w-6 h-6"></i>
                            <h3 class="text-lg font-black">{{ $result['decision_label'] }}</h3>
                        </div>
                        <span class="text-xs font-bold px-3 py-1 rounded-full {{ $result['decision'] === 'yes' ? 'bg-emerald-200/80 text-emerald-900' : ($result['decision'] === 'caution' ? 'bg-amber-200/80 text-amber-900' : 'bg-rose-200/80 text-rose-900') }}">
                            توصية النظام
                        </span>
                    </div>

                    <p class="text-xs font-medium leading-relaxed mb-4">
                        {{ $result['summary'] }}
                    </p>

                    @if($result['safe_suggestion'])
                        <div class="bg-white/80 backdrop-blur-xs p-3.5 rounded-xl border border-white/60 text-xs">
                            <span class="font-bold block mb-1">الموعد الآمن الموصى به:</span>
                            <div class="flex items-center gap-4 text-[11px]">
                                <div>أقرب موعد ممكن: <strong class="font-mono">{{ $result['safe_suggestion']['earliest_date'] }}</strong></div>
                                <div>الموعد الآمن مع هامش الأمان: <strong class="font-mono text-brand-700 font-bold">{{ $result['safe_suggestion']['recommended_date'] }}</strong></div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- مؤشرات المقارنة قبل وبعد -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs">
                        <span class="text-[11px] text-slate-400 block mb-1">استغلال القدرة (قبل)</span>
                        <span class="text-xl font-black text-slate-700">{{ $result['utilization_before'] }}%</span>
                    </div>

                    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs">
                        <span class="text-[11px] text-slate-400 block mb-1">استغلال القدرة (بعد)</span>
                        <span class="text-xl font-black {{ $result['utilization_after'] >= 85 ? 'text-rose-600' : 'text-slate-800' }}">
                            {{ $result['utilization_after'] }}%
                        </span>
                    </div>

                    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs">
                        <span class="text-[11px] text-slate-400 block mb-1">العمل الإجمالي (قبل)</span>
                        <span class="text-xl font-black text-slate-700">{{ $result['total_minutes_before'] }} د</span>
                    </div>

                    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs">
                        <span class="text-[11px] text-slate-400 block mb-1">العمل الإجمالي (بعد)</span>
                        <span class="text-xl font-black text-brand-600">{{ $result['total_minutes_after'] }} د</span>
                    </div>
                </div>

                <!-- الطلبات الحالية المتأثرة -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                    <h3 class="font-bold text-slate-800 text-sm mb-4 pb-2 border-b border-slate-100 flex items-center justify-between">
                        <span>الطلبات التي ستتأثر بقبول هذا الطلب</span>
                        <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-bold">
                            {{ count($result['affected_orders']) }} طلب متأثر
                        </span>
                    </h3>

                    @if(count($result['affected_orders']) > 0)
                        <div class="space-y-3">
                            @foreach($result['affected_orders'] as $aff)
                                <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50 flex items-center justify-between text-xs">
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $aff['customer_name'] }} ({{ $aff['order_number'] }})</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            موعد الانتهاء سينزاح من <span class="font-mono">{{ $aff['finish_before'] }}</span> إلى <span class="font-mono font-bold text-rose-600">{{ $aff['finish_after'] }}</span>
                                            ({{ $aff['shift_days'] }} يوم تأخير)
                                        </div>
                                    </div>

                                    @if($aff['newly_late'])
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 animate-pulse">
                                            سيتأخر عن موعده!
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            تضييق هامش الأمان
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center text-emerald-600 text-xs font-bold">
                            رائع! لا يوجد أي طلب حالي سيتأخر أو ينزاح موعده في حال قبول هذا الطلب.
                        </div>
                    @endif
                </div>

            @else
                <div class="bg-white p-12 rounded-2xl border border-slate-200/80 shadow-sm text-center">
                    <div class="w-16 h-16 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="cpu" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 mb-2">محاكي ضغط العمل جاهز</h3>
                    <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                        حدد بيانات الطلب الذي ترغب في اختباره من النموذج واضغط على "تشغيل المحاكاة" لاختبار تأثيره الفعلي على مواعيد طلباتك الحالية.
                    </p>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function updateSimMinutes() {
            const sel = document.getElementById('sim_product');
            const qty = parseInt(document.getElementById('sim_qty').value) || 1;
            const opt = sel.options[sel.selectedIndex];
            const unit = opt ? parseInt(opt.dataset.min || 60) : 60;
            document.getElementById('sim_min').value = unit * qty;
        }
    </script>
    @endpush
</x-app-layout>
