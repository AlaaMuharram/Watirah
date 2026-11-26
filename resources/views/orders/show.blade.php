<x-app-layout>
    <x-slot name="title">تفاصيل الطلب {{ $order->order_number }}</x-slot>
    <x-slot name="headerTitle">تفاصيل الطلب: {{ $order->order_number }}</x-slot>

    <!-- شريط معلومات الطلب الرئيسي -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h2 class="text-xl font-black text-slate-800">{{ $order->customer_name }}</h2>
                    <span class="text-xs font-mono font-bold bg-slate-100 text-slate-600 px-2.5 py-1 rounded-lg">
                        {{ $order->order_number }}
                    </span>
                    <!-- شارة الحالة -->
                    @if($order->status === 'new')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">جديد</span>
                    @elseif($order->status === 'in_progress')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">قيد التنفيذ</span>
                    @elseif($order->status === 'waiting')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">بالانتظار</span>
                    @elseif($order->status === 'late')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">متأخر</span>
                    @elseif($order->status === 'completed')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">مكتمل</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 font-medium">{{ $order->product_name }} &bull; الكمية: {{ $order->quantity }} قطعة</p>
            </div>

            <!-- أزرار الإجراءات السريعة -->
            <div class="flex items-center gap-2">
                <!-- قائمة تغيير الحالة السريع -->
                <form method="POST" action="{{ route('orders.status', $order->id) }}" class="inline">
                    @csrf
                    <select name="status" onchange="this.form.submit()" class="text-xs font-bold border border-slate-200 rounded-xl px-3 py-2 bg-slate-50 text-slate-700 focus:outline-none focus:border-brand-500">
                        <option value="new" {{ $order->status === 'new' ? 'selected' : '' }}>جديد</option>
                        <option value="in_progress" {{ $order->status === 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                        <option value="waiting" {{ $order->status === 'waiting' ? 'selected' : '' }}>بالانتظار</option>
                        <option value="late" {{ $order->status === 'late' ? 'selected' : '' }}>متأخر</option>
                        <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>مكتمل</option>
                    </select>
                </form>

                <a href="{{ route('orders.edit', $order->id) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                    <span>تعديل</span>
                </a>

                <form method="POST" action="{{ route('orders.destroy', $order->id) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب؟');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-700 px-3 py-2 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        <span>حذف</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- تفاصيل الأوقات والتواريخ -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-5 text-xs">
            <div>
                <span class="text-slate-400 block mb-1">تاريخ الاستلام</span>
                <span class="font-bold text-slate-800">{{ $order->received_date->format('Y-m-d') }}</span>
            </div>
            <div>
                <span class="text-slate-400 block mb-1">موعد التسليم المتفق عليه</span>
                <span class="font-bold {{ $order->due_date->isPast() && $order->status !== 'completed' ? 'text-rose-600' : 'text-slate-800' }}">
                    {{ $order->due_date->format('Y-m-d') }} ({{ $order->due_date->diffForHumans() }})
                </span>
            </div>
            <div>
                <span class="text-slate-400 block mb-1">إجمالي العمل المقدر</span>
                <span class="font-bold text-slate-800">{{ $order->estimated_duration }} دقيقة</span>
            </div>
            <div>
                <span class="text-slate-400 block mb-1">العمل المتبقي للتسليم</span>
                <span class="font-bold text-brand-600">{{ $order->remaining_duration }} دقيقة</span>
            </div>
        </div>

        @if($order->description)
            <div class="mt-4 p-3 bg-slate-50 rounded-xl text-xs text-slate-700 border border-slate-100">
                <span class="font-bold text-slate-800 block mb-1">وصف ومواصفات الطلب:</span>
                <p>{{ $order->description }}</p>
            </div>
        @endif
    </div>

    <!-- شبكة القسمين: المهام التنفيذية + تقييم المخاطر والسجل -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- القسم 1: إدارة المهام التنفيذية (Tasks Management) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <i data-lucide="check-square" class="w-5 h-5 text-brand-600"></i>
                        <h3 class="font-bold text-slate-800 text-base">المهام التنفيذية للطلب</h3>
                    </div>
                    <span class="text-xs bg-brand-50 text-brand-700 px-3 py-1 rounded-full font-bold">
                        {{ $order->tasks->where('status', 'completed')->count() }} من {{ $order->tasks->count() }} مكتملة
                    </span>
                </div>

                <!-- قائمة المهام -->
                <div class="space-y-2.5 mb-6">
                    @forelse($order->tasks as $task)
                        <div class="p-3.5 rounded-xl border {{ $task->status === 'completed' ? 'bg-slate-50 border-slate-200/60 opacity-80' : 'bg-white border-slate-200 shadow-xs' }} flex items-center justify-between gap-3 transition-all">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <!-- زر تبديل الحالة بنقرة واحدة -->
                                <form method="POST" action="{{ route('tasks.toggle', $task->id) }}">
                                    @csrf
                                    <button type="submit" class="w-6 h-6 rounded-lg border {{ $task->status === 'completed' ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-300 text-transparent hover:border-brand-500' }} flex items-center justify-center transition-colors">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                    </button>
                                </form>

                                <div class="min-w-0">
                                    <h4 class="text-xs font-bold text-slate-800 {{ $task->status === 'completed' ? 'line-through text-slate-400' : '' }}">
                                        {{ $task->title }}
                                    </h4>
                                    <div class="flex items-center gap-2 text-[10px] text-slate-400 mt-0.5">
                                        <span>{{ $task->duration_minutes }} دقيقة</span>
                                        <span>&bull;</span>
                                        <span class="font-bold {{ $task->priority === 'high' ? 'text-rose-600' : ($task->priority === 'medium' ? 'text-amber-600' : 'text-slate-500') }}">
                                            أولوية {{ $task->priority_label }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('tasks.destroy', $task->id) }}" onsubmit="return confirm('حذف المهمة؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-rose-600 p-1 rounded-lg">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-xs">
                            لم يتم إضافة أي مهام تنفيذية بعد. أضف مهامك لتتبع مراحل الإنجاز بدقة.
                        </div>
                    @endforelse
                </div>

                <!-- نموذج إضافة مهمة جديدة -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80">
                    <h4 class="text-xs font-bold text-slate-800 mb-3 flex items-center gap-1.5">
                        <i data-lucide="plus" class="w-4 h-4 text-brand-600"></i>
                        <span>إضافة مهمة تنفيذية جديدة</span>
                    </h4>

                    <form method="POST" action="{{ route('orders.tasks.store', $order->id) }}" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div class="sm:col-span-2">
                                <input type="text" name="title" required placeholder="عنوان المهمة (مثلاً: تجهيز التغليف والكرت)"
                                       class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-brand-500">
                            </div>

                            <div>
                                <input type="number" name="duration_minutes" min="5" value="30" required placeholder="المدة بالدقائق"
                                       class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-brand-500">
                            </div>

                            <div>
                                <select name="priority" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-brand-500">
                                    <option value="high">أولوية عالية</option>
                                    <option value="medium" selected>أولوية متوسطة</option>
                                    <option value="low">أولوية منخفضة</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold px-4 py-1.5 rounded-lg shadow-sm transition-colors">
                                إضافة المهمة
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- الخط الزمني لسجل التنفيذ والتغييرات -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
                    <i data-lucide="history" class="w-5 h-5 text-slate-600"></i>
                    <h3 class="font-bold text-slate-800 text-base">سجل أحداث وحالات الطلب</h3>
                </div>

                <div class="relative pr-6 space-y-4 before:content-[''] before:absolute before:top-2 before:bottom-2 before:right-2 before:w-0.5 before:bg-slate-200">
                    @forelse($order->executionLogs as $log)
                        <div class="relative text-xs">
                            <div class="absolute -right-6 top-1 w-2.5 h-2.5 rounded-full bg-brand-600 ring-4 ring-white"></div>
                            <div class="flex items-center justify-between mb-0.5">
                                <span class="font-bold text-slate-800">
                                    {{ $log->notes ?: 'تحديث الحالة' }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $log->changed_at->format('Y-m-d H:i') }}</span>
                            </div>
                            <span class="text-[11px] text-slate-500">الحالة: {{ $log->new_status }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">لا يوجد سجلات سابقة.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- القسم 2: تقرير التنبؤ بالمخاطر المخصص للطلب -->
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
                    <i data-lucide="shield-alert" class="w-5 h-5 text-amber-500"></i>
                    <h3 class="font-bold text-slate-800 text-base">تقرير التنبؤ بالتأخير</h3>
                </div>

                @if($orderRisk)
                    <div class="mb-4">
                        <div class="p-3.5 rounded-xl border {{ $orderRisk['level'] === 'high' ? 'bg-rose-50 border-rose-200 text-rose-800' : ($orderRisk['level'] === 'medium' ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800') }}">
                            <div class="flex items-center justify-between mb-1 font-bold text-xs">
                                <span>مستوى الخطر المقدر:</span>
                                <span>{{ $orderRisk['level'] === 'high' ? 'مرتفع (حرج)' : ($orderRisk['level'] === 'medium' ? 'متوسط' : 'منخفض ومستقر') }}</span>
                            </div>
                            <div class="text-[11px] space-y-1">
                                <div>الوقت الإنتاجي المتاح: {{ $orderRisk['available_capacity'] }} دقيقة</div>
                                <div>الطلب التراكمي: {{ $orderRisk['cumulative_demand'] }} دقيقة</div>
                                <div>هامش الأمان: {{ $orderRisk['slack_minutes'] }} دقيقة</div>
                                @if($orderRisk['expected_finish'])
                                    <div>أقرب موعد إنجاز متوقع: <strong class="font-mono">{{ $orderRisk['expected_finish'] }}</strong></div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- أسباب التوقع -->
                    <div class="mb-4">
                        <h4 class="text-xs font-bold text-slate-700 mb-2">أسباب التوقع:</h4>
                        <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside bg-slate-50 p-3 rounded-xl border border-slate-100">
                            @foreach($orderRisk['reasons'] as $r)
                                <li>{{ $r }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- الإجراءات الموصى بها -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-700 mb-2">الإجراءات الموصى بها:</h4>
                        <ul class="text-xs text-slate-600 space-y-1.5 bg-slate-50 p-3 rounded-xl border border-slate-100">
                            @foreach($orderRisk['actions'] as $act)
                                <li class="flex items-start gap-1.5">
                                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600 shrink-0 mt-0.5"></i>
                                    <span>{{ $act }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="text-xs text-slate-400">الطلب مكتمل أو لا توجد مخاطر نشطة عليه.</p>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
