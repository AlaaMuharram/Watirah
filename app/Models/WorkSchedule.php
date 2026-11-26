<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class WorkSchedule extends Model {
    protected $fillable = ['user_id','date','available_hours','used_hours'];
    protected $casts = ['date'=>'date','available_hours'=>'decimal:2','used_hours'=>'decimal:2'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}