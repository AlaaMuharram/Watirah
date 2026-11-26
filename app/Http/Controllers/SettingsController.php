<?php
namespace App\Http\Controllers;
use App\Models\ProjectSetting;
use App\Models\User;
use App\Services\WorkloadService;
use Illuminate\Http\Request;
class SettingsController extends Controller {
    protected function user(){ return User::firstOrCreate(['email'=>'owner@watira.local'],['name'=>'Owner','password'=>bcrypt('password')]); }
    public function index(WorkloadService $ws){ $u=$this->user(); $s=$ws->settings($u); return view('settings.index', compact('s')); }
    public function store(Request $r, WorkloadService $ws){
        $u=$this->user();
        $s=$ws->settings($u);
        $s->update($r->only('project_name','work_days','work_start','work_end','daily_production_hours','break_hours','locale'));
        if($r->locale) session(['locale'=>$r->locale]);
        return back()->with('success',__('Saved'));
    }
}