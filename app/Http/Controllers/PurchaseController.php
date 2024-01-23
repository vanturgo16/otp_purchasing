<?php

namespace App\Http\Controllers;

use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use RealRashid\SweetAlert\Facades\Alert;
use Browser;

// Model
use App\Models\PurchaseRequisitions;
use App\Models\PurchaseOrders;
use App\Models\MstRequester;
use App\Models\MstSupplier;
use App\Models\PurchaseRequisitionsDetail;
use App\Models\PurchaseRequisitionsDetailSmt;

class PurchaseController extends Controller
{
    use AuditLogsTrait;

    public function index(){
        $datas = PurchaseRequisitions::leftJoin('master_suppliers as b', 'purchase_requisitions.id_master_suppliers', '=', 'b.id')
                ->leftJoin('master_requester as c', 'purchase_requisitions.requester', '=', 'c.id')
                ->select('purchase_requisitions.*', 'b.name', 'c.nm_requester')
                ->orderBy('purchase_requisitions.created_at', 'desc')
                ->get();
        $data_requester = MstRequester::get();

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Purchase';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('purchase.index',compact('datas','data_requester'));

    }
    // Fungsi untuk mengonversi bulan dalam format angka menjadi format romawi
    private function romanMonth($month)
    {
        $romans = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        return $romans[$month];
    }

    public function purchase_order(){
        $datas = PurchaseRequisitions::get();
        $datas = PurchaseOrders::leftJoin('master_suppliers', 'purchase_orders.id_master_suppliers', '=', 'master_suppliers.id')
                ->leftJoin('purchase_requisitions', 'purchase_orders.reference_number', '=', 'purchase_requisitions.id')
                ->select('purchase_orders.*', 'master_suppliers.name', 'purchase_requisitions.request_number')
                ->orderBy('purchase_orders.created_at', 'desc') // Menambahkan pengurutan berdasarkan created_at desc
                ->get();

        $supplier = MstSupplier::get();


        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Purchase Order';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('purchase.purchase_order',compact('datas','supplier'));

    }
    public function generateCode()
    {
        // Ambil tahun 2 digit terakhir
        $year = date('y');
        // Ambil nomor urut terakhir dari database
        $lastCode = PurchaseOrders::orderBy('created_at', 'desc')
        ->value(DB::raw('LEFT(po_number, 3)'));
    
        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // dd($lastCode);
        // die;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Ambil bulan saat ini dalam format romawi
        $currentMonth = $this->romanMonth(date('n'));

        // Format kode dengan 3 digit nomor urut, informasi lainnya, bulan romawi, dan tahun
        $formattedCode = sprintf('%03d/PO/OTP/%s/%02d', $nextCode, $currentMonth, $year);
        // $formattedCode = $this->purchase_order();
        $data['find'] = $formattedCode;
        return response()->json(['data' => $data]);

        // return response()->json(['code' => $formattedCode]);
    }
    public function hapus_request_number(){
         // Ambil nomor urut terakhir dari database
         $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')
         ->value(DB::raw('RIGHT(request_number, 7)'));
     
         // Jika tidak ada nomor urut sebelumnya, atur ke 0
         $lastCode = $lastCode ? $lastCode : 0;
 
         // Tingkatkan nomor urut
         $nextCode = $lastCode + 1;
 
         // Format kode dengan panjang 7 karakter
         $formattedCode = 'PR'.date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

         PurchaseRequisitionsDetailSmt::where('request_number', $formattedCode)->delete(); 

        //  return redirect()->route('http://localhost/add-pr-rm');
         return redirect()->intended('/add-pr-rm');
    }
    public function hapus_request_number_wip(){
        // Ambil nomor urut terakhir dari database
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')
        ->value(DB::raw('RIGHT(request_number, 7)'));
    
        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Format kode dengan panjang 7 karakter
        $formattedCode = 'PR'.date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        PurchaseRequisitionsDetailSmt::where('request_number', $formattedCode)->delete(); 

       //  return redirect()->route('http://localhost/add-pr-rm');
        return redirect()->intended('/add-pr-wip');
   }
   public function hapus_request_number_fg(){
        // Ambil nomor urut terakhir dari database
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')
        ->value(DB::raw('RIGHT(request_number, 7)'));

        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Format kode dengan panjang 7 karakter
        $formattedCode = 'PR'.date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        PurchaseRequisitionsDetailSmt::where('request_number', $formattedCode)->delete(); 

    //  return redirect()->route('http://localhost/add-pr-rm');
        return redirect()->intended('/add-pr-fg');
    }
    public function hapus_request_number_ta(){
        // Ambil nomor urut terakhir dari database
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')
        ->value(DB::raw('RIGHT(request_number, 7)'));

        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Format kode dengan panjang 7 karakter
        $formattedCode = 'PR'.date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        PurchaseRequisitionsDetailSmt::where('request_number', $formattedCode)->delete(); 

    //  return redirect()->route('http://localhost/add-pr-rm');
        return redirect()->intended('/add-pr-sparepart');
    }
    public function tambah_pr_rm(){
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $rawMaterials = DB::table('master_raw_materials')
                        ->select('description','id')
                        ->get();
        $units = DB::table('master_units')
                        ->select('unit_code','id')
                        ->get();
        
        // Ambil nomor urut terakhir dari database
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')
        ->value(DB::raw('RIGHT(request_number, 7)'));
    
        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Format kode dengan panjang 7 karakter
        $formattedCode = 'PR'.date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        // $dt_detailSmt = PurchaseRequisitionsDetailSmt::where('request_number', $formattedCode)->get();
        $dt_detailSmt = DB::table('purchase_requisition_details_sementara as a')
                        ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code')
                        ->where('a.request_number', $formattedCode)
                        ->get();            

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Add Purchase Order RM';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('purchase.tambah_pr_rm',compact('datas','supplier','rawMaterials','units','formattedCode'
        ,'dt_detailSmt'));

    }
    public function tambah_pr_wip(){
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $wip = DB::table('master_wips')
                        ->select('description','id')
                        ->get();
        $units = DB::table('master_units')
                        ->select('unit_code','id')
                        ->get();

        // Ambil nomor urut terakhir dari database
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')
        ->value(DB::raw('RIGHT(request_number, 7)'));
    
        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Format kode dengan panjang 7 karakter
        $formattedCode = 'PR'.date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        $dt_detailSmt = DB::table('purchase_requisition_details_sementara as a')
                        ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code')
                        ->where('a.request_number', $formattedCode)
                        ->get();

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Add Purchase Order WIP';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('purchase.tambah_pr_wip',compact('datas','supplier','wip','units','formattedCode'
        ,'dt_detailSmt'));

    }
    public function tambah_pr_fg(){
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $fg = DB::table('master_product_fgs')
                        ->select('description','id')
                        ->get();
        $units = DB::table('master_units')
                        ->select('unit_code','id')
                        ->get();

        // Ambil nomor urut terakhir dari database
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')
        ->value(DB::raw('RIGHT(request_number, 7)'));
    
        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Format kode dengan panjang 7 karakter
        $formattedCode = 'PR'.date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        $dt_detailSmt = DB::table('purchase_requisition_details_sementara as a')
                        ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code')
                        ->where('a.request_number', $formattedCode)
                        ->get();

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Add Purchase Order FG';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('purchase.tambah_pr_fg',compact('datas','supplier','fg','units','formattedCode','dt_detailSmt'));

    }
    public function tambah_pr_sparepart(){
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $ta = DB::table('master_tool_auxiliaries')
                        ->select('description','id')
                        ->get();
        $units = DB::table('master_units')
                        ->select('unit_code','id')
                        ->get();

        // Ambil nomor urut terakhir dari database
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')
        ->value(DB::raw('RIGHT(request_number, 7)'));
    
        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Format kode dengan panjang 7 karakter
        $formattedCode = 'PR'.date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        $dt_detailSmt = DB::table('purchase_requisition_details_sementara as a')
                        ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code')
                        ->where('a.request_number', $formattedCode)
                        ->get();


        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Add Purchase Order Sparepart';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('purchase.tambah_pr_sparepart',compact('datas','supplier','ta','units','formattedCode','dt_detailSmt'));

    }
    public function get_supplier(){
        $data = DB::select("SELECT master_suppliers.name,master_suppliers.id  FROM master_suppliers");
        $data['rn'] = DB::select("SELECT `purchase_requisitions`.`request_number` FROM `purchase_requisitions` GROUP BY request_number");
        return response()->json(['data' => $data]);
    }
    public function simpan_po(Request $request){
        // dd($request);
        // die;
        $pesan = [
            'po_number.required' => 'po number masih kosong',
            'date.required' => 'date masih kosong',
            'reference_number.required' => 'reference number masih kosong',
            'id_master_suppliers.required' => 'id master suppliers masih kosong',
            'qc_check.required' => 'qc check masih kosong',
            'non_invoiceable.required' => 'non_invoiceable masih kosong',
            'vendor_taxable.required' => 'vendor_taxable masih kosong',
            'down_payment.required' => 'down payment masih kosong',
            'own_remarks.required' => 'own remarks masih kosong',
            'supplier_remarks.required' => 'supplier remarks masih kosong',
            'status.required' => 'status masih kosong',
            'type.required' => 'type masih kosong',
        ];

        $validatedData = $request->validate([
            'po_number' => 'required',
            'date' => 'required',
            'reference_number' => 'required',
            'id_master_suppliers' => 'required',
            'qc_check' => 'required',
            'non_invoiceable' => 'required',
            'vendor_taxable' => 'required',
            'down_payment' => 'required',
            'own_remarks' => 'required',
            'supplier_remarks' => 'required',
            'status' => 'required',
            'type' => 'required',

        ], $pesan);

        PurchaseOrders::create($validatedData);

        if ($validatedData) {
            //redirect dengan pesan sukses
            Alert::success('Success', 'Data berhasil ditambahkan');
            return back();
        } else {
            //redirect dengan pesan error
            Alert::error('Error', 'Data gagal berhasil ditambahkan');
            return back();
        }
    }
    public function simpan_detail_rm(Request $request){
        if($request->has('save_detail')){
        $pesan = [
            'type_product.required' => 'type masih kosong',
            'master_products_id.required' => 'master_products_id masih kosong',
            'qty.required' => 'qty masih kosong',
            'master_units_id.required' => 'master_units_id masih kosong',
            'required_date.required' => 'required_date masih kosong',
            'cc_co.required' => 'cc_co masih kosong',
            'remarks.required' => 'remarks masih kosong',
            'request_number.required' => 'type masih kosong',
            
        ];

        $validatedData = $request->validate([
            'type_product' => 'required',
            'master_products_id' => 'required',
            'qty' => 'required',
            'master_units_id' => 'required',
            'required_date' => 'required',
            'cc_co' => 'required',
            'remarks' => 'required',
            'request_number' => 'required',

        ], $pesan);

        // dd($validatedData);
        // die;
        $request_number = $request->input('request_number');
        PurchaseRequisitionsDetail::create($validatedData);

        // return "Tombol Save detail diklik.";
        return Redirect::to('/detail-pr/'.$request_number)->with('pesan', 'Data berhasil disimpan.');
        // return Redirect::to('/detail-pr/'.$request_number);
        }elseif ($request->has('hapus_detail')){
            $validatedData = $request->input('hapus_detail');

            // dd($id);
            // die;
            $request_number = $request->input('request_number');
            PurchaseRequisitionsDetail::destroy($validatedData);
            return Redirect::to('/detail-pr/'.$request_number)->with('pesan', 'Data berhasil dihapus.');

            // return "Tombol Save detail diklik.";
        }

    }
    public function simpan_pr_rm(Request $request){
        // dd($request);
        // die;

        if ($request->has('savemore')) {
            // Tombol "Save & Add More" diklik
            // Lakukan tindakan yang sesuai di sini
            return "Tombol Save & Add More diklik.";
        } elseif ($request->has('save')) {
            // dd($request->has('save'));
            // die;
            $pesan = [
                'request_number.required' => 'request number masih kosong',
                'date.required' => 'date masih kosong',
                'id_master_suppliers.required' => 'id master suppliers masih kosong',
                'requester.required' => 'requester masih kosong',
                'qc_check.required' => 'qc_check masih kosong',
                'note.required' => 'note masih kosong',
                'status.required' => 'status masih kosong',
                'type.required' => 'type masih kosong',
                
            ];

            $validatedData = $request->validate([
                'request_number' => 'required',
                'date' => 'required',
                'id_master_suppliers' => 'required',
                'requester' => 'required',
                'qc_check' => 'required',
                'note' => 'required',
                'status' => 'required',
                'type' => 'required',

            ], $pesan);

            // dd($validatedData);
            // die;
            $request_number = $request->input('request_number');

            PurchaseRequisitions::create($validatedData);

            return Redirect::to('/detail-pr/'.$request_number);
       

        }elseif($request->has('save_detail')) {

            $pesan = [
                'type_product.required' => 'type masih kosong',
                'master_products_id.required' => 'master_products_id masih kosong',
                'qty.required' => 'qty masih kosong',
                'master_units_id.required' => 'master_units_id masih kosong',
                'required_date.required' => 'required_date masih kosong',
                'cc_co.required' => 'cc_co masih kosong',
                'remarks.required' => 'remarks masih kosong',
                'request_number.required' => 'type masih kosong',
                
            ];
    
            $validatedData = $request->validate([
                'type_product' => 'required',
                'master_products_id' => 'required',
                'qty' => 'required',
                'master_units_id' => 'required',
                'required_date' => 'required',
                'cc_co' => 'required',
                'remarks' => 'required',
                'request_number' => 'required',
    
            ], $pesan);

            // dd($validatedData);
            // die;
    
            PurchaseRequisitionsDetailSmt::create($validatedData);

            // return "Tombol Save detail diklik.";
            return back();
            
        }elseif ($request->has('hapus_detail')){
            $validatedData = $request->input('hapus_detail');

            // dd($id);
            // die;
            PurchaseRequisitionsDetailSmt::destroy($validatedData);

            // return "Tombol Save detail diklik.";
        }
        
    }
    public function detail_pr($request_number){
        // dd($request_number);
        // die;
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $rawMaterials = DB::table('master_raw_materials')
                        ->select('description','id')
                        ->get();
        $units = DB::table('master_units')
                        ->select('unit_code','id')
                        ->get();

         $dt_detailSmt = DB::table('purchase_requisition_details as a')
         ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
         ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
         ->select('a.*', 'b.description', 'c.unit_code')
         ->where('a.request_number', $request_number)
         ->get();            

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Add Purchase Order RM';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('purchase.detail_pr',compact('datas','supplier','rawMaterials','units','dt_detailSmt'
        ,'request_number'));
    }
    public function simpan_pr_wip(Request $request){
        // dd($request->has('save_detail'));
        // die;

        if ($request->has('savemore')) {
            // Tombol "Save & Add More" diklik
            // Lakukan tindakan yang sesuai di sini
            return "Tombol Save & Add More diklik.";
        } elseif ($request->has('save')) {
            $pesan = [
                'request_number.required' => 'request number masih kosong',
                'date.required' => 'date masih kosong',
                'id_master_suppliers.required' => 'id master suppliers masih kosong',
                'requester.required' => 'requester masih kosong',
                'qc_check.required' => 'qc_check masih kosong',
                'note.required' => 'note masih kosong',
                'status.required' => 'status masih kosong',
                'type.required' => 'type masih kosong',
                // 'master_products_id.required' => 'master_products_id masih kosong',
                // 'qty.required' => 'qty masih kosong',
                // 'master_units_id.required' => 'master_units_id masih kosong',
                // 'required_date.required' => 'required date masih kosong',
                // 'cc_co.required' => 'cc co masih kosong',
                // 'remarks.required' => 'remarks masih kosong',
                // 'master_units_id.required' => 'master_units_id masih kosong',
            ];

            $validatedData = $request->validate([
                'request_number' => 'required',
                'date' => 'required',
                'id_master_suppliers' => 'required',
                'requester' => 'required',
                'qc_check' => 'required',
                'note' => 'required',
                'status' => 'required',
                'type' => 'required',

            ], $pesan);

            PurchaseRequisitions::create($validatedData);
        } elseif($request->has('save_detail')) {

            $pesan = [
                'type.required' => 'type masih kosong',
                'master_products_id.required' => 'master_products_id masih kosong',
                'qty.required' => 'qty masih kosong',
                'master_units_id.required' => 'master_units_id masih kosong',
                'required_date.required' => 'required_date masih kosong',
                'cc_co.required' => 'cc_co masih kosong',
                'remarks.required' => 'remarks masih kosong',
                'request_number.required' => 'type masih kosong',
                
            ];
    
            $validatedData = $request->validate([
                'type' => 'required',
                'master_products_id' => 'required',
                'qty' => 'required',
                'master_units_id' => 'required',
                'required_date' => 'required',
                'cc_co' => 'required',
                'remarks' => 'required',
                'request_number' => 'required',
    
            ], $pesan);
    
            PurchaseRequisitionsDetailSmt::create($validatedData);
            
        }elseif ($request->has('hapus_detail')){
            $validatedData = $request->input('hapus_detail');

            // dd($id);
            // die;
            PurchaseRequisitionsDetailSmt::destroy($validatedData);
        }
        // $pesan = [
        //     'request_number.required' => 'request number masih kosong',
        //     'date.required' => 'date masih kosong',
        //     'id_master_suppliers.required' => 'id master suppliers masih kosong',
        //     'requester.required' => 'requester masih kosong',
        //     'qc_check.required' => 'qc_check masih kosong',
        //     'note.required' => 'note masih kosong',
        //     'status.required' => 'status masih kosong',
        //     'type.required' => 'type masih kosong',
        //     'master_products_id.required' => 'master_products_id masih kosong',
        //     'qty.required' => 'qty masih kosong',
        //     'master_units_id.required' => 'master_units_id masih kosong',
        //     'required_date.required' => 'required date masih kosong',
        //     'cc_co.required' => 'cc co masih kosong',
        //     'remarks.required' => 'remarks masih kosong',
        //     'master_units_id.required' => 'master_units_id masih kosong',
        // ];

        // $validatedData = $request->validate([
        //     'po_number' => 'required',
        //     'date' => 'required',
        //     'reference_number' => 'required',
        //     'id_master_suppliers' => 'required',
        //     'qc_check' => 'required',
        //     'non_invoiceable' => 'required',
        //     'vendor_taxable' => 'required',
        //     'down_payment' => 'required',
        //     'own_remarks' => 'required',
        //     'supplier_remarks' => 'required',
        //     'status' => 'required',
        //     'type' => 'required',

        // ], $pesan);

        // PurchaseOrders::create($validatedData);

        if ($validatedData) {
            //redirect dengan pesan sukses
            Alert::success('Success', 'Data berhasil ditambahkan');
            return back();
        } else {
            //redirect dengan pesan error
            Alert::error('Error', 'Data gagal berhasil ditambahkan');
            return back();
        }
    }
    public function simpan_pr_fg(Request $request){
        // dd($request->has('save_detail'));
        // die;

        if ($request->has('savemore')) {
            // Tombol "Save & Add More" diklik
            // Lakukan tindakan yang sesuai di sini
            return "Tombol Save & Add More diklik.";
        } elseif ($request->has('save')) {
            $pesan = [
                'request_number.required' => 'request number masih kosong',
                'date.required' => 'date masih kosong',
                'id_master_suppliers.required' => 'id master suppliers masih kosong',
                'requester.required' => 'requester masih kosong',
                'qc_check.required' => 'qc_check masih kosong',
                'note.required' => 'note masih kosong',
                'status.required' => 'status masih kosong',
                'type.required' => 'type masih kosong',
                // 'master_products_id.required' => 'master_products_id masih kosong',
                // 'qty.required' => 'qty masih kosong',
                // 'master_units_id.required' => 'master_units_id masih kosong',
                // 'required_date.required' => 'required date masih kosong',
                // 'cc_co.required' => 'cc co masih kosong',
                // 'remarks.required' => 'remarks masih kosong',
                // 'master_units_id.required' => 'master_units_id masih kosong',
            ];

            $validatedData = $request->validate([
                'request_number' => 'required',
                'date' => 'required',
                'id_master_suppliers' => 'required',
                'requester' => 'required',
                'qc_check' => 'required',
                'note' => 'required',
                'status' => 'required',
                'type' => 'required',

            ], $pesan);

            PurchaseRequisitions::create($validatedData);
        } elseif($request->has('save_detail')) {

            $pesan = [
                'type.required' => 'type masih kosong',
                'master_products_id.required' => 'master_products_id masih kosong',
                'qty.required' => 'qty masih kosong',
                'master_units_id.required' => 'master_units_id masih kosong',
                'required_date.required' => 'required_date masih kosong',
                'cc_co.required' => 'cc_co masih kosong',
                'remarks.required' => 'remarks masih kosong',
                'request_number.required' => 'type masih kosong',
                
            ];
    
            $validatedData = $request->validate([
                'type' => 'required',
                'master_products_id' => 'required',
                'qty' => 'required',
                'master_units_id' => 'required',
                'required_date' => 'required',
                'cc_co' => 'required',
                'remarks' => 'required',
                'request_number' => 'required',
    
            ], $pesan);
    
            PurchaseRequisitionsDetailSmt::create($validatedData);
            
        }elseif ($request->has('hapus_detail')){
            $validatedData = $request->input('hapus_detail');

            // dd($id);
            // die;
            PurchaseRequisitionsDetailSmt::destroy($validatedData);
        }
        // $pesan = [
        //     'request_number.required' => 'request number masih kosong',
        //     'date.required' => 'date masih kosong',
        //     'id_master_suppliers.required' => 'id master suppliers masih kosong',
        //     'requester.required' => 'requester masih kosong',
        //     'qc_check.required' => 'qc_check masih kosong',
        //     'note.required' => 'note masih kosong',
        //     'status.required' => 'status masih kosong',
        //     'type.required' => 'type masih kosong',
        //     'master_products_id.required' => 'master_products_id masih kosong',
        //     'qty.required' => 'qty masih kosong',
        //     'master_units_id.required' => 'master_units_id masih kosong',
        //     'required_date.required' => 'required date masih kosong',
        //     'cc_co.required' => 'cc co masih kosong',
        //     'remarks.required' => 'remarks masih kosong',
        //     'master_units_id.required' => 'master_units_id masih kosong',
        // ];

        // $validatedData = $request->validate([
        //     'po_number' => 'required',
        //     'date' => 'required',
        //     'reference_number' => 'required',
        //     'id_master_suppliers' => 'required',
        //     'qc_check' => 'required',
        //     'non_invoiceable' => 'required',
        //     'vendor_taxable' => 'required',
        //     'down_payment' => 'required',
        //     'own_remarks' => 'required',
        //     'supplier_remarks' => 'required',
        //     'status' => 'required',
        //     'type' => 'required',

        // ], $pesan);

        // PurchaseOrders::create($validatedData);

        if ($validatedData) {
            //redirect dengan pesan sukses
            Alert::success('Success', 'Data berhasil ditambahkan');
            return back();
        } else {
            //redirect dengan pesan error
            Alert::error('Error', 'Data gagal berhasil ditambahkan');
            return back();
        }
    }
    public function simpan_pr_ta(Request $request){
        // dd($request->has('save_detail'));
        // die;

        if ($request->has('savemore')) {
            // Tombol "Save & Add More" diklik
            // Lakukan tindakan yang sesuai di sini
            return "Tombol Save & Add More diklik.";
        } elseif ($request->has('save')) {
            $pesan = [
                'request_number.required' => 'request number masih kosong',
                'date.required' => 'date masih kosong',
                'id_master_suppliers.required' => 'id master suppliers masih kosong',
                'requester.required' => 'requester masih kosong',
                'qc_check.required' => 'qc_check masih kosong',
                'note.required' => 'note masih kosong',
                'status.required' => 'status masih kosong',
                'type.required' => 'type masih kosong',
                // 'master_products_id.required' => 'master_products_id masih kosong',
                // 'qty.required' => 'qty masih kosong',
                // 'master_units_id.required' => 'master_units_id masih kosong',
                // 'required_date.required' => 'required date masih kosong',
                // 'cc_co.required' => 'cc co masih kosong',
                // 'remarks.required' => 'remarks masih kosong',
                // 'master_units_id.required' => 'master_units_id masih kosong',
            ];

            $validatedData = $request->validate([
                'request_number' => 'required',
                'date' => 'required',
                'id_master_suppliers' => 'required',
                'requester' => 'required',
                'qc_check' => 'required',
                'note' => 'required',
                'status' => 'required',
                'type' => 'required',

            ], $pesan);

            PurchaseRequisitions::create($validatedData);
        } elseif($request->has('save_detail')) {

            $pesan = [
                'type.required' => 'type masih kosong',
                'master_products_id.required' => 'master_products_id masih kosong',
                'qty.required' => 'qty masih kosong',
                'master_units_id.required' => 'master_units_id masih kosong',
                'required_date.required' => 'required_date masih kosong',
                'cc_co.required' => 'cc_co masih kosong',
                'remarks.required' => 'remarks masih kosong',
                'request_number.required' => 'type masih kosong',
                
            ];
    
            $validatedData = $request->validate([
                'type' => 'required',
                'master_products_id' => 'required',
                'qty' => 'required',
                'master_units_id' => 'required',
                'required_date' => 'required',
                'cc_co' => 'required',
                'remarks' => 'required',
                'request_number' => 'required',
    
            ], $pesan);
    
            PurchaseRequisitionsDetailSmt::create($validatedData);
            
        }elseif ($request->has('hapus_detail')){
            $validatedData = $request->input('hapus_detail');

            // dd($id);
            // die;
            PurchaseRequisitionsDetailSmt::destroy($validatedData);
        }
        // $pesan = [
        //     'request_number.required' => 'request number masih kosong',
        //     'date.required' => 'date masih kosong',
        //     'id_master_suppliers.required' => 'id master suppliers masih kosong',
        //     'requester.required' => 'requester masih kosong',
        //     'qc_check.required' => 'qc_check masih kosong',
        //     'note.required' => 'note masih kosong',
        //     'status.required' => 'status masih kosong',
        //     'type.required' => 'type masih kosong',
        //     'master_products_id.required' => 'master_products_id masih kosong',
        //     'qty.required' => 'qty masih kosong',
        //     'master_units_id.required' => 'master_units_id masih kosong',
        //     'required_date.required' => 'required date masih kosong',
        //     'cc_co.required' => 'cc co masih kosong',
        //     'remarks.required' => 'remarks masih kosong',
        //     'master_units_id.required' => 'master_units_id masih kosong',
        // ];

        // $validatedData = $request->validate([
        //     'po_number' => 'required',
        //     'date' => 'required',
        //     'reference_number' => 'required',
        //     'id_master_suppliers' => 'required',
        //     'qc_check' => 'required',
        //     'non_invoiceable' => 'required',
        //     'vendor_taxable' => 'required',
        //     'down_payment' => 'required',
        //     'own_remarks' => 'required',
        //     'supplier_remarks' => 'required',
        //     'status' => 'required',
        //     'type' => 'required',

        // ], $pesan);

        // PurchaseOrders::create($validatedData);

        if ($validatedData) {
            //redirect dengan pesan sukses
            Alert::success('Success', 'Data berhasil ditambahkan');
            return back();
        } else {
            //redirect dengan pesan error
            Alert::error('Error', 'Data gagal berhasil ditambahkan');
            return back();
        }
    }
    public function hapus_po(Request $request, $id)
    {
        // dd('test');
        // die;
        PurchaseOrders::destroy($id);

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Hapus Purchase Order';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        if ($id) {
            //redirect dengan pesan sukses
            Alert::success('Success', 'Data berhasil dihapus');
            return back();
        } else {
            //redirect dengan pesan error
            Alert::error('Error', 'Data gagal dihapus');
            return back();
        }
    }
    public function get_edit_po($id)
    {
        $data['find'] = PurchaseOrders::find($id);
        return response()->json(['data' => $data]);
    }
    public function get_edit_pr($id)
    {
        $data['find'] = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code')
                        ->where('a.id', $id)
                        ->get();
        return response()->json(['data' => $data]);
    }
    public function hapus_pr(Request $request, $id)
    {
        // dd('test');
        // die;
        PurchaseRequisitions::destroy($id);

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Hapus Purchase Requisitions';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        if ($id) {
            //redirect dengan pesan sukses
            Alert::success('Success', 'Data berhasil dihapus');
            return back();
        } else {
            //redirect dengan pesan error
            Alert::error('Error', 'Data gagal dihapus');
            return back();
        }
    }
    public function edit_pr($request_number)
    {
        // dd($request_number);
        //  die;
        $datas = PurchaseRequisitions::select(
            'purchase_requisitions.*',
            'master_suppliers.name',
            'master_requester.nm_requester',
            'purchase_requisition_details.type_product',
            'purchase_requisition_details.qty',
            'purchase_requisition_details.cc_co',
            'purchase_requisition_details.required_date',
            'purchase_requisition_details.remarks'
        )
        ->leftJoin('master_suppliers', 'purchase_requisitions.id_master_suppliers', '=', 'master_suppliers.id')
        ->leftJoin('master_requester', 'purchase_requisitions.requester', '=', 'master_requester.id')
        ->leftJoin('purchase_requisition_details', 'purchase_requisitions.request_number', '=', 'purchase_requisition_details.request_number')
        ->where('purchase_requisitions.request_number', '=', $request_number)
        ->orderBy('purchase_requisitions.created_at', 'desc')
        ->get();
        
        $qtyOfFirstRow = $datas[0]->qty;
        $selectedId = $datas[0]->id_master_suppliers;
        $selectedIdreques = $datas[0]->requester;
        $radioselectted = $datas[0]->qc_check;

        
        $data_requester = MstRequester::get();
        $supplier = MstSupplier::get();
        $units = DB::table('master_units')
                        ->select('unit_code','id')
                        ->get();
        $rawMaterials = DB::table('master_raw_materials')
                        ->select('description','id')
                        ->get();
        $ta = DB::table('master_tool_auxiliaries')
                        ->select('description')
                        ->get();
        $fg = DB::table('master_product_fgs')
                        ->select('description')
                        ->get();
        $wip = DB::table('master_wips')
                        ->select('description')
                        ->get();
        
        $data_detail_ta = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code')
                        ->where('a.request_number', $request_number)
                        ->get();

        $data_detail_rm = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code')
                        ->where('a.request_number', $request_number)
                        ->get();

        $data_detail_fg = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code')
                        ->where('a.request_number', $request_number)
                        ->get();

        $data_detail_wip = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code')
                        ->where('a.request_number', $request_number)
                        ->get();
                    


        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Purchase';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('purchase.edit_pr',compact('datas','data_requester','supplier','units','rawMaterials','selectedId'
        ,'selectedIdreques','radioselectted','data_detail_ta','ta','fg','wip','data_detail_rm','data_detail_fg'
        ,'data_detail_wip'));

    }
}
