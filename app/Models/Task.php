<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $table = 'tasks';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'order_id',
        'title',
        'duration_minutes',
        'status',
        'priority',
        'sort_order',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'sort_order' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'معلقة',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتملة',
            default => $this->status,
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'high' => 'عالية',
            'medium' => 'متوسطة',
            'low' => 'منخفضة',
            default => $this->priority,
        };
    }
}
