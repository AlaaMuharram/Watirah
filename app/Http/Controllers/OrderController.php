<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\ProductType;
use App\Models\User;
use App\Models\ExecutionLog;
use Illuminate\Http\Request;

class OrderController extends Controller {
    protected function user(){ return User::firstOrCreate(['email'=>'owner@watira.local'],['name'=>'Owner','password'=>bcrypt('password')]); }
    public function index(Request $r){
        $u=$this->user();
        $q=Order::where('user_id',$u->id);
        if($r->search) $q->where(function($x)use($r){$x->where('customer_name','like','%'.$r->search.'%')->orWhere('order_number','like','%'.$r->search.'%');});
        if($r->status) $q->where('status',$r->status);
        $orders=$q->orderBy('due_date')->get();
        return view('orders.index', compact('orders'));
    }
    public function create(){ $u=$this->user(); $types=ProductType::where('user_id',$u->id)->get(); return view('orders.create', compact('types')); }
    public function store(Request $r){
        $u=$this->user();
        $data=$r->validate(['customer_name'=>'required','product_name'=>'required','quantity'=>'required|integer|min:1','unit_prep_minutes'=>'required|numeric|min:0','due_date'=>'nullable|date','received_date'=>'nullable|date','description'=>'nullable','notes'=>'nullable','status'=>'nullable']);
        $data['user_id']=$u->id;
        $data['order_number']='ORD-'.str_pad(Order::count()+1,4,'0',STR_PAD_LEFT);
        $data['total_prep_minutes']=$data['unit_prep_minutes']*$data['quantity'];
        $data['status']=$data['status']??'new';
        Order::create($data);
        return redirect()->route('orders.index')->with('success',__('Saved'));
    }
    public function show(Order $order){ return view('orders.show', compact('order')); }
    public function edit(Order $order){ $u=$this->user(); $types=ProductType::where('user_id',$u->id)->get(); return view('orders.edit', compact('order','types')); }
    public function update(Request $r, Order $order){
        $data=$r->validate(['customer_name'=>'required','product_name'=>'required','quantity'=>'required|integer|min:1','unit_prep_minutes'=>'required|numeric|min:0','due_date'=>'nullable|date','received_date'=>'nullable|date','description'=>'nullable','notes'=>'nullable','status'=>'nullable']);
        $data['total_prep_minutes']=$data['unit_prep_minutes']*$data['quantity'];
        if(isset($data['status']) && $data['status']!==$order->status){
            ExecutionLog::create(['order_id'=>$order->id,'from_status'=>$order->status,'to_status'=>$data['status']]);
        }
        $order->update($data);
        return redirect()->route('orders.index')->with('success',__('Saved'));
    }
    public function destroy(Order $order){ $order->delete(); return redirect()->route('orders.index')->with('success',__('Deleted')); }
}