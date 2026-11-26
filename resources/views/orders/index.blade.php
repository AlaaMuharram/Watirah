<x-app-layout>
    <x-slot name="title">إدارة الطلبات</x-slot>
    <x-slot name="headerTitle">سجل وإدارة الطلبات</x-slot>

    <!-- رأس الصفحة مع الفلاتر والأزرار -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <!-- الفلاتر السريعة للحالات -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 text-xs font-bold">
                <a href="{{ route('orders.index', ['status' => 'all', 'risk' => $currentRisk, 'search' => $search]) }}"
                   class="px-3 py-1.5 rounded-xl transition-colors {{ $currentStatus === 'all' ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    الكل ({{ $counts['all'] }})
                </a>
                <a href="{{ route('orders.index', ['status' => 'new', 'risk' => $currentRisk, 'search' => $search]) }}"
                   class="px-3 py-1.5 rounded-xl transition-colors {{ $currentStatus === 'new' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    جديد ({{ $counts['new'] }})
                </a>
                <a href="{{ route('orders.index', ['status' => 'in_progress', 'risk' => $currentRisk, 'search' => $search]) }}"
                   class="px-3 py-1.5 rounded-xl transition-colors {{ $currentStatus === 'in_progress' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    قيد التنفيذ ({{ $counts['in_progress'] }})
                </a>
                <a href="{{ route('orders.index', ['status' => 'waiting', 'risk' => $currentRisk, 'search' => $search]) }}"
                   class="px-3 py-1.5 rounded-xl transition-colors {{ $currentStatus === 'waiting' ? 'bg-purple-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    بالانتظار ({{ $counts['waiting'] }})
                </a>
                <a href="{{ route('orders.index', ['status' => 'late', 'risk' => $currentRisk, 'search' => $search]) }}"
                   class="px-3 py-1.5 rounded-xl transition-colors {{ $currentStatus === 'late' ? 'bg-rose-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    متأخر ({{ $counts['late'] }})
                </a>
                <a href="{{ route('orders.index', ['status' => 'completed', 'risk' => $currentRisk, 'search' => $search]) }}"
                   class="px-3 py-1.5 rounded-xl transition-colors {{ $currentStatus === 'completed' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    مكتمل ({{ $counts['completed'] }})
                </a>
            </div>

            <!-- زر إضافة طلب -->
            <a href="{{ route('orders.create') }}" class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-sm transition-all shrink-0">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>طلب جديد</span>
            </a>
        </div>

        <!-- شريط البحث وفلتر المخاطر -->
        <form method="GET" action="{{ route('orders.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <input type="hidden" name="status" value="{{ $currentStatus }}">

            <div class="relative flex-1 w-full">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute right-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="ابحث برقم الطلب، اسم العميل، أو المنتج..."
                       class="w-full pr-10 pl-4 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="risk" onchange="this.form.submit()" class="text-xs border border-slate-200 rounded-xl px-3 py-2 bg-white text-slate-700 font-medium focus:outline-none focus:border-brand-500">
                    <option value="all" {{ $currentRisk === 'all' ? 'selected' : '' }}>جميع مستويات الخطر</option>
                    <option value="high" {{ $currentRisk === 'high' ? 'selected' : '' }}>خطر مرتفع (حرج)</option>
                    <option value="medium" {{ $currentRisk === 'medium' ? 'selected' : '' }}>خطر متوسط</option>
                    <option value="low" {{ $currentRisk === 'low' ? 'selected' : '' }}>خطر منخفض</option>
                </select>

                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-xl text-xs font-bold transition-colors">
                    تصفية
                </button>

                @if($search || $currentStatus !== 'all' || $currentRisk !== 'all')
                    <a href="{{ route('orders.index') }}" class="text-xs text-slate-500 hover:text-slate-800 px-2 py-2 font-bold">
                        إعادة ضبط
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- جدول الطلبات الرئيسي -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="p-4">رقم الطلب</th>
                        <th class="p-4">العميل</th>
                        <th class="p-4">المنتج والكمية</th>
                        <th class="p-4">موعد التسليم</th>
                        <th class="p-4">الوقت المتبقي</th>
                        <th class="p-4">المهام</th>
                        <th class="p-4">حالة الطلب</th>
                        <th class="p-4">مستوى الخطر</th>
                        <th class="p-4 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        @php
                            $risk = $risks[$order->id] ?? null;
                            $tasksTotal = $order->tasks->count();
                            $tasksCompleted = $order->tasks->where('status', 'completed')->count();
                            $progress = $tasksTotal > 0 ? round(($tasksCompleted / $tasksTotal) * 100) : ($order->status === 'completed' ? 100 : 0);
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- رقم الطلب -->
                            <td class="p-4 font-mono font-bold text-slate-800">
                                <a href="{{ route('orders.show', $order->id) }}" class="text-brand-600 hover:text-brand-800">
                                    {{ $order->order_number }}
                                </a>
                            </td>

                            <!-- بيانات العميل -->
                            <td class="p-4">
                                <div class="font-bold text-slate-800">{{ $order->customer_name }}</div>
                                @if($order->customer_phone)
                                    <span class="text-[11px] text-slate-400 font-mono">{{ $order->customer_phone }}</span>
                                @endif
                            </td>

                            <!-- المنتج -->
                            <td class="p-4">
                                <div class="font-bold text-slate-700">{{ $order->product_name }}</div>
                                <span class="text-[11px] text-slate-400">الكمية: {{ $order->quantity }}</span>
                            </td>

                            <!-- موعد التسليم -->
                            <td class="p-4">
                                <div class="font-bold {{ $order->due_date->isPast() && $order->status !== 'completed' ? 'text-rose-600' : 'text-slate-800' }}">
                                    {{ $order->due_date->format('Y-m-d') }}
                                </div>
                                <span class="text-[10px] text-slate-400">
                                    {{ $order->due_date->isPast() ? 'مضى منذ ' . abs((int)$order->due_date->diffInDays(now())) . ' يوم' : 'متبقي ' . (int)$order->due_date->diffInDays(now()) . ' يوم' }}
                                </span>
                            </td>

                            <!-- الوقت المتبقي -->
                            <td class="p-4">
                                <div class="font-bold text-slate-700">{{ $order->remaining_duration }} دقيقة</div>
                                <span class="text-[10px] text-slate-400">من أصل {{ $order->estimated_duration }} د</span>
                            </td>

                            <!-- تقدم المهام التنفيذية -->
                            <td class="p-4">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[11px] font-bold text-slate-600">{{ $tasksCompleted }}/{{ $tasksTotal }}</span>
                                    <span class="text-[10px] text-slate-400">({{ $progress }}%)</span>
                                </div>
                                <div class="w-20 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-brand-500 rounded-full" style="width: {{ $progress }}%"></div>
                                </div>
                            </td>

                            <!-- حالة الطلب -->
                            <td class="p-4">
                                @if($order->status === 'new')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        جديد
                                    </span>
                                @elseif($order->status === 'in_progress')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        قيد التنفيذ
                                    </span>
                                @elseif($order->status === 'waiting')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        بالانتظار
                                    </span>
                                @elseif($order->status === 'late')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        متأخر
                                    </span>
                                @elseif($order->status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        مكتمل
                                    </span>
                                @endif
                            </td>

                            <!-- مستوى الخطر التنبؤي -->
                            <td class="p-4">
                                @if(($risk['level'] ?? $order->risk_level) === 'high')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200" title="{{ $risk['reasons'][0] ?? '' }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                        مرتفع
                                    </span>
                                @elseif(($risk['level'] ?? $order->risk_level) === 'medium')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200" title="{{ $risk['reasons'][0] ?? '' }}">
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

                            <!-- الإجراءات -->
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('orders.show', $order->id) }}" title="عرض التفاصيل والمهام"
                                       class="p-1.5 rounded-lg text-slate-500 hover:text-brand-600 hover:bg-slate-100 transition-colors">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>

                                    <a href="{{ route('orders.edit', $order->id) }}" title="تعديل"
                                       class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-slate-100 transition-colors">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>

                                    <form method="POST" action="{{ route('orders.destroy', $order->id) }}" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا الطلب؟');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="حذف" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-100 transition-colors">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-sm font-semibold">لم يتم العثور على أي طلبات مطابقة للبحث أو الفلتر.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
