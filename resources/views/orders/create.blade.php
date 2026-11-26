<x-app-layout>
    <x-slot name="title">إضافة طلب جديد</x-slot>
    <x-slot name="headerTitle">إضافة طلب جديد</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- أداة الاستخراج الذكي من المحادثة (AI Chat Extractor) -->
        <div class="bg-gradient-to-r from-brand-50 to-indigo-50 p-5 rounded-2xl border border-brand-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2 text-brand-800 font-bold text-sm">
                    <i data-lucide="sparkles" class="w-4 h-4 text-amber-500"></i>
                    <span>استخراج بيانات الطلب من المحادثة تلقائياً (ذكاء اصطناعي)</span>
                </div>
                <span class="text-[11px] bg-brand-200/60 text-brand-800 px-2 py-0.5 rounded-full font-bold">ميزة ذكية</span>
            </div>
            <p class="text-xs text-slate-600 mb-3">انسخ نص محادثة العميل (من واتساب أو إنستغرام) والصقه هنا، وسيقوم النظام باستخراج اسم العميل، والمنتج، والكمية، وموعد التسليم وتعبئة النموذج آلياً!</p>

            <div class="space-y-3">
                <textarea id="aiConversationText" rows="3" placeholder="مثال: السلام عليكم، معك سارة القحطاني.. أبغى بوكسين هدايا ورود صغيرة وشوكولاتة لحفل تخرج بعد 3 أيام لو سمحت..."
                          class="w-full p-3 text-xs bg-white border border-brand-200 rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500"></textarea>

                <div class="flex items-center justify-between">
                    <button type="button" onclick="extractFromText()" id="btnExtract" class="bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-sm transition-all flex items-center gap-2">
                        <i data-lucide="wand-2" class="w-3.5 h-3.5"></i>
                        <span>استخراج وتعبئة الحقول</span>
                    </button>
                    <span id="extractStatus" class="text-xs font-medium text-slate-500 hidden"></span>
                </div>
            </div>
        </div>

        <!-- النموذج الرئيسي -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <h3 class="font-bold text-slate-800 text-base mb-5 pb-3 border-b border-slate-100 flex items-center justify-between">
                <span>بيانات وتفاصيل الطلب</span>
                <span class="text-xs text-slate-400 font-mono">الرقم المقترح: {{ $nextOrderNumber }}</span>
            </h3>

            <form method="POST" action="{{ route('orders.store') }}" class="space-y-5" id="orderForm">
                @csrf

                <!-- بيانات العميل -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">اسم العميل <span class="text-rose-500">*</span></label>
                        <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}" required
                               placeholder="مثال: نورة العتيبي"
                               class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">رقم هاتف العميل (اختياري)</label>
                        <input type="text" name="customer_phone" id="customer_phone" value="{{ old('customer_phone') }}"
                               placeholder="05XXXXXXXX"
                               class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>
                </div>

                <!-- تفاصيل المنتج والكمية -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">نوع المنتج <span class="text-rose-500">*</span></label>
                        <select name="product_name" id="product_name" onchange="handleProductChange()" required
                                class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-white focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                            <option value="">-- اختر نوع المنتج --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->product_name }}" data-duration="{{ $p->estimated_unit_duration }}" {{ old('product_name') === $p->product_name ? 'selected' : '' }}>
                                    {{ $p->product_name }} (تقديري: {{ $p->estimated_unit_duration }} دقيقة/قطعة)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">الكمية المطلوبة <span class="text-rose-500">*</span></label>
                        <input type="number" name="quantity" id="quantity" min="1" value="{{ old('quantity', 1) }}" oninput="calculateTotalMinutes()" required
                               class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>
                </div>

                <!-- التواريخ: الاستلام والتسليم -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">تاريخ الاستلام <span class="text-rose-500">*</span></label>
                        <input type="date" name="received_date" id="received_date" value="{{ old('received_date', now()->toDateString()) }}" required
                               class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">موعد التسليم المتفق عليه <span class="text-rose-500">*</span></label>
                        <input type="date" name="due_date" id="due_date" value="{{ old('due_date', $defaultDue) }}" required
                               class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>
                </div>

                <!-- احتساب الوقت ومدة التنفيذ -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-800">طريقة تحديد مدة التنفيذ</label>
                        <div class="flex items-center gap-4 text-xs">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="duration_mode" value="auto" checked onchange="toggleDurationMode()" class="text-brand-600 focus:ring-brand-500">
                                <span>تلقائي (حسب معايير المنتج)</span>
                            </label>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="duration_mode" value="manual" onchange="toggleDurationMode()" class="text-brand-600 focus:ring-brand-500">
                                <span>يدوي مخصص</span>
                            </label>
                        </div>
                    </div>

                    <div id="manualDurationBox" class="hidden grid grid-cols-2 gap-4 pt-2 border-t border-slate-200">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">المدة التقديرية للقطعة (بالدقائق)</label>
                            <input type="number" name="unit_duration" id="unit_duration" min="5" value="60" oninput="calculateTotalMinutes()"
                                   class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">إجمالي المدة المقدرة (بالدقائق)</label>
                            <input type="number" name="estimated_duration" id="estimated_duration" min="5" value="60"
                                   class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg">
                        </div>
                    </div>

                    <div class="text-xs font-bold text-brand-700 flex items-center justify-between pt-1">
                        <span>إجمالي العمل المتوقع لهذا الطلب:</span>
                        <span id="calculatedMinutesDisplay" class="bg-brand-100 text-brand-900 px-3 py-1 rounded-lg">-- دقيقة</span>
                    </div>
                </div>

                <!-- الوصف والملاحظات -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">وصف ومواصفات الطلب (الألوان، التغليف، المناسبة)</label>
                    <textarea name="description" id="description" rows="2" placeholder="مثال: باقتان بتدرجات اللون البنفسجي، تغليف أبيض لحفل تخرج..."
                              class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">ملاحظات داخلية للورشة</label>
                    <textarea name="notes" id="notes" rows="2" placeholder="ملاحظات متابعة أو تفاصيل تجهيز الخامات..."
                              class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">{{ old('notes') }}</textarea>
                </div>

                <!-- أزرار الحفظ -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('orders.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                        إلغاء
                    </a>
                    <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>حفظ وإضافة الطلب</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function handleProductChange() {
            calculateTotalMinutes();
        }

        function toggleDurationMode() {
            const isManual = document.querySelector('input[name="duration_mode"]:checked').value === 'manual';
            document.getElementById('manualDurationBox').classList.toggle('hidden', !isManual);
            calculateTotalMinutes();
        }

        function calculateTotalMinutes() {
            const isManual = document.querySelector('input[name="duration_mode"]:checked').value === 'manual';
            const qty = parseInt(document.getElementById('quantity').value) || 1;
            let unitMin = 60;

            if (isManual) {
                unitMin = parseInt(document.getElementById('unit_duration').value) || 60;
                const total = unitMin * qty;
                document.getElementById('estimated_duration').value = total;
                document.getElementById('calculatedMinutesDisplay').textContent = total + ' دقيقة (' + (Math.round(total / 60 * 10) / 10) + ' ساعة)';
            } else {
                const sel = document.getElementById('product_name');
                const opt = sel.options[sel.selectedIndex];
                if (opt && opt.dataset.duration) {
                    unitMin = parseInt(opt.dataset.duration);
                }
                const total = unitMin * qty;
                document.getElementById('calculatedMinutesDisplay').textContent = total + ' دقيقة (' + (Math.round(total / 60 * 10) / 10) + ' ساعة)';
            }
        }

        async function extractFromText() {
            const text = document.getElementById('aiConversationText').value.trim();
            if (!text) {
                alert('يرجى لصق نص المحادثة أولاً');
                return;
            }

            const btn = document.getElementById('btnExtract');
            const status = document.getElementById('extractStatus');
            btn.disabled = true;
            status.classList.remove('hidden');
            status.innerHTML = '<span class="text-brand-600">جاري الاستخراج بالذكاء الاصطناعي...</span>';

            try {
                const res = await fetch("{{ route('ai.extract') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ text: text })
                });

                const data = await res.json();
                if (data.success && data.fields) {
                    const f = data.fields;
                    if (f.customer_name) document.getElementById('customer_name').value = f.customer_name;
                    if (f.quantity) document.getElementById('quantity').value = f.quantity;
                    if (f.due_date) document.getElementById('due_date').value = f.due_date;
                    if (f.description) document.getElementById('description').value = f.description;

                    if (f.product_name) {
                        const sel = document.getElementById('product_name');
                        for (let i = 0; i < sel.options.length; i++) {
                            if (sel.options[i].value === f.product_name) {
                                sel.selectedIndex = i;
                                break;
                            }
                        }
                    }

                    calculateTotalMinutes();
                    status.innerHTML = '<span class="text-emerald-600 font-bold">تم استخراج البيانات بنجاح!</span>';
                } else {
                    status.innerHTML = '<span class="text-rose-600">تعذر استخراج الحقول بدقة. يرجى التعبئة يدوياً.</span>';
                }
            } catch (err) {
                status.innerHTML = '<span class="text-rose-600">حدث خطأ أثناء معالجة النص.</span>';
            } finally {
                btn.disabled = false;
            }
        }

        document.addEventListener('DOMContentLoaded', calculateTotalMinutes);
    </script>
    @endpush
</x-app-layout>
