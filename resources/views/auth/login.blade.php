<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول | وَتيرة — منصة إدارة المتاجر الحرفية</title>

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

    <div class="w-full max-w-md">
        <!-- الشعار والهوية -->
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-600 items-center justify-center text-white shadow-xl shadow-brand-500/25 mb-3">
                <i data-lucide="layers" class="w-8 h-8"></i>
            </div>
            <h1 class="text-2xl font-black text-slate-800">وَتِيرَة | WATIRA</h1>
            <p class="text-xs text-slate-500 font-medium mt-1">المنصة الذكية لإدارة ومحاكاة طلبات المتاجر الحرفية</p>
        </div>

        <!-- صندوق تسجيل الدخول -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200/80 shadow-xl shadow-slate-100">
            <h2 class="text-lg font-bold text-slate-800 mb-1">تسجيل دخول صاحب المتجر</h2>
            <p class="text-xs text-slate-400 mb-6 font-medium">أدخل بيانات حسابك للوصول إلى لوحة تحكم متجرك</p>

            @if(session('success'))
                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold mb-5 flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold mb-5 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">البريد الإلكتروني</label>
                    <div class="relative">
                        <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute right-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="name@example.com"
                               class="w-full pr-10 pl-4 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-bold text-slate-700">كلمة المرور</label>
                    </div>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute right-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="password" name="password" required
                               placeholder="••••••••"
                               class="w-full pr-10 pl-4 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600 font-medium">
                        <input type="checkbox" name="remember" class="rounded text-brand-600 focus:ring-brand-500 border-slate-300">
                        <span>تذكر تسجيل الدخول</span>
                    </label>
                    <a href="javascript:void(0)" onclick="alert('في البيئة التجريبية، يمكنك استخدام كلمة المرور الافتراضية: password123')" class="text-brand-600 hover:text-brand-800 font-bold">
                        نسيت كلمة المرور؟
                    </a>
                </div>

                <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 px-4 rounded-xl text-xs shadow-md shadow-brand-500/20 transition-all flex items-center justify-center gap-2 mt-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>تسجيل الدخول</span>
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    ليس لديك حساب متجر بعد؟
                    <a href="{{ route('register') }}" class="font-bold text-brand-600 hover:text-brand-800 underline decoration-2 underline-offset-4">
                        إنشاء متجر جديد الآن
                    </a>
                </p>
            </div>
        </div>

        <!-- بطاقة المساعدة السريعة للمحكمين والمستخدمين -->
        <div class="mt-5 bg-slate-100/80 p-4 rounded-2xl border border-slate-200/60 text-xs">
            <span class="font-bold text-slate-700 block mb-2">💡 حسابات تجريبية جاهزة للاختبار الفوري:</span>
            <div class="space-y-1.5 text-slate-600 text-[11px]">
                <div class="flex items-center justify-between bg-white p-2 rounded-lg border border-slate-200/50">
                    <div>
                        <strong class="text-brand-700">متجر ورود ليان:</strong> layan@watira.com
                    </div>
                    <button type="button" onclick="fillCreds('layan@watira.com', 'password123')" class="text-brand-600 hover:text-brand-800 font-bold underline">
                        تعبئة
                    </button>
                </div>
                <div class="flex items-center justify-between bg-white p-2 rounded-lg border border-slate-200/50">
                    <div>
                        <strong class="text-brand-700">متجر خزف ياسمين:</strong> yasmeen@watira.com
                    </div>
                    <button type="button" onclick="fillCreds('yasmeen@watira.com', 'password123')" class="text-brand-600 hover:text-brand-800 font-bold underline">
                        تعبئة
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        function fillCreds(email, password) {
            document.querySelector('input[name="email"]').value = email;
            document.querySelector('input[name="password"]').value = password;
        }
    </script>
</body>
</html>
