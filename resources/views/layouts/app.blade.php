<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'لوحة التحكم' }} | وَتيرة — منصة إدارة الطلبات والمحاكاة الذكية</title>

    <!-- خط كايرو العربي الفاخر -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN للتوافق الفوري والكامل -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Cairo', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Chart.js للرسوم البيانية -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        /* تخصيص شريط التمرير */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-brand-500 selection:text-white">

    <div class="flex-1 flex overflow-hidden">
        <!-- القائمة الجانبية (Sidebar) -->
        <aside class="w-64 bg-white border-l border-slate-200 flex flex-col shrink-0 z-30 transition-all duration-300 hidden md:flex shadow-sm">
            <!-- الشعار وبيانات المتجر الحالي -->
            <div class="h-20 flex items-center gap-3 px-6 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-brand-500/20 shrink-0">
                    <i data-lucide="store" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <h1 class="text-sm font-bold tracking-tight text-slate-800 truncate" title="{{ auth()->user()?->store?->name }}">
                        {{ auth()->user()?->store?->name ?? 'وَتِيرَة' }}
                    </h1>
                    <p class="text-[11px] text-brand-600 font-semibold truncate">{{ auth()->user()?->name ?? 'صاحب المتجر' }}</p>
                </div>
            </div>

            <!-- روابط التنقل -->
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-colors {{ request()->routeIs('dashboard') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                    <span>لوحة التحكم</span>
                </a>

                <a href="{{ route('orders.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-colors {{ request()->routeIs('orders.*') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="shopping-bag" class="w-5 h-5 {{ request()->routeIs('orders.*') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                        <span>الطلبات</span>
                    </div>
                    <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-bold">
                        {{ \App\Models\Order::where('store_id', auth()->user()?->store_id)->where('status', '!=', 'completed')->count() }}
                    </span>
                </a>

                <a href="{{ route('simulator.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-colors {{ request()->routeIs('simulator.*') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="activity" class="w-5 h-5 {{ request()->routeIs('simulator.*') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                    <span>محاكاة ضغط العمل</span>
                </a>

                <a href="{{ route('risks.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-colors {{ request()->routeIs('risks.*') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="shield-alert" class="w-5 h-5 {{ request()->routeIs('risks.*') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                        <span>التنبؤ بالتأخير</span>
                    </div>
                    @php $highRisks = \App\Models\Order::where('store_id', auth()->user()?->store_id)->where('risk_level', 'high')->where('status', '!=', 'completed')->count(); @endphp
                    @if($highRisks > 0)
                        <span class="text-xs bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full font-bold animate-pulse">
                            {{ $highRisks }} حرج
                        </span>
                    @endif
                </a>

                <a href="{{ route('schedule.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-colors {{ request()->routeIs('schedule.*') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="calendar" class="w-5 h-5 {{ request()->routeIs('schedule.*') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                    <span>جدول العمل</span>
                </a>

                <div class="pt-4 pb-2">
                    <div class="h-px bg-slate-100"></div>
                </div>

                <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-colors {{ request()->routeIs('settings.*') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="sliders" class="w-5 h-5 {{ request()->routeIs('settings.*') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                    <span>إعدادات الورشة</span>
                </a>

                <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-colors {{ request()->routeIs('profile.*') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="user-check" class="w-5 h-5 {{ request()->routeIs('profile.*') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                    <span>الملف الشخصي للمتجر</span>
                </a>
            </nav>

            <!-- زر المساعد الذكي -->
            <div class="p-4 border-t border-slate-100">
                <button onclick="openAiChatModal()" class="w-full bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-700 hover:to-indigo-700 text-white p-3 rounded-xl flex items-center justify-center gap-2 shadow-sm font-bold text-sm transition-all hover:shadow-brand-500/25">
                    <i data-lucide="sparkles" class="w-4 h-4 text-amber-300"></i>
                    <span>مساعد وَتيرة الذكي</span>
                </button>
            </div>
        </aside>

        <!-- المحتوى الرئيسي (Main Content) -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            <!-- شريط العنوان العلوي (Top Header) -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0 sticky top-0 z-20">
                <div class="flex items-center gap-4">
                    <button class="md:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100" onclick="document.querySelector('aside').classList.toggle('hidden')">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">{{ $headerTitle ?? 'وَتيرة' }}</h2>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('orders.create') }}" class="bg-brand-600 hover:bg-brand-700 text-white px-3.5 py-2 rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm transition-all">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>طلب جديد</span>
                    </a>

                    <!-- بيانات المستخدم وزر تسجيل الخروج -->
                    <div class="h-6 w-px bg-slate-200"></div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('profile.show') }}" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-50 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 font-bold flex items-center justify-center text-xs">
                                {{ mb_substr(auth()->user()?->name ?? 'م', 0, 1) }}
                            </div>
                            <div class="hidden sm:block text-right">
                                <span class="text-xs font-bold text-slate-800 block leading-tight">{{ auth()->user()?->name }}</span>
                                <span class="text-[10px] text-slate-400 font-medium">{{ auth()->user()?->store?->name }}</span>
                            </div>
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" title="تسجيل الخروج" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- تنبيهات الفلاش (Flash Messages) -->
            <div class="px-6 pt-4">
                @if(session('success'))
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-sm mb-4">
                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                        <span class="text-sm font-semibold">{{ session('success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm mb-4">
                        <div class="flex items-center gap-2 font-bold mb-1 text-sm">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i>
                            <span>يرجى تصحيح الأخطاء التالية:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-1">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- مساحة الصفحة الفعلية -->
            <main class="flex-1 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- نافذة المساعد الذكي المودال (AI Chat Modal) -->
    <div id="aiChatModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl flex flex-col max-h-[85vh] overflow-hidden border border-slate-100 animate-in fade-in zoom-in duration-200">
            <!-- الهيدر -->
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-brand-50 to-indigo-50">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center shadow-md shadow-brand-500/20">
                        <i data-lucide="sparkles" class="w-5 h-5 text-amber-300"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">مساعد وَتيرة الذكي</h3>
                        <p class="text-xs text-slate-500">إجابات دقيقة مبنية على بيانات ورشتك الحالية</p>
                    </div>
                </div>
                <button onclick="closeAiChatModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-white/80">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- سجل الحوار -->
            <div id="aiChatMessages" class="p-4 flex-1 overflow-y-auto space-y-3 min-h-[280px]">
                <div class="bg-slate-50 p-3.5 rounded-2xl text-xs text-slate-700 leading-relaxed border border-slate-100">
                    مرحباً بك! أنا مساعد وَتيرة الذكي. يمكنني تحليل جدول عملك وإخبارك بإمكانية قبول طلب جديد، أو توضيح أسباب ضغط العمل، والطلبات المعرضة للتأخير من واقع بياناتك الفعلية.
                </div>

                <!-- أسئلة سريعة مقترحة -->
                <div class="space-y-1.5 pt-2">
                    <p class="text-[11px] font-bold text-slate-400">أسئلة سريعة:</p>
                    <div class="flex flex-wrap gap-1.5">
                        <button onclick="sendAiQuickQuestion('هل أستطيع استقبال طلب جديد هذا الأسبوع؟')" class="text-xs bg-slate-100 hover:bg-brand-50 hover:text-brand-700 text-slate-700 px-2.5 py-1.5 rounded-lg transition-colors border border-slate-200/60 font-medium">
                            هل أستطيع استقبال طلب هذا الأسبوع؟
                        </button>
                        <button onclick="sendAiQuickQuestion('ما الطلبات المعرضة للتأخير؟')" class="text-xs bg-slate-100 hover:bg-brand-50 hover:text-brand-700 text-slate-700 px-2.5 py-1.5 rounded-lg transition-colors border border-slate-200/60 font-medium">
                            ما الطلبات المعرضة للتأخير؟
                        </button>
                        <button onclick="sendAiQuickQuestion('ما أكثر الأيام ازدحاماً؟')" class="text-xs bg-slate-100 hover:bg-brand-50 hover:text-brand-700 text-slate-700 px-2.5 py-1.5 rounded-lg transition-colors border border-slate-200/60 font-medium">
                            ما أكثر الأيام ازدحاماً؟
                        </button>
                        <button onclick="sendAiQuickQuestion('لخّص حالة المشروع')" class="text-xs bg-slate-100 hover:bg-brand-50 hover:text-brand-700 text-slate-700 px-2.5 py-1.5 rounded-lg transition-colors border border-slate-200/60 font-medium">
                            لخّص حالة المشروع
                        </button>
                    </div>
                </div>
            </div>

            <!-- صندوق الإدخال -->
            <div class="p-3 border-t border-slate-100 bg-white">
                <form id="aiChatForm" onsubmit="handleAiChatSubmit(event)" class="flex gap-2">
                    <input type="text" id="aiChatInput" placeholder="اكتب سؤالك هنا..." class="flex-1 px-3.5 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition-colors flex items-center justify-center">
                        <i data-lucide="send" class="w-4 h-4 rtl:rotate-180"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // تفعيل أيقونات Lucide
        lucide.createIcons();

        function openAiChatModal() {
            document.getElementById('aiChatModal').classList.remove('hidden');
            document.getElementById('aiChatInput').focus();
        }

        function closeAiChatModal() {
            document.getElementById('aiChatModal').classList.add('hidden');
        }

        function sendAiQuickQuestion(text) {
            document.getElementById('aiChatInput').value = text;
            handleAiChatSubmit(new Event('submit'));
        }

        async function handleAiChatSubmit(e) {
            e.preventDefault();
            const input = document.getElementById('aiChatInput');
            const q = input.value.trim();
            if (!q) return;

            const container = document.getElementById('aiChatMessages');

            // إضافة رسالة المستخدم
            container.innerHTML += `
                <div class="flex justify-end">
                    <div class="bg-brand-600 text-white p-3 rounded-2xl text-xs max-w-[85%] font-medium">
                        ${q}
                    </div>
                </div>
            `;
            input.value = '';
            container.scrollTop = container.scrollHeight;

            // مؤشر التحميل
            const loadingId = 'loading_' + Date.now();
            container.innerHTML += `
                <div id="${loadingId}" class="bg-slate-50 p-3 rounded-2xl text-xs text-slate-500 flex items-center gap-2 border border-slate-100">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-brand-600"></i>
                    <span>جاري تحليل البيانات وحساب المؤشرات...</span>
                </div>
            `;
            lucide.createIcons();
            container.scrollTop = container.scrollHeight;

            try {
                const res = await fetch("{{ route('ai.chat') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ question: q })
                });

                const data = await res.json();
                document.getElementById(loadingId)?.remove();

                let bulletsHtml = '';
                if (data.bullets && data.bullets.length) {
                    bulletsHtml = '<ul class="list-disc list-inside mt-2 space-y-1 text-slate-600">' +
                        data.bullets.map(b => `<li>${b}</li>`).join('') +
                        '</ul>';
                }

                container.innerHTML += `
                    <div class="bg-slate-50 p-3.5 rounded-2xl text-xs text-slate-800 leading-relaxed border border-slate-200/80 shadow-sm">
                        <div class="flex items-center gap-1.5 font-bold text-brand-700 mb-1">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                            <span>${data.decision || 'تحليل وَتيرة'}</span>
                        </div>
                        <p>${data.message}</p>
                        ${bulletsHtml}
                    </div>
                `;
            } catch (err) {
                document.getElementById(loadingId)?.remove();
                container.innerHTML += `
                    <div class="bg-rose-50 p-3 rounded-2xl text-xs text-rose-700 border border-rose-200">
                        حدث خطأ أثناء معالجة السؤال، يرجى المحاولة مرة أخرى.
                    </div>
                `;
            }

            lucide.createIcons();
            container.scrollTop = container.scrollHeight;
        }
    </script>

    @stack('scripts')
</body>
</html>
