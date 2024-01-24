<?php

namespace App\Http\Controllers;

use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Browser;

// Model
use App\Models\MstCountries;

class MstCountriesController extends Controller
{
    use AuditLogsTrait;

    public function index(){
        $countries = MstCountries::get();
        
        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Mst Country';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('country.index',compact('countries'));
    }
    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'code' => 'required',
            'country' => 'required',
        ]);

        $count= MstCountries::where('country',$request->country)->count();
        
        if($count > 0){
            return redirect()->back()->with('warning','Country Was Already Registered');
        } else {
            DB::beginTransaction();
            try{
                $data = MstCountries::create([
                    'country_code' => $request->code,
                    'country' => $request->country,
                    'is_active' => '1'
                ]);

                //Audit Log
                $username= auth()->user()->email; 
                $ipAddress=$_SERVER['REMOTE_ADDR'];
                $location='0';
                $access_from=Browser::browserName();
                $activity='Create New Country ('. $request->country . ')';
                $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                DB::commit();

                return redirect()->back()->with(['success' => 'Success Create New Country']);
            } catch (\Exception $e) {
                dd($e);
                return redirect()->back()->with(['fail' => 'Failed to Create New Country!']);
            }
        }
    }

    public function update(Request $request, $id){
        // dd($request->all());

        $id = decrypt($id);

        $request->validate([
            'code' => 'required',
            'country' => 'required',
        ]);

        $databefore = MstCountries::where('id', $id)->first();
        $databefore->country_code = $request->code;
        $databefore->country = $request->country;

        if($databefore->isDirty()){
            $count= MstCountries::where('country',$request->country)->whereNotIn('id', [$id])->count();
            if($count > 0){
                return redirect()->back()->with('warning','Country Was Already Registered');
            } else {
                DB::beginTransaction();
                try{
                    $data = MstCountries::where('id', $id)->update([
                        'country_code' => $request->code,
                        'country' => $request->country
                    ]);

                    //Audit Log
                    $username= auth()->user()->email; 
                    $ipAddress=$_SERVER['REMOTE_ADDR'];
                    $location='0';
                    $access_from=Browser::browserName();
                    $activity='Update Country ('. $request->country . ')';
                    $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                    DB::commit();
                    return redirect()->back()->with(['success' => 'Success Update Country']);
                } catch (\Exception $e) {
                    dd($e);
                    return redirect()->back()->with(['fail' => 'Failed to Update Country!']);
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
            $data = MstCountries::where('id', $id)->update([
                'is_active' => 1
            ]);

            $name = MstCountries::where('id', $id)->first();

            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Activate Country ('. $name->country . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Activate Country ' . $name->country]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Activate Country ' . $name->country .'!']);
        }
    }

    public function deactivate($id){
        $id = decrypt($id);

        DB::beginTransaction();
        try{
            $data = MstCountries::where('id', $id)->update([
                'is_active' => 0
            ]);

            $name = MstCountries::where('id', $id)->first();
            
            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Deactivate Country ('. $name->country . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Deactivate Country ' . $name->country]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Deactivate Country ' . $name->country .'!']);
        }
    }
}