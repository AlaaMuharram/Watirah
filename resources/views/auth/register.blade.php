<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إنشاء حساب متجر جديد | وَتيرة — منصة إدارة المتاجر الحرفية</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Cairo', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#f5f3ff', 100: '#ede9fe', 200: '#ddd6fe',
                            300: '#c4b5fd', 400: '#a78bfa', 500: '#8b5cf6',
                            600: '#7c3aed', 700: '#6d28d9', 800: '#5b21b6', 900: '#4c1d95',
                        }
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>body { font-family: 'Cairo', sans-serif; background-color: #f8fafc; }</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-lg my-6">
        <!-- الشعار والهوية -->
        <div class="text-center mb-6">
            <div class="inline-flex w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-600 items-center justify-center text-white shadow-xl shadow-brand-500/25 mb-2">
                <i data-lucide="layers" class="w-7 h-7"></i>
            </div>
            <h1 class="text-2xl font-black text-slate-800">وَتِيرَة | WATIRA</h1>
            <p class="text-xs text-slate-500 font-medium mt-1">ابدأ إدارة متجرك وتوقع مواعيد التسليم باحترافية</p>
        </div>

        <!-- صندوق إنشاء الحساب -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200/80 shadow-xl shadow-slate-100">
            <h2 class="text-lg font-bold text-slate-800 mb-1">تسجيل متجر أو مشروع جديد</h2>
            <p class="text-xs text-slate-400 mb-6 font-medium">أنشئ حسابك المستقل واحصل على مساحة مخصصة لمتجرك وطلباتك</p>

            @if($errors->any())
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs mb-5">
                    <div class="flex items-center gap-1.5 font-bold mb-1">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i>
                        <span>يرجى مراجعة الأخطاء التالية:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">اسم صاحب المتجر <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <i data-lucide="user" class="w-4 h-4 text-slate-400 absolute right-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                                   placeholder="مثال: سارة القحطاني"
                                   class="w-full pr-10 pl-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">اسم المتجر / الورشة <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <i data-lucide="store" class="w-4 h-4 text-slate-400 absolute right-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="text" name="store_name" value="{{ old('store_name') }}" required
                                   placeholder="مثال: متجر هدايا سارة"
                                   class="w-full pr-10 pl-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">البريد الإلكتروني <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute right-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   placeholder="sara@example.com"
                                   class="w-full pr-10 pl-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">رقم الهاتف <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <i data-lucide="phone" class="w-4 h-4 text-slate-400 absolute right-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="text" name="phone" value="{{ old('phone') }}" required
                                   placeholder="05XXXXXXXX"
                                   class="w-full pr-10 pl-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">كلمة المرور <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute right-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="password" name="password" required
                                   placeholder="6 أحرف على الأقل"
                                   class="w-full pr-10 pl-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">تأكيد كلمة المرور <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <i data-lucide="shield-check" class="w-4 h-4 text-slate-400 absolute right-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="password" name="password_confirmation" required
                                   placeholder="أعد كتابة كلمة المرور"
                                   class="w-full pr-10 pl-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 px-4 rounded-xl text-xs shadow-md shadow-brand-500/20 transition-all flex items-center justify-center gap-2 mt-4">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    <span>إنشاء حساب المتجر والبدء</span>
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    لديك متجر مسجل بالفعل؟
                    <a href="{{ route('login') }}" class="font-bold text-brand-600 hover:text-brand-800 underline decoration-2 underline-offset-4">
                        تسجيل الدخول هنا
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
