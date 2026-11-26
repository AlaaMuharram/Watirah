<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProjectSetting extends Model {
    protected $fillable = ['user_id','project_name','work_days','work_start','work_end','daily_production_hours','break_hours','locale'];
    protected $casts = ['work_days'=>'array','daily_production_hours'=>'decimal:2','break_hours'=>'decimal:2'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}