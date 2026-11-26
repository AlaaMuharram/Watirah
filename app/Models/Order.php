<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'orders';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'store_id',
        'order_number',
        'customer_name',
        'customer_phone',
        'product_name',
        'description',
        'quantity',
        'received_date',
        'due_date',
        'duration_mode',
        'unit_duration',
        'estimated_duration',
        'remaining_duration',
        'status',
        'risk_level',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'received_date' => 'date',
        'due_date' => 'date',
        'unit_duration' => 'integer',
        'estimated_duration' => 'integer',
        'remaining_duration' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'order_id')->orderBy('sort_order')->orderBy('created_at');
    }

    public function executionLogs(): HasMany
    {
        return $this->hasMany(ExecutionLog::class, 'order_id')->orderBy('changed_at', 'desc');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'order_id')->orderBy('created_at', 'desc');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new' => 'جديد',
            'waiting' => 'بالانتظار',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'late' => 'متأخر',
            default => $this->status,
        };
    }

    public function getRiskLabelAttribute(): string
    {
        return match ($this->risk_level) {
            'high' => 'مرتفع',
            'medium' => 'متوسط',
            'low' => 'منخفض',
            default => 'منخفض',
        };
    }
}
