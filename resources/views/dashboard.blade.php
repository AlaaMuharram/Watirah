<x-app-layout>
    <x-slot name="title">لوحة التحكم</x-slot>
    <x-slot name="headerTitle">لوحة التحكم والمؤشرات الذكية</x-slot>

    <!-- ملخص البطاقات الإحصائية (KPI Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- الطلبات النشطة -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 mb-1">الطلبات النشطة</p>
                <h3 class="text-2xl font-black text-slate-800">{{ $totalActive }}</h3>
                <span class="text-[11px] text-slate-500 font-medium">منها {{ $criticalOrders->count() }} تتطلب متابعة</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                <i data-lucide="package" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- ساعات العمل المتبقية -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 mb-1">العمل المتبقي</p>
                <h3 class="text-2xl font-black text-slate-800">{{ round($totalRemainingMinutes / 60, 1) }} <span class="text-sm font-bold text-slate-500">ساعة</span></h3>
                <span class="text-[11px] text-slate-500 font-medium">إجمالي {{ $totalRemainingMinutes }} دقيقة</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- الطلبات المتأخرة والحرجة -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 mb-1">خطر التأخير / متأخرة</p>
                <h3 class="text-2xl font-black text-rose-600">{{ $highRiskCount }} <span class="text-xs font-bold text-slate-400">/ {{ $totalLate }} متأخر</span></h3>
                <span class="text-[11px] text-rose-500 font-medium font-bold">تتطلب تدخلاً عاجلاً</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <i data-lucide="alert-octagon" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- مستوى ضغط العمل -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 mb-1">نسبة ضغط العمل (14 يوم)</p>
                <h3 class="text-2xl font-black {{ $workloadRatio >= 85 ? 'text-rose-600' : ($workloadRatio >= 65 ? 'text-amber-600' : 'text-emerald-600') }}">
                    {{ $workloadRatio }}%
                </h3>
                <span class="text-[11px] font-medium text-slate-500">
                    {{ $workloadRatio >= 85 ? 'ضغط مرتفع جداً' : ($workloadRatio >= 65 ? 'ضغط متوسط ومقبول' : 'سعة مريحة ومتاحة') }}
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl {{ $workloadRatio >= 85 ? 'bg-rose-50 text-rose-600' : ($workloadRatio >= 65 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600') }} flex items-center justify-center">
                <i data-lucide="gauge" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- الرسوم البيانية ومحاكاة الضغط -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- الرسم البياني: عبء العمل وتوزيع الدقائق -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">توزيع ضغط العمل على الأيام القادمة</h3>
                    <p class="text-xs text-slate-400 font-medium">مقارنة ساعات العمل المحجوزة بالقدرة الإنتاجية اليومية ({{ round($settings->daily_productive_hours, 1) }} س/يوم)</p>
                </div>
                <span class="text-xs font-bold bg-slate-100 text-slate-600 px-3 py-1 rounded-full">14 يوم عمل</span>
            </div>

            <div class="h-64">
                <canvas id="workloadChart"></canvas>
            </div>
        </div>

        <!-- بطاقة المحاكاة الذكية السريعة -->
        <div class="bg-gradient-to-br from-indigo-900 to-brand-900 text-white p-6 rounded-2xl shadow-md flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-white/5 rounded-full blur-2xl"></div>

            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="bg-white/10 text-amber-300 px-2.5 py-1 rounded-lg text-xs font-bold flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        <span>محاكاة ضغط العمل</span>
                    </span>
                </div>
                <h3 class="text-xl font-black mb-2">هل تفكر في قبول طلب جديد؟</h3>
                <p class="text-xs text-indigo-200 leading-relaxed mb-6">
                    لا تقبل أي طلب دون تجربة تأثيره! استخدم محاكي وَتيرة لمعرفة ما إذا كان الطلب سيتسبب في تأخير طلبات أخرى واكتشف الموعد الآمن للتسليم.
                </p>
            </div>

            <div class="space-y-3">
                <a href="{{ route('simulator.index') }}" class="w-full bg-white text-indigo-950 hover:bg-slate-100 font-bold py-3 px-4 rounded-xl text-center text-sm shadow-sm transition-all flex items-center justify-center gap-2">
                    <i data-lucide="play" class="w-4 h-4 fill-current"></i>
                    <span>فتح محاكي ضغط العمل</span>
                </a>
                <button onclick="openAiChatModal()" class="w-full bg-white/10 hover:bg-white/20 text-white font-bold py-2.5 px-4 rounded-xl text-center text-xs transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                    <span>اسأل مساعد وَتيرة عن توفر موعد</span>
                </button>
            </div>
        </div>
    </div>

    <!-- جدول الطلبات الحرجة والتنبيهات الذكية -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- الطلبات الأكثر عرضة للتأخير والحرجة -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="shield-alert" class="w-5 h-5 text-rose-600"></i>
                    <h3 class="font-bold text-slate-800 text-base">الطلبات الأكثر حرجاً وعرضة للتأخير</h3>
                </div>
                <a href="{{ route('orders.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">عرض جميع الطلبات &larr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                        <tr>
                            <th class="p-3.5">رقم الطلب والعميل</th>
                            <th class="p-3.5">المنتج</th>
                            <th class="p-3.5">موعد التسليم</th>
                            <th class="p-3.5">المتبقي</th>
                            <th class="p-3.5">مستوى الخطر</th>
                            <th class="p-3.5 text-center">إجراء</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($criticalOrders as $order)
                            @php $risk = $risks[$order->id] ?? null; @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="p-3.5">
                                    <div class="font-bold text-slate-800">{{ $order->customer_name }}</div>
                                    <span class="text-[11px] text-slate-400 font-mono">{{ $order->order_number }}</span>
                                </td>
                                <td class="p-3.5 text-slate-700 font-medium">{{ $order->product_name }} ({{ $order->quantity }})</td>
                                <td class="p-3.5">
                                    <div class="font-bold {{ $order->due_date->isPast() ? 'text-rose-600' : 'text-slate-700' }}">
                                        {{ $order->due_date->format('Y-m-d') }}
                                    </div>
                                    <span class="text-[10px] text-slate-400">{{ $order->due_date->diffForHumans() }}</span>
                                </td>
                                <td class="p-3.5 font-bold text-slate-700">{{ $order->remaining_duration }} دقيقة</td>
                                <td class="p-3.5">
                                    @if(($risk['level'] ?? '') === 'high' || $order->status === 'late')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                            مرتفع
                                        </span>
                                    @elseif(($risk['level'] ?? '') === 'medium')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            متوسط
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            منخفض
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-center">
                                    <a href="{{ route('orders.show', $order->id) }}" class="text-brand-600 hover:text-brand-800 font-bold text-[11px] bg-brand-50 hover:bg-brand-100 px-2.5 py-1 rounded-lg transition-colors">
                                        التفاصيل
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-slate-400">
                                    لا توجد طلبات نشطة حالياً.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- التنبيهات والتوصيات الذكية -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="bell-ring" class="w-5 h-5 text-amber-500"></i>
                        <h3 class="font-bold text-slate-800 text-base">التنبيهات الذكية</h3>
                    </div>
                    <span class="text-xs bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full font-bold">
                        {{ $alerts->where('is_read', false)->count() }} جديد
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($alerts as $alert)
                        <div class="p-3.5 rounded-xl border {{ $alert->risk_level === 'high' ? 'bg-rose-50/50 border-rose-100' : 'bg-slate-50 border-slate-100' }} text-xs">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold {{ $alert->risk_level === 'high' ? 'text-rose-700' : 'text-slate-800' }}">
                                    {{ $alert->alert_type === 'overdue' ? 'تجاوز الموعد' : ($alert->alert_type === 'heavy_load' ? 'عبء إنتاجي مكثف' : 'ضغط في الجدول') }}
                                </span>
                                <span class="text-[10px] text-slate-400">{{ $alert->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-slate-600 leading-relaxed mb-2">{{ $alert->message }}</p>
                            @if($alert->action)
                                <div class="bg-white p-2 rounded-lg text-[11px] text-slate-700 border border-slate-200/60 font-medium flex items-start gap-1.5">
                                    <i data-lucide="lightbulb" class="w-3.5 h-3.5 text-amber-500 shrink-0 mt-0.5"></i>
                                    <span>{{ $alert->action }}</span>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-xs">
                            لا توجد تنبيهات جديدة في الوقت الحالي.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 text-center">
                <a href="{{ route('risks.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 inline-flex items-center gap-1">
                    <span>مراجعة صفحة التنبؤ بالتأخير والمخاطر</span>
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const daysData = @json($upcomingDays);

            const labels = daysData.map(d => d.day_name + ' ' + d.date.slice(5));
            const loads = daysData.map(d => Math.round(d.load / 60 * 10) / 10);
            const capacities = daysData.map(d => Math.round(d.capacity / 60 * 10) / 10);

            const ctx = document.getElementById('workloadChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'ساعات العمل المحجوزة',
                            data: loads,
                            backgroundColor: daysData.map(d => d.load_percentage >= 100 ? '#e11d48' : (d.load_percentage >= 80 ? '#f59e0b' : '#8b5cf6')),
                            borderRadius: 6,
                        },
                        {
                            label: 'القدرة اليومية المتاحة',
                            data: capacities,
                            type: 'line',
                            borderColor: '#94a3b8',
                            borderDash: [4, 4],
                            pointRadius: 0,
                            fill: false,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            rtl: true,
                            labels: {
                                font: { family: 'Cairo', size: 11, weight: 'bold' }
                            }
                        },
                        tooltip: {
                            rtl: true,
                            titleFont: { family: 'Cairo' },
                            bodyFont: { family: 'Cairo' }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'ساعات',
                                font: { family: 'Cairo', size: 10 }
                            }
                        },
                        x: {
                            ticks: {
                                font: { family: 'Cairo', size: 9 }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
