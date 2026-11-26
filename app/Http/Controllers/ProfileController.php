<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();
        $store = $user->store;

        return view('profile.show', [
            'user' => $user,
            'store' => $store,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $store = $user->store;

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'store_name' => 'required|string|max:200',
            'email' => "required|email|max:191|unique:users,email,{$user->id}",
            'phone' => "required|string|max:30|unique:users,phone,{$user->id}|unique:stores,phone,{$store->id}",
            'store_description' => 'nullable|string|max:1000',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:6|confirmed',
        ], [
            'name.required' => 'يرجى إدخال اسم صاحب المتجر.',
            'store_name.required' => 'يرجى إدخال اسم المتجر.',
            'email.unique' => 'البريد الإلكتروني مسجل لحساب آخر.',
            'phone.unique' => 'رقم الهاتف مسجل لحساب آخر.',
            'new_password.min' => 'يجب ألا تقل كلمة المرور عن 6 أحرف.',
            'new_password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
        ]);

        // التحقق من كلمة المرور الحالية إن رغب في تغييرها
        if (!empty($validated['new_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة.']);
            }
            $user->password = Hash::make($validated['new_password']);
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        $user->save();

        if ($store) {
            $store->name = $validated['store_name'];
            $store->phone = $validated['phone'];
            $store->email = $validated['email'];
            $store->description = $validated['store_description'] ?? $store->description;
            $store->save();
        }

        return back()->with('success', 'تم تحديث بيانات المتجر والحساب الشخصي بنجاح.');
    }
}
