<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\User;
use App\Services\WorkloadService;
class DelayController extends Controller {
    protected function user(){ return User::firstOrCreate(['email'=>'owner@watira.local'],['name'=>'Owner','password'=>bcrypt('password')]); }
    public function index(WorkloadService $ws){
        $u=$this->user(); $s=$ws->settings($u);
        $orders=Order::where('user_id',$u->id)->where('status','!=','completed')->get();
        $list=[];
        foreach($orders as $o){ $o->risk_level=$ws->risk($o,$s->daily_production_hours); if(in_array($o->risk_level,['medium','high'])) $list[]=$o; }
        return view('delays.index', compact('list','s'));
    }
}