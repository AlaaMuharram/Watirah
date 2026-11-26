<?php

namespace App\Http\Controllers;

use App\Models\ProjectSetting;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'يرجى إدخال البريد الإلكتروني.',
            'email.email' => 'يرجى إدخال بريد إلكتروني صالح.',
            'password.required' => 'يرجى إدخال كلمة المرور.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'مرحباً بك مجدداً في وَتيرة! تم تسجيل الدخول بنجاح.');
        }

        return back()->withErrors([
            'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
        ])->onlyInput('email');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'store_name' => 'required|string|max:200',
            'email' => 'required|email|max:191|unique:users,email',
            'phone' => 'required|string|max:30|unique:users,phone|unique:stores,phone',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => 'يرجى إدخال اسم صاحب المتجر.',
            'store_name.required' => 'يرجى إدخال اسم المتجر أو المشروع.',
            'email.required' => 'يرجى إدخال البريد الإلكتروني.',
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً في النظام.',
            'phone.required' => 'يرجى إدخال رقم الهاتف.',
            'phone.unique' => 'رقم الهاتف مسجل مسبقاً لمتجر آخر.',
            'password.required' => 'يرجى إدخال كلمة المرور.',
            'password.min' => 'يجب ألا تقل كلمة المرور عن 6 أحرف.',
            'password.confirmed' => 'كلمة المرور وتأكيدها غير متطابقين.',
        ]);

        // إنشاء المتجر والمستخدم داخل Transaction لضمان سلامة البيانات
        $user = DB::transaction(function () use ($validated) {
            $store = Store::create([
                'name' => $validated['store_name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'description' => 'متجر حرفي مسجل حديثاً في منصة وَتيرة.',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'store_id' => $store->id,
                'password' => Hash::make($validated['password']),
            ]);

            // إعدادات افتراضية للمتجر الجديد
            ProjectSetting::create([
                'store_id' => $store->id,
                'project_name' => $store->name,
                'working_days' => [0, 1, 2, 3, 4], // الأحد - الخميس
                'start_time' => '09:00',
                'end_time' => '17:00',
                'daily_productive_hours' => 5.0,
                'break_duration' => 60,
                'buffer_days' => 1,
            ]);

            return $user;
        });

        // تسجيل الدخول التلقائي للمستخدم الجديد
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', "أهلاً بك يا {$user->name}! تم إنشاء حساب متجر «{$user->store->name}» بنجاح.");
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'تم تسجيل الخروج بنجاح.');
    }
}
