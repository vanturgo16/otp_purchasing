<?php

namespace App\Http\Controllers;

use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Browser;

// Model
use App\Models\MstReasons;

class MstReasonsController extends Controller
{
    use AuditLogsTrait;

    public function index(){
        $datas = MstReasons::get();
        
        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Mst Reason';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('reason.index',compact('datas'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'reason_code' => 'required',
            'reason' => 'required',
        ]);

        $count= MstReasons::where('reason',$request->reason)->count();
        
        if($count > 0){
            return redirect()->back()->with('warning','Reason Was Already Registered');
        } else {
            DB::beginTransaction();
            try{
                $data = MstReasons::create([
                    'reason_code' => $request->reason_code,
                    'reason' => $request->reason,
                    'is_active' => '1'
                ]);

                //Audit Log
                $username= auth()->user()->email; 
                $ipAddress=$_SERVER['REMOTE_ADDR'];
                $location='0';
                $access_from=Browser::browserName();
                $activity='Create New Reason ('. $request->reason . ')';
                $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                DB::commit();

                return redirect()->back()->with(['success' => 'Success Create New Reason']);
            } catch (\Exception $e) {
                dd($e);
                return redirect()->back()->with(['fail' => 'Failed to Create New Reason!']);
            }
        }
    }

    public function update(Request $request, $id){
        // dd($request->all());

        $id = decrypt($id);

        $request->validate([
            'reason_code' => 'required',
            'reason' => 'required',
        ]);

        $databefore = MstReasons::where('id', $id)->first();
        $databefore->reason_code = $request->reason_code;
        $databefore->reason = $request->reason;

        if($databefore->isDirty()){
            $count= MstReasons::where('reason',$request->reason)->whereNotIn('id', [$id])->count();
            if($count > 0){
                return redirect()->back()->with('warning','Reason Was Already Registered');
            } else {
                DB::beginTransaction();
                try{
                    $data = MstReasons::where('id', $id)->update([
                        'reason_code' => $request->reason_code,
                        'reason' => $request->reason,
                    ]);

                    //Audit Log
                    $username= auth()->user()->email; 
                    $ipAddress=$_SERVER['REMOTE_ADDR'];
                    $location='0';
                    $access_from=Browser::browserName();
                    $activity='Update Reason ('. $request->reason . ')';
                    $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                    DB::commit();
                    return redirect()->back()->with(['success' => 'Success Update Reason']);
                } catch (\Exception $e) {
                    dd($e);
                    return redirect()->back()->with(['fail' => 'Failed to Update Reason!']);
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
            $data = MstReasons::where('id', $id)->update([
                'is_active' => 1
            ]);

            $name = MstReasons::where('id', $id)->first();

            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Activate Reason ('. $name->reason . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Activate Reason ' . $name->reason]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Activate Reason ' . $name->reason .'!']);
        }
    }

    public function deactivate($id){
        $id = decrypt($id);

        DB::beginTransaction();
        try{
            $data = MstReasons::where('id', $id)->update([
                'is_active' => 0
            ]);

            $name = MstReasons::where('id', $id)->first();
            
            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Deactivate Reason ('. $name->reason . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Deactivate Reason ' . $name->reason]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Deactivate Reason ' . $name->reason .'!']);
        }
    }
}
