<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ProjectSetting;
use App\Models\ProductType;
use App\Models\Order;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        $u = User::firstOrCreate(['email'=>'owner@watira.local'],['name'=>'Owner','password'=>bcrypt('password')]);
        ProjectSetting::firstOrCreate(['user_id'=>$u->id],['project_name'=>'WATIRA','work_days'=>['sun','mon','tue','wed','thu'],'work_start'=>'09:00','work_end'=>'17:00','daily_production_hours'=>6,'break_hours'=>1,'locale'=>'ar']);
        $pt = ProductType::create(['user_id'=>$u->id,'name'=>'هدية مخصصة','unit_prep_minutes'=>45]);
        ProductType::create(['user_id'=>$u->id,'name'=>'توزيعات','unit_prep_minutes'=>20]);
        $names=['أحمد','فاطمة','محمد','سارة','خالد','نورة','عبدالله','ريم'];
        $products=['علبة هدايا','توزيعات زفاف','باقة ورد','شموع','مagnet','لوحة خشبية','كرت دعوة','صندوق هدية'];
        for($i=0;$i<8;$i++){
            Order::create(['user_id'=>$u->id,'product_type_id'=>$pt->id,'order_number'=>'ORD-'.str_pad($i+1,4,'0',STR_PAD_LEFT),'customer_name'=>$names[$i],'product_name'=>$products[$i],'quantity'=>rand(1,5),'unit_prep_minutes'=>rand(20,60),'total_prep_minutes'=>0,'due_date'=>now()->addDays(rand(2,14))->toDateString(),'received_date'=>now()->toDateString(),'status'=>collect(['new','pending','in_progress'])->random()]);
        }
        foreach(Order::all() as $o){ $o->total_prep_minutes=$o->unit_prep_minutes*$o->quantity; $o->save(); }
    }
}