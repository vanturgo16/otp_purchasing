<?php

namespace App\Http\Controllers;

use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Browser;

// Model
use App\Models\MstVehicles;

class MstVehiclesController extends Controller
{
    use AuditLogsTrait;

    public function index(){
        $datas = MstVehicles::get();
        
        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Mst Vehicle';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('vehicle.index',compact('datas'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'vehicle_number' => 'required',
            'driver' => 'required',
        ]);

        $count= MstVehicles::where('vehicle_number',$request->vehicle_number)->count();
        
        if($count > 0){
            return redirect()->back()->with('warning','Vehicle Was Already Registered');
        } else {
            DB::beginTransaction();
            try{
                $data = MstVehicles::create([
                    'vehicle_number' => $request->vehicle_number,
                    'driver' => $request->driver,
                    'is_active' => '1'
                ]);

                //Audit Log
                $username= auth()->user()->email; 
                $ipAddress=$_SERVER['REMOTE_ADDR'];
                $location='0';
                $access_from=Browser::browserName();
                $activity='Create New Vehicle ('. $request->vehicle_number . ')';
                $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                DB::commit();

                return redirect()->back()->with(['success' => 'Success Create New Vehicle']);
            } catch (\Exception $e) {
                dd($e);
                return redirect()->back()->with(['fail' => 'Failed to Create New Vehicle!']);
            }
        }
    }

    public function update(Request $request, $id){
        // dd($request->all());

        $id = decrypt($id);

        $request->validate([
            'vehicle_number' => 'required',
            'driver' => 'required',
        ]);

        $databefore = MstVehicles::where('id', $id)->first();
        $databefore->vehicle_number = $request->vehicle_number;
        $databefore->driver = $request->driver;

        if($databefore->isDirty()){
            $count= MstVehicles::where('vehicle_number',$request->vehicle_number)->whereNotIn('id', [$id])->count();
            if($count > 0){
                return redirect()->back()->with('warning','Vehicle Was Already Registered');
            } else {
                DB::beginTransaction();
                try{
                    $data = MstVehicles::where('id', $id)->update([
                        'vehicle_number' => $request->vehicle_number,
                        'driver' => $request->driver,
                    ]);

                    //Audit Log
                    $username= auth()->user()->email; 
                    $ipAddress=$_SERVER['REMOTE_ADDR'];
                    $location='0';
                    $access_from=Browser::browserName();
                    $activity='Update Vehicle ('. $request->vehicle_number . ')';
                    $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                    DB::commit();
                    return redirect()->back()->with(['success' => 'Success Update Vehicle']);
                } catch (\Exception $e) {
                    dd($e);
                    return redirect()->back()->with(['fail' => 'Failed to Update Vehicle!']);
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
            $data = MstVehicles::where('id', $id)->update([
                'is_active' => 1
            ]);

            $name = MstVehicles::where('id', $id)->first();

            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Activate Vehicle ('. $name->vehicle_number . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Activate Vehicle ' . $name->vehicle_number]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Activate Vehicle ' . $name->vehicle_number .'!']);
        }
    }

    public function deactivate($id){
        $id = decrypt($id);

        DB::beginTransaction();
        try{
            $data = MstVehicles::where('id', $id)->update([
                'is_active' => 0
            ]);

            $name = MstVehicles::where('id', $id)->first();
            
            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Deactivate Vehicle ('. $name->vehicle_number . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Deactivate Vehicle ' . $name->vehicle_number]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Deactivate Vehicle ' . $name->vehicle_number .'!']);
        }
    }
}
