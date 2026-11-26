<x-app-layout>
    <x-slot name="title">تعديل الطلب {{ $order->order_number }}</x-slot>
    <x-slot name="headerTitle">تعديل بيانات الطلب: {{ $order->order_number }}</x-slot>

    <div class="max-w-3xl mx-auto bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="POST" action="{{ route('orders.update', $order->id) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">اسم العميل <span class="text-rose-500">*</span></label>
                    <input type="text" name="customer_name" value="{{ old('customer_name', $order->customer_name) }}" required
                           class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">رقم الهاتف</label>
                    <input type="text" name="customer_phone" value="{{ old('customer_phone', $order->customer_phone) }}"
                           class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">نوع المنتج <span class="text-rose-500">*</span></label>
                    <input type="text" name="product_name" value="{{ old('product_name', $order->product_name) }}" required
                           class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">الكمية <span class="text-rose-500">*</span></label>
                    <input type="number" name="quantity" min="1" value="{{ old('quantity', $order->quantity) }}" required
                           class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">تاريخ الاستلام <span class="text-rose-500">*</span></label>
                    <input type="date" name="received_date" value="{{ old('received_date', $order->received_date->format('Y-m-d')) }}" required
                           class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">موعد التسليم <span class="text-rose-500">*</span></label>
                    <input type="date" name="due_date" value="{{ old('due_date', $order->due_date->format('Y-m-d')) }}" required
                           class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">حالة الطلب <span class="text-rose-500">*</span></label>
                    <select name="status" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-white focus:outline-none focus:border-brand-500">
                        <option value="new" {{ $order->status === 'new' ? 'selected' : '' }}>جديد</option>
                        <option value="in_progress" {{ $order->status === 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                        <option value="waiting" {{ $order->status === 'waiting' ? 'selected' : '' }}>بالانتظار</option>
                        <option value="late" {{ $order->status === 'late' ? 'selected' : '' }}>متأخر</option>
                        <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>مكتمل</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">الوصف والمواصفات</label>
                <textarea name="description" rows="2" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">{{ old('description', $order->description) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">ملاحظات داخلية</label>
                <textarea name="notes" rows="2" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">{{ old('notes', $order->notes) }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('orders.show', $order->id) }}" class="px-5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                    إلغاء
                </a>
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-2 rounded-xl text-xs font-bold shadow-sm">
                    حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
