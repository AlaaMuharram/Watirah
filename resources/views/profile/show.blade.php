<x-app-layout>
    <x-slot name="title">الملف الشخصي للمتجر</x-slot>
    <x-slot name="headerTitle">بيانات المتجر والحساب</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <!-- كرت ملخص المتجر -->
        <div class="bg-gradient-to-r from-brand-600 to-indigo-600 rounded-3xl p-6 text-white shadow-lg shadow-brand-500/15 flex flex-col sm:flex-row items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-sm flex items-center justify-center text-white shrink-0">
                <i data-lucide="store" class="w-8 h-8"></i>
            </div>
            <div class="text-center sm:text-right flex-1">
                <h2 class="text-xl font-black mb-1">{{ $store->name }}</h2>
                <p class="text-xs text-brand-100 font-medium">المالك: {{ $user->name }} &bull; الهاتف: {{ $user->phone }}</p>
                <span class="inline-block mt-2 bg-white/20 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full">
                    متجر مستقل ومفعل &bull; معرف: #{{ $store->id }}
                </span>
            </div>
        </div>

        <!-- نموذج تعديل البيانات -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
            <h3 class="font-bold text-slate-800 text-base mb-5 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="settings" class="w-5 h-5 text-brand-600"></i>
                <span>تعديل البيانات الأساسية</span>
            </h3>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">اسم صاحب المتجر <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">اسم المتجر / الورشة <span class="text-rose-500">*</span></label>
                        <input type="text" name="store_name" value="{{ old('store_name', $store->name) }}" required
                               class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">البريد الإلكتروني <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">رقم الهاتف <span class="text-rose-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required
                               class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">وصف المتجر ونشاطه الحرفي</label>
                    <textarea name="store_description" rows="2" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">{{ old('store_description', $store->description) }}</textarea>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <h4 class="font-bold text-slate-800 text-xs mb-3 flex items-center gap-1.5">
                        <i data-lucide="lock" class="w-4 h-4 text-slate-500"></i>
                        <span>تغيير كلمة المرور (اختياري)</span>
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <input type="password" name="current_password" placeholder="كلمة المرور الحالية"
                                   class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                        </div>
                        <div>
                            <input type="password" name="new_password" placeholder="كلمة المرور الجديدة"
                                   class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                        </div>
                        <div>
                            <input type="password" name="new_password_confirmation" placeholder="تأكيد كلمة المرور الجديدة"
                                   class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold py-2.5 px-6 rounded-xl text-xs shadow-sm transition-all flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>حفظ التعديلات</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
