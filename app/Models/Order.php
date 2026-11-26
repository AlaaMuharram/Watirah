<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model {
    protected $fillable = ['user_id','product_type_id','order_number','customer_name','product_name','description','quantity','received_date','due_date','unit_prep_minutes','total_prep_minutes','status','risk_level','notes'];
    protected $casts = ['received_date'=>'date','due_date'=>'date','unit_prep_minutes'=>'decimal:2','total_prep_minutes'=>'decimal:2'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function productType(): BelongsTo { return $this->belongsTo(ProductType::class); }
    public function logs(): HasMany { return $this->hasMany(ExecutionLog::class); }
    public function getTotalPrepHoursAttribute(): float { return round($this->total_prep_minutes/60,2); }
    public function getRemainingMinutesAttribute(): float {
        if ($this->status==='completed') return 0;
        return (float)$this->total_prep_minutes;
    }
}