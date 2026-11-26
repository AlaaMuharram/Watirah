<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\User;
use App\Services\WorkloadService;
use Illuminate\Http\Request;
class SimulatorController extends Controller {
    protected function user(){ return User::firstOrCreate(['email'=>'owner@watira.local'],['name'=>'Owner','password'=>bcrypt('password')]); }
    public function index(){ return view('simulator.index'); }
    public function run(Request $r, WorkloadService $ws){
        $u=$this->user(); $s=$ws->settings($u);
        $orders=Order::where('user_id',$u->id)->where('status','!=','completed')->get();
        $before=$ws->requiredHours($orders);
        $newMinutes=($r->unit_prep_minutes)*($r->quantity);
        $after=$before+$newMinutes/60;
        $available=$ws->availableTotalDays(30,$s);
        $utilBefore=$available>0?round($before/$available*100,1):0;
        $utilAfter=$available>0?round($after/$available*100,1):0;
        $decision = $utilAfter>100 ? 'unsuitable' : ($utilAfter>85 ? 'conditional' : 'suitable');
        return view('simulator.result', compact('before','after','utilBefore','utilAfter','available','decision','r'));
    }
    public function accept(Request $r){
        $u=$this->user();
        Order::create(['user_id'=>$u->id,'order_number'=>'ORD-'.str_pad(Order::count()+1,4,'0',STR_PAD_LEFT),'customer_name'=>$r->customer_name,'product_name'=>$r->product_name,'quantity'=>$r->quantity,'unit_prep_minutes'=>$r->unit_prep_minutes,'total_prep_minutes'=>$r->unit_prep_minutes*$r->quantity,'due_date'=>$r->due_date,'status'=>'new']);
        return redirect()->route('orders.index')->with('success',__('Saved'));
    }
}