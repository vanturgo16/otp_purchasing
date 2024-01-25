<?php

namespace App\Http\Controllers;

use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Browser;

// Model
use App\Models\MstProcessProductions;

class MstProcessProductionsController extends Controller
{
    use AuditLogsTrait;

    public function index(){
        $datas = MstProcessProductions::get();
        
        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Mst Process Production';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('processproduction.index',compact('datas'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'process_code' => 'required',
            'process' => 'required',
            'result_location_code' => 'required',
        ]);

        $count= MstProcessProductions::where('process',$request->process)->count();
        
        if($count > 0){
            return redirect()->back()->with('warning','Process Production Was Already Registered');
        } else {
            DB::beginTransaction();
            try{
                $data = MstProcessProductions::create([
                    'process_code' => $request->process_code,
                    'process' => $request->process,
                    'result_location_code' => $request->result_location_code,
                    'status' => 'Active'
                ]);

                //Audit Log
                $username= auth()->user()->email; 
                $ipAddress=$_SERVER['REMOTE_ADDR'];
                $location='0';
                $access_from=Browser::browserName();
                $activity='Create New Process Production ('. $request->process . ')';
                $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                DB::commit();

                return redirect()->back()->with(['success' => 'Success Create New Process Production']);
            } catch (\Exception $e) {
                dd($e);
                return redirect()->back()->with(['fail' => 'Failed to Create New Process Production!']);
            }
        }
    }

    public function update(Request $request, $id){
        // dd($request->all());

        $id = decrypt($id);

        $request->validate([
            'process_code' => 'required',
            'process' => 'required',
            'result_location_code' => 'required',
        ]);

        $databefore = MstProcessProductions::where('id', $id)->first();
        $databefore->process_code = $request->process_code;
        $databefore->process = $request->process;
        $databefore->result_location_code = $request->result_location_code;

        if($databefore->isDirty()){
            $count= MstProcessProductions::where('process',$request->process)->whereNotIn('id', [$id])->count();
            if($count > 0){
                return redirect()->back()->with('warning','Process Production Was Already Registered');
            } else {
                DB::beginTransaction();
                try{
                    $data = MstProcessProductions::where('id', $id)->update([
                        'process_code' => $request->process_code,
                        'process' => $request->process,
                        'result_location_code' => $request->result_location_code,
                    ]);

                    //Audit Log
                    $username= auth()->user()->email; 
                    $ipAddress=$_SERVER['REMOTE_ADDR'];
                    $location='0';
                    $access_from=Browser::browserName();
                    $activity='Update Process Production ('. $request->process . ')';
                    $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                    DB::commit();
                    return redirect()->back()->with(['success' => 'Success Update Process Production']);
                } catch (\Exception $e) {
                    dd($e);
                    return redirect()->back()->with(['fail' => 'Failed to Update Process Production!']);
                }
            }
        } else {
            return redirect()->back()->with(['info' => 'Nothing Change, The data entered is the same as the previous one!']);
        }
    }

    public function activate($id){
        $id = decrypt($id);

        DB::beginTransaction();
        try{
            $data = MstProcessProductions::where('id', $id)->update([
                'status' => 'Active'
            ]);

            $name = MstProcessProductions::where('id', $id)->first();

            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Activate Process Production ('. $name->process . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Activate Process Production ' . $name->process]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Activate Process Production ' . $name->process .'!']);
        }
    }

    public function deactivate($id){
        $id = decrypt($id);

        DB::beginTransaction();
        try{
            $data = MstProcessProductions::where('id', $id)->update([
                'status' => 'Innactive'
            ]);

            $name = MstProcessProductions::where('id', $id)->first();
            
            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Deactivate Process Production ('. $name->process . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Deactivate Process Production ' . $name->process]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Deactivate Process Production ' . $name->process .'!']);
        }
    }
}
