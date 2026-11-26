<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSetting extends Model
{
    protected $table = 'project_settings';

    protected $fillable = [
        'id',
        'store_id',
        'project_name',
        'working_days',
        'start_time',
        'end_time',
        'daily_productive_hours',
        'break_duration',
        'buffer_days',
    ];

    protected $casts = [
        'working_days' => 'array',
        'daily_productive_hours' => 'float',
        'break_duration' => 'integer',
        'buffer_days' => 'integer',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public static function current(?int $storeId = null): self
    {
        $storeId = $storeId ?: auth()->user()?->store_id;

        if ($storeId) {
            $setting = static::where('store_id', $storeId)->first();
            if ($setting) {
                return $setting;
            }

            $store = Store::find($storeId);
            $storeName = $store ? $store->name : 'متجري الحرفي';

            return static::create([
                'store_id' => $storeId,
                'project_name' => $storeName,
                'working_days' => [0, 1, 2, 3, 4], // الأحد - الخميس
                'start_time' => '09:00',
                'end_time' => '17:00',
                'daily_productive_hours' => 5.0,
                'break_duration' => 60,
                'buffer_days' => 1,
            ]);
        }

        return static::first() ?: static::create([
            'project_name' => 'ورود ليان — ورود دائمة وهدايا يدوية',
            'working_days' => [0, 1, 2, 3, 4],
            'start_time' => '09:00',
            'end_time' => '17:00',
            'daily_productive_hours' => 5.0,
            'break_duration' => 60,
            'buffer_days' => 1,
        ]);
    }
}
