<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\User;
use App\Services\WorkloadService;
class ScheduleController extends Controller {
    protected function user(){ return User::firstOrCreate(['email'=>'owner@watira.local'],['name'=>'Owner','password'=>bcrypt('password')]); }
    public function index(WorkloadService $ws){
        $u=$this->user(); $s=$ws->settings($u);
        $orders=Order::where('user_id',$u->id)->where('status','!=','completed')->orderBy('due_date')->get();
        return view('schedule.index', compact('orders','s'));
    }
}