<?php

namespace App\Http\Controllers;

use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Browser;

// Model
use App\Models\MstProvinces;

class MstProvincesController extends Controller
{
    use AuditLogsTrait;

    public function index(){
        $provinces = MstProvinces::get();
        
        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Mst Province';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('province.index',compact('provinces'));
    }
    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'code' => 'required',
            'province' => 'required',
        ]);

        $count= MstProvinces::where('province',$request->province)->count();
        
        if($count > 0){
            return redirect()->back()->with('warning','Province Was Already Registered');
        } else {
            DB::beginTransaction();
            try{
                $data = MstProvinces::create([
                    'province_code' => $request->code,
                    'province' => $request->province,
                    'is_active' => '1'
                ]);

                //Audit Log
                $username= auth()->user()->email; 
                $ipAddress=$_SERVER['REMOTE_ADDR'];
                $location='0';
                $access_from=Browser::browserName();
                $activity='Create New Province ('. $request->province . ')';
                $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                DB::commit();

                return redirect()->back()->with(['success' => 'Success Create New Province']);
            } catch (\Exception $e) {
                dd($e);
                return redirect()->back()->with(['fail' => 'Failed to Create New Province!']);
            }
        }
    }

    public function update(Request $request, $id){
        // dd($request->all());

        $id = decrypt($id);

        $request->validate([
            'code' => 'required',
            'province' => 'required',
        ]);

        $databefore = MstProvinces::where('id', $id)->first();
        $databefore->province_code = $request->code;
        $databefore->province = $request->province;

        if($databefore->isDirty()){
            $count= MstProvinces::where('province',$request->province)->whereNotIn('id', [$id])->count();
            if($count > 0){
                return redirect()->back()->with('warning','Province Was Already Registered');
            } else {
                DB::beginTransaction();
                try{
                    $data = MstProvinces::where('id', $id)->update([
                        'province_code' => $request->code,
                        'province' => $request->province
                    ]);

                    //Audit Log
                    $username= auth()->user()->email; 
                    $ipAddress=$_SERVER['REMOTE_ADDR'];
                    $location='0';
                    $access_from=Browser::browserName();
                    $activity='Update Province ('. $request->province . ')';
                    $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                    DB::commit();
                    return redirect()->back()->with(['success' => 'Success Update Province']);
                } catch (\Exception $e) {
                    dd($e);
                    return redirect()->back()->with(['fail' => 'Failed to Update Province!']);
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
            $data = MstProvinces::where('id', $id)->update([
                'is_active' => 1
            ]);

            $name = MstProvinces::where('id', $id)->first();

            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Activate Province ('. $name->province . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Activate Province ' . $name->province]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Activate Province ' . $name->province .'!']);
        }
    }

    public function deactivate($id){
        $id = decrypt($id);

        DB::beginTransaction();
        try{
            $data = MstProvinces::where('id', $id)->update([
                'is_active' => 0
            ]);

            $name = MstProvinces::where('id', $id)->first();
            
            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Deactivate Province ('. $name->province . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Deactivate Province ' . $name->province]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Deactivate Province ' . $name->province .'!']);
        }
    }
}
