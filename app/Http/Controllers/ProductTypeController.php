<?php
namespace App\Http\Controllers;
use App\Models\ProductType;
use App\Models\User;
use Illuminate\Http\Request;
class ProductTypeController extends Controller {
    protected function user(){ return User::firstOrCreate(['email'=>'owner@watira.local'],['name'=>'Owner','password'=>bcrypt('password')]); }
    public function index(){ $u=$this->user(); $types=ProductType::where('user_id',$u->id)->get(); return view('product_types.index', compact('types')); }
    public function store(Request $r){ $u=$this->user(); ProductType::create(['user_id'=>$u->id,'name'=>$r->name,'unit_prep_minutes'=>$r->unit_prep_minutes]); return back()->with('success',__('Saved')); }
    public function update(Request $r, ProductType $productType){ $productType->update($r->only('name','unit_prep_minutes')); return back()->with('success',__('Saved')); }
    public function destroy(ProductType $productType){ $productType->delete(); return back()->with('success',__('Deleted')); }
}