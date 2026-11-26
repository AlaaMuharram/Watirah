<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\ProjectSetting;
use App\Models\User;
use App\Services\WorkloadService;

class DashboardController extends Controller {
    protected function user(){ return User::firstOrCreate(['email'=>'owner@watira.local'],['name'=>'Owner','password'=>bcrypt('password')]); }
    public function index(WorkloadService $ws){
        $u=$this->user();
        $s=$ws->settings($u);
        $orders=Order::where('user_id',$u->id)->get();
        $required=$ws->requiredHours($orders);
        $available=$ws->availableTotalDays(30,$s);
        $util = $available>0 ? round($required/$available*100,1) : 0;
        foreach($orders as $o){ $o->risk_level=$ws->risk($o,$s->daily_production_hours); }
        return view('dashboard', compact('orders','s','required','available','util'));
    }
}