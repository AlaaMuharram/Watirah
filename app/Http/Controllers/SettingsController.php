<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
use App\Models\ProjectSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $storeId = auth()->user()->store_id;
        $settings = ProjectSetting::current($storeId);
        $products = ProductType::where('store_id', $storeId)->get();

        return view('settings.index', [
            'settings' => $settings,
            'products' => $products,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $storeId = auth()->user()->store_id;

        $validated = $request->validate([
            'project_name' => 'required|string|max:200',
            'working_days' => 'required|array|min:1',
            'working_days.*' => 'integer|between:0,6',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'daily_productive_hours' => 'required|numeric|min:0.5|max:24',
            'break_duration' => 'nullable|integer|min:0',
            'buffer_days' => 'nullable|integer|min:0',
        ]);

        $settings = ProjectSetting::current($storeId);
        $settings->update([
            'project_name' => $validated['project_name'],
            'working_days' => array_map('intval', $validated['working_days']),
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'daily_productive_hours' => (float) $validated['daily_productive_hours'],
            'break_duration' => (int) ($validated['break_duration'] ?? 0),
            'buffer_days' => (int) ($validated['buffer_days'] ?? 0),
        ]);

        return back()->with('success', 'تم حفظ إعدادات الورشة بنجاح.');
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $storeId = auth()->user()->store_id;

        $validated = $request->validate([
            'product_name' => 'required|string|max:200',
            'estimated_unit_duration' => 'required|integer|min:5',
            'notes' => 'nullable|string|max:500',
        ]);

        ProductType::create([
            'id' => 'p_' . Str::random(8),
            'store_id' => $storeId,
            'product_name' => $validated['product_name'],
            'estimated_unit_duration' => $validated['estimated_unit_duration'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'تمت إضافة نوع المنتج بنجاح.');
    }

    public function deleteProduct(string $id): RedirectResponse
    {
        $storeId = auth()->user()->store_id;
        $product = ProductType::where('store_id', $storeId)->findOrFail($id);
        $product->delete();

        return back()->with('success', 'تم حذف نوع المنتج.');
    }
}
