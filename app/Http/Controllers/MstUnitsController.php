<?php

namespace App\Http\Controllers;

use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Browser;

// Model
use App\Models\MstUnits;

class MstUnitsController extends Controller
{
    use AuditLogsTrait;

    public function index(){
        $datas = MstUnits::get();
        
        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Mst Unit';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('unit.index',compact('datas'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'unit_code' => 'required',
            'unit' => 'required',
        ]);

        $count= MstUnits::where('unit',$request->unit)->count();
        
        if($count > 0){
            return redirect()->back()->with('warning','Unit Was Already Registered');
        } else {
            DB::beginTransaction();
            try{
                $data = MstUnits::create([
                    'unit_code' => $request->unit_code,
                    'unit' => $request->unit,
                    'is_active' => '1'
                ]);

                //Audit Log
                $username= auth()->user()->email; 
                $ipAddress=$_SERVER['REMOTE_ADDR'];
                $location='0';
                $access_from=Browser::browserName();
                $activity='Create New Unit ('. $request->unit . ')';
                $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                DB::commit();

                return redirect()->back()->with(['success' => 'Success Create New Unit']);
            } catch (\Exception $e) {
                dd($e);
                return redirect()->back()->with(['fail' => 'Failed to Create New Unit!']);
            }
        }
    }

    public function update(Request $request, $id){
        // dd($request->all());

        $id = decrypt($id);

        $request->validate([
            'unit_code' => 'required',
            'unit' => 'required',
        ]);

        $databefore = MstUnits::where('id', $id)->first();
        $databefore->unit_code = $request->unit_code;
        $databefore->unit = $request->unit;

        if($databefore->isDirty()){
            $count= MstUnits::where('unit',$request->unit)->whereNotIn('id', [$id])->count();
            if($count > 0){
                return redirect()->back()->with('warning','Unit Was Already Registered');
            } else {
                DB::beginTransaction();
                try{
                    $data = MstUnits::where('id', $id)->update([
                        'unit_code' => $request->unit_code,
                        'unit' => $request->unit,
                    ]);

                    //Audit Log
                    $username= auth()->user()->email; 
                    $ipAddress=$_SERVER['REMOTE_ADDR'];
                    $location='0';
                    $access_from=Browser::browserName();
                    $activity='Update Unit ('. $request->unit . ')';
                    $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                    DB::commit();
                    return redirect()->back()->with(['success' => 'Success Update Unit']);
                } catch (\Exception $e) {
                    dd($e);
                    return redirect()->back()->with(['fail' => 'Failed to Update Unit!']);
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
            $data = MstUnits::where('id', $id)->update([
                'is_active' => 1
            ]);

            $name = MstUnits::where('id', $id)->first();

            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Activate Unit ('. $name->unit . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Activate Unit ' . $name->unit]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Activate Unit ' . $name->unit .'!']);
        }
    }

    public function deactivate($id){
        $id = decrypt($id);

        DB::beginTransaction();
        try{
            $data = MstUnits::where('id', $id)->update([
                'is_active' => 0
            ]);

            $name = MstUnits::where('id', $id)->first();
            
            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Deactivate Unit ('. $name->unit . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Deactivate Unit ' . $name->unit]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Deactivate Unit ' . $name->unit .'!']);
        }
    }
}
