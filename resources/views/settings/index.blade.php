<x-app-layout>
    <x-slot name="title">إعدادات الورشة</x-slot>
    <x-slot name="headerTitle">إعدادات الورشة وأنواع المنتجات</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- إعدادات المشروع وساعات العمل -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <h3 class="font-bold text-slate-800 text-base mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="sliders" class="w-5 h-5 text-brand-600"></i>
                <span>أوقات العمل والطاقة الإنتاجية</span>
            </h3>

            <form method="POST" action="{{ route('settings.update') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">اسم المشروع / الورشة</label>
                    <input type="text" name="project_name" value="{{ old('project_name', $settings->project_name) }}" required
                           class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">أيام العمل الأسبوعية</label>
                    <div class="grid grid-cols-4 gap-2 text-xs">
                        @php
                            $daysMap = [
                                0 => 'الأحد',
                                1 => 'الإثنين',
                                2 => 'الثلاثاء',
                                3 => 'الأربعاء',
                                4 => 'الخميس',
                                5 => 'الجمعة',
                                6 => 'السبت',
                            ];
                            $activeDays = $settings->working_days ?? [0, 1, 2, 3, 4];
                        @endphp
                        @foreach($daysMap as $num => $name)
                            <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200/80 cursor-pointer hover:bg-slate-50 transition-colors">
                                <input type="checkbox" name="working_days[]" value="{{ $num }}" {{ in_array($num, $activeDays) ? 'checked' : '' }}
                                       class="rounded text-brand-600 focus:ring-brand-500">
                                <span class="font-bold text-slate-700">{{ $name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">بداية الدوام</label>
                        <input type="time" name="start_time" value="{{ old('start_time', $settings->start_time) }}" required
                               class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">نهاية الدوام</label>
                        <input type="time" name="end_time" value="{{ old('end_time', $settings->end_time) }}" required
                               class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">ساعات العمل الإنتاجية الفعلية</label>
                        <input type="number" step="0.5" name="daily_productive_hours" value="{{ old('daily_productive_hours', $settings->daily_productive_hours) }}" required
                               class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                        <span class="text-[10px] text-slate-400 block mt-1">الوقت المخصص للعمل اليدوي الفعلي</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">هامش الأمان المقترح (أيام عمل)</label>
                        <input type="number" name="buffer_days" value="{{ old('buffer_days', $settings->buffer_days) }}" required
                               class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                        <span class="text-[10px] text-slate-400 block mt-1">يُضاف عند اقتراح موعد تسليم آمن</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white px-5 py-2 rounded-xl text-xs font-bold shadow-sm transition-colors">
                        حفظ الإعدادات
                    </button>
                </div>
            </form>
        </div>

        <!-- إدارة أنواع المنتجات والمدد القياسية -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-base mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="tag" class="w-5 h-5 text-brand-600"></i>
                    <span>أنواع المنتجات والأوقات القياسية</span>
                </h3>

                <!-- قائمة المنتجات الحالية -->
                <div class="space-y-2 mb-6">
                    @forelse($products as $p)
                        <div class="p-3 rounded-xl border border-slate-100 bg-slate-50 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-slate-800">{{ $p->product_name }}</span>
                                <span class="text-slate-400 block text-[11px]">{{ $p->notes ?: 'بدون وصف' }}</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="font-mono font-bold bg-white px-2.5 py-1 rounded-lg border border-slate-200 text-brand-700">
                                    {{ $p->estimated_unit_duration }} دقيقة/قطعة
                                </span>

                                <form method="POST" action="{{ route('settings.products.destroy', $p->id) }}" onsubmit="return confirm('حذف نوع المنتج؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-rose-600 p-1">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">لم يتم تعريف أنواع منتجات بعد.</p>
                    @endforelse
                </div>

                <!-- نموذج إضافة نوع منتج جديد -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80">
                    <h4 class="text-xs font-bold text-slate-800 mb-3">إضافة نوع منتج جديد</h4>
                    <form method="POST" action="{{ route('settings.products.store') }}" class="space-y-3">
                        @csrf
                        <div>
                            <input type="text" name="product_name" required placeholder="اسم المنتج (مثال: سلة هدايا)"
                                   class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-brand-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <input type="number" name="estimated_unit_duration" min="5" value="60" required placeholder="المدة المقدرة (دقيقة)"
                                       class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-brand-500">
                            </div>
                            <div>
                                <input type="text" name="notes" placeholder="ملاحظات توضيحية"
                                       class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-brand-500">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold px-4 py-1.5 rounded-lg shadow-sm transition-colors">
                                إضافة المنتج
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
