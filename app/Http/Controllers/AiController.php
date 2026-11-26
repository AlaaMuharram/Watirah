<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
class AiController extends Controller {
    protected function user(){ return User::firstOrCreate(['email'=>'owner@watira.local'],['name'=>'Owner','password'=>bcrypt('password')]); }
    public function index(){ return view('ai.index'); }
    public function extract(Request $r){
        $text=$r->text;
        // Simple local extraction (demo)
        preg_match('/(\d+)\s*(?:قطعة|pieces|pcs)/iu', $text, $q);
        preg_match('/(?:اسم|name)[:：]?\s*([^\n]+)/iu', $text, $n);
        preg_match('/(?:منتج|product)[:：]?\s*([^\n]+)/iu', $text, $p);
        preg_match('/(\d{4}-\d{2}-\d{2})/', $text, $d);
        $data=['customer_name'=>$n[1]??'','product_name'=>$p[1]??'','quantity'=>$q[1]??1,'due_date'=>$d[1]??'','description'=>$text];
        return view('ai.index', ['data' => $data, 'text' => $text]);
    }
}
