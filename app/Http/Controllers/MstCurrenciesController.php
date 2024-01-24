<?php

namespace App\Http\Controllers;

use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Browser;

// Model
use App\Models\MstCurrencies;

class MstCurrenciesController extends Controller
{
    use AuditLogsTrait;

    public function index(){
        $currencies = MstCurrencies::get();
        
        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Mst Currency';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('currency.index',compact('currencies'));
    }
    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'code' => 'required',
            'currency' => 'required',
        ]);

        $count= MstCurrencies::where('currency',$request->currency)->count();
        
        if($count > 0){
            return redirect()->back()->with('warning','Currency Was Already Registered');
        } else {
            DB::beginTransaction();
            try{
                $data = MstCurrencies::create([
                    'currency_code' => $request->code,
                    'currency' => $request->currency,
                    'is_active' => '1'
                ]);

                //Audit Log
                $username= auth()->user()->email; 
                $ipAddress=$_SERVER['REMOTE_ADDR'];
                $location='0';
                $access_from=Browser::browserName();
                $activity='Create New Currency ('. $request->currency . ')';
                $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                DB::commit();

                return redirect()->back()->with(['success' => 'Success Create New Currency']);
            } catch (\Exception $e) {
                dd($e);
                return redirect()->back()->with(['fail' => 'Failed to Create New Currency!']);
            }
        }
    }

    public function update(Request $request, $id){
        // dd($request->all());

        $id = decrypt($id);

        $request->validate([
            'code' => 'required',
            'currency' => 'required',
        ]);

        $databefore = MstCurrencies::where('id', $id)->first();
        $databefore->currency_code = $request->code;
        $databefore->currency = $request->currency;

        if($databefore->isDirty()){
            $count= MstCurrencies::where('currency',$request->currency)->whereNotIn('id', [$id])->count();
            if($count > 0){
                return redirect()->back()->with('warning','Currency Was Already Registered');
            } else {
                DB::beginTransaction();
                try{
                    $data = MstCurrencies::where('id', $id)->update([
                        'currency_code' => $request->code,
                        'currency' => $request->currency
                    ]);

                    //Audit Log
                    $username= auth()->user()->email; 
                    $ipAddress=$_SERVER['REMOTE_ADDR'];
                    $location='0';
                    $access_from=Browser::browserName();
                    $activity='Update Currency ('. $request->currency . ')';
                    $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                    DB::commit();
                    return redirect()->back()->with(['success' => 'Success Update Currency']);
                } catch (\Exception $e) {
                    dd($e);
                    return redirect()->back()->with(['fail' => 'Failed to Update Currency!']);
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
            $data = MstCurrencies::where('id', $id)->update([
                'is_active' => 1
            ]);

            $name = MstCurrencies::where('id', $id)->first();

            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Activate Currency ('. $name->currency . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Activate Currency ' . $name->currency]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Activate Currency ' . $name->currency .'!']);
        }
    }

    public function deactivate($id){
        $id = decrypt($id);

        DB::beginTransaction();
        try{
            $data = MstCurrencies::where('id', $id)->update([
                'is_active' => 0
            ]);

            $name = MstCurrencies::where('id', $id)->first();
            
            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Deactivate Currency ('. $name->currency . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Deactivate Currency ' . $name->currency]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Deactivate Currency ' . $name->currency .'!']);
        }
    }
}
