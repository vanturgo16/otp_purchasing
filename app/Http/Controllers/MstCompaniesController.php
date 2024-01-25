<?php

namespace App\Http\Controllers;

use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Browser;

// Model
use App\Models\User;
use App\Models\MstCompanies;
use App\Models\MstProvinces;
use App\Models\MstCountries;
use App\Models\MstCurrencies;

class MstCompaniesController extends Controller
{
    use AuditLogsTrait;

    public function index(){
        $companies = MstCompanies::select('master_companies.*', 'master_provinces.province', 'master_countries.country', 'master_currencies.currency')
            ->leftjoin('master_provinces', 'master_companies.id_master_provinces', '=', 'master_provinces.id')
            ->leftjoin('master_countries', 'master_companies.id_master_countries', '=', 'master_countries.id')
            ->leftjoin('master_currencies', 'master_companies.id_master_currencies', '=', 'master_currencies.id')
            ->get();
        // dd($companies);

        $provinces = MstProvinces::where('is_active', 1)->get();
        $countries = MstCountries::where('is_active', 1)->get();
        $currencies = MstCurrencies::where('is_active', 1)->get();

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Mst Company';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);
        
        return view('company.index', compact('companies', 'provinces', 'countries', 'currencies'));
    }
    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'company_name' => 'required',
            'address' => 'required',
            'city' => 'required',
            'id_master_provinces' => 'required',
            'id_master_countries' => 'required',
            'postal_code' => 'required',
            'telephone' => 'required',
            'mobile_phone' => 'required',
            'fax' => 'required',
            'email' => 'required',
            'website' => 'required',
            'penandatanganan' => 'required',
            'id_master_currencies' => 'required',
            'tax_no' => 'required',
        ]);

        $count= MstCompanies::where('company_name',$request->company_name)->count();
        
        if($count > 0){
            return redirect()->back()->with('warning','Company Was Already Registered');
        } else {
            DB::beginTransaction();
            try{
                $data = MstCompanies::create([
                    'company_name' => $request->company_name,
                    'address' => $request->address,
                    'city' => $request->city,
                    'id_master_provinces' => $request->id_master_provinces,
                    'id_master_countries' => $request->id_master_countries,
                    'postal_code' => $request->postal_code,
                    'telephone' => $request->telephone,
                    'mobile_phone' => $request->mobile_phone,
                    'fax' => $request->fax,
                    'email' => $request->email,
                    'website' => $request->website,
                    'penandatanganan' => $request->penandatanganan,
                    'id_master_currencies' => $request->id_master_currencies,
                    'tax_no' => $request->tax_no,
                    'is_active' => '1'
                ]);

                //Audit Log
                $username= auth()->user()->email; 
                $ipAddress=$_SERVER['REMOTE_ADDR'];
                $location='0';
                $access_from=Browser::browserName();
                $activity='Create New Company ('. $request->company_name . ')';
                $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                DB::commit();

                return redirect()->back()->with(['success' => 'Success Create New Company']);
            } catch (\Exception $e) {
                dd($e);
                return redirect()->back()->with(['fail' => 'Failed to Create New Company!']);
            }
        }
    }

    public function update(Request $request, $id){
        // dd($request->all());

        $id = decrypt($id);

        $request->validate([
            'company_name' => 'required',
            'address' => 'required',
            'city' => 'required',
            'id_master_provinces' => 'required',
            'id_master_countries' => 'required',
            'postal_code' => 'required',
            'telephone' => 'required',
            'mobile_phone' => 'required',
            'fax' => 'required',
            'email' => 'required',
            'website' => 'required',
            'penandatanganan' => 'required',
            'id_master_currencies' => 'required',
            'tax_no' => 'required',
        ]);

        $databefore = MstCompanies::where('id', $id)->first();
        $databefore->company_name = $request->company_name;
        $databefore->address = $request->address;
        $databefore->city = $request->city;
        $databefore->id_master_provinces = $request->id_master_provinces;
        $databefore->id_master_countries = $request->id_master_countries;
        $databefore->postal_code = $request->postal_code;
        $databefore->telephone = $request->telephone;
        $databefore->mobile_phone = $request->mobile_phone;
        $databefore->fax = $request->fax;
        $databefore->email = $request->email;
        $databefore->website = $request->website;
        $databefore->penandatanganan = $request->penandatanganan;
        $databefore->id_master_currencies = $request->id_master_currencies;
        $databefore->tax_no = $request->tax_no;

        if($databefore->isDirty()){
            $count= MstCompanies::where('company_name',$request->company_name)->whereNotIn('id', [$id])->count();
            if($count > 0){
                return redirect()->back()->with('warning','Company Was Already Registered');
            } else {
                DB::beginTransaction();
                try{
                    $data = MstCompanies::where('id', $id)->update([
                        'company_name' => $request->company_name,
                        'address' => $request->address,
                        'city' => $request->city,
                        'id_master_provinces' => $request->id_master_provinces,
                        'id_master_countries' => $request->id_master_countries,
                        'postal_code' => $request->postal_code,
                        'telephone' => $request->telephone,
                        'mobile_phone' => $request->mobile_phone,
                        'fax' => $request->fax,
                        'email' => $request->email,
                        'website' => $request->website,
                        'penandatanganan' => $request->penandatanganan,
                        'id_master_currencies' => $request->id_master_currencies,
                        'tax_no' => $request->tax_no
                    ]);

                    //Audit Log
                    $username= auth()->user()->email; 
                    $ipAddress=$_SERVER['REMOTE_ADDR'];
                    $location='0';
                    $access_from=Browser::browserName();
                    $activity='Update Company ('. $request->company_name . ')';
                    $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                    DB::commit();
                    return redirect()->back()->with(['success' => 'Success Update Company']);
                } catch (\Exception $e) {
                    dd($e);
                    return redirect()->back()->with(['fail' => 'Failed to Update Company!']);
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
            $data = MstCompanies::where('id', $id)->update([
                'is_active' => 1
            ]);

            $name = MstCompanies::where('id', $id)->first();

            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Activate Company ('. $name->company_name . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Activate Company ' . $name->company_name]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Activate Company ' . $name->company_name .'!']);
        }
    }

    public function deactivate($id){
        $id = decrypt($id);

        DB::beginTransaction();
        try{
            $data = MstCompanies::where('id', $id)->update([
                'is_active' => 0
            ]);

            $name = MstCompanies::where('id', $id)->first();
            
            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Deactivate Company ('. $name->company_name . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Deactivate Company ' . $name->company_name]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Deactivate Company ' . $name->company_name .'!']);
        }
    }
}
