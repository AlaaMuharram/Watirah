<?php
namespace App\Services;
use App\Models\Order;
use App\Models\ProjectSetting;
use App\Models\User;

class WorkloadService {
    public function settings(User $u): ProjectSetting {
        return ProjectSetting::firstOrCreate(['user_id'=>$u->id], [
            'project_name'=>'WATIRA','work_days'=>['sun','mon','tue','wed','thu'],
            'work_start'=>'09:00','work_end'=>'17:00','daily_production_hours'=>6,'break_hours'=>1,'locale'=>'ar'
        ]);
    }
    public function availableHours(ProjectSetting $s): float { return (float)$s->daily_production_hours; }
    public function requiredHours($orders): float {
        $m = 0; foreach($orders as $o){ if($o->status!=='completed') $m += (float)$o->total_prep_minutes; }
        return round($m/60,2);
    }
    public function availableTotalDays(int $days, ProjectSetting $s): float {
        $workDays = $s->work_days ?? ['sun','mon','tue','wed','thu'];
        $map = ['sun'=>0,'mon'=>1,'tue'=>2,'wed'=>3,'thu'=>4,'fri'=>5,'sat'=>6];
        $allowed = array_map(fn($d)=>$map[$d]??0, $workDays);
        $total=0;
        for($i=0;$i<$days;$i++){ $d=date('w',strtotime("+$i days")); if(in_array((int)$d,$allowed)) $total += (float)$s->daily_production_hours; }
        return round($total,2);
    }
    public function risk(Order $o, float $availableHours): string {
        if($o->status==='completed') return 'low';
        $remaining = (float)$o->total_prep_minutes/60;
        $days = max(1, (strtotime($o->due_date)-time())/86400);
        $neededPerDay = $remaining/max(1,$days);
        $cap = $availableHours/max(1,$days);
        if($neededPerDay > $cap*1.3) return 'high';
        if($neededPerDay > $cap) return 'medium';
        return 'low';
    }
}