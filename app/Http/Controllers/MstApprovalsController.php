<?php

namespace App\Http\Controllers;

use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Browser;

// Model
use App\Models\MstApprovals;

class MstApprovalsController extends Controller
{
    use AuditLogsTrait;

    public function index(){
        $datas = MstApprovals::get();
        
        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Mst Approval';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('approval.index',compact('datas'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'type' => 'required',
            'id_master_employees' => 'required',
        ]);

        DB::beginTransaction();
        try{
            $data = MstApprovals::create([
                'type' => $request->type,
                'id_master_employees' => $request->id_master_employees,
                'status' => 'Active'
            ]);

            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Create New Approval ('. $request->type . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();

            return redirect()->back()->with(['success' => 'Success Create New Approval']);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Create New Approval!']);
        }
    }

    public function update(Request $request, $id){
        // dd($request->all());

        $id = decrypt($id);

        $request->validate([
            'type' => 'required',
            'id_master_employees' => 'required',
        ]);

        $databefore = MstApprovals::where('id', $id)->first();
        $databefore->type = $request->type;
        $databefore->id_master_employees = $request->id_master_employees;

        if($databefore->isDirty()){
            DB::beginTransaction();
            try{
                $data = MstApprovals::where('id', $id)->update([
                    'type' => $request->type,
                    'id_master_employees' => $request->id_master_employees,
                ]);

                //Audit Log
                $username= auth()->user()->email; 
                $ipAddress=$_SERVER['REMOTE_ADDR'];
                $location='0';
                $access_from=Browser::browserName();
                $activity='Update Approval ('. $request->type . ')';
                $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                DB::commit();
                return redirect()->back()->with(['success' => 'Success Update Approval']);
            } catch (\Exception $e) {
                dd($e);
                return redirect()->back()->with(['fail' => 'Failed to Update Approval!']);
            }
        } else {
            return redirect()->back()->with(['info' => 'Nothing Change, The data entered is the same as the previous one!']);
        }
    }

    public function activate($id){
        $id = decrypt($id);

        DB::beginTransaction();
        try{
            $data = MstApprovals::where('id', $id)->update([
                'status' => "Active"
            ]);

            $name = MstApprovals::where('id', $id)->first();

            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Activate Approval ('. $name->type . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Activate Approval ' . $name->type]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Activate Approval ' . $name->type .'!']);
        }
    }

    public function deactivate($id){
        $id = decrypt($id);

        DB::beginTransaction();
        try{
            $data = MstApprovals::where('id', $id)->update([
                'status' => "Innactive"
            ]);

            $name = MstApprovals::where('id', $id)->first();
            
            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Deactivate Approval ('. $name->type . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Deactivate Approval ' . $name->type]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Deactivate Approval ' . $name->type .'!']);
        }
    }
}
