<?php

namespace App\Http\Controllers;

use DataTables;
use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use RealRashid\SweetAlert\Facades\Alert;
use Browser;
use Illuminate\Support\Facades\Crypt;


// Model
use App\Models\PurchaseRequisitions;
use App\Models\PurchaseOrders;
use App\Models\MstRequester;
use App\Models\MstSupplier;
use App\Models\PurchaseRequisitionsDetail;
use App\Models\PurchaseRequisitionsDetailSmt;
use App\Models\PurchaseOrderDetailsSMT;
use App\Models\PurchaseOrderDetails;

class PurchaseController extends Controller
{
    use AuditLogsTrait;

    public function index(Request $request){
        // $datas = PurchaseRequisitions::leftJoin('master_suppliers as b', 'purchase_requisitions.id_master_suppliers', '=', 'b.id')
        //         ->leftJoin('master_requester as c', 'purchase_requisitions.requester', '=', 'c.id')
        //         ->select('purchase_requisitions.*', 'b.name', 'c.nm_requester')
        //         ->orderBy('purchase_requisitions.created_at', 'desc')
        //         ->get();
        // $data_requester = MstRequester::get();

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Purchase';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        if (request()->ajax()) {
            $orderColumn = $request->input('order')[0]['column'];
            $orderDirection = $request->input('order')[0]['dir'];
            $columns = ['id', 'request_number', 'date', 'name', 'nm_requester', 'qc_check', 'note', '', 'type', '', ''];

            // Query dasar
            $query = PurchaseRequisitions::leftJoin('master_suppliers as b', 'purchase_requisitions.id_master_suppliers', '=', 'b.id')
                    ->leftJoin('master_requester as c', 'purchase_requisitions.requester', '=', 'c.id')
                    ->select('purchase_requisitions.*', 'b.name', 'c.nm_requester')
            ->orderBy($columns[$orderColumn], $orderDirection);

            // Handle pencarian
            if ($request->has('search') && $request->input('search')) {
                $searchValue = $request->input('search');
                $query->where(function ($query) use ($searchValue) {
                    $query->where('request_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('date', 'like', '%' . $searchValue . '%')
                        ->orWhere('b.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('c.nm_requester', 'like', '%' . $searchValue . '%')
                        ->orWhere('qc_check', 'like', '%' . $searchValue . '%')
                        ->orWhere('note', 'like', '%' . $searchValue . '%')
                        ->orWhere('type', 'like', '%' . $searchValue . '%');
                });
            }

            return DataTables::of($query)
                ->addColumn('action', function ($data) {
                    return view('purchase.action_pr', compact('data'));
                    // return 'ACTION';
                })
                ->addColumn('status', function ($data) {
                    $badgeColor = $data->status == 'Request' ? 'info' 
                                : ($data->status == 'Un Posted' ? 'warning' 
                                : ($data->status == 'Closed' ? 'primary' 
                                : ($data->status == 'Create PO' ? 'purple' 
                                : 'success')));
                    return '<span class="badge bg-' . $badgeColor . '" style="font-size: smaller;width: 100%">' . $data->status . '</span>';
                })                
                ->addColumn('statusLabel', function ($data) {
                    return $data->status;
                })
                ->rawColumns(['action', 'status', 'statusLabel'])
                ->make(true);
        }

        return view('purchase.index');

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

    public function purchase_order(Request $request){
        $datas = PurchaseRequisitions::get();
        // $datas = PurchaseOrders::leftJoin('master_suppliers', 'purchase_orders.id_master_suppliers', '=', 'master_suppliers.id')
        //         ->leftJoin('purchase_requisitions', 'purchase_orders.reference_number', '=', 'purchase_requisitions.id')
        //         ->select('purchase_orders.*', 'master_suppliers.name', 'purchase_requisitions.request_number')
        //         ->orderBy('purchase_orders.created_at', 'desc') // Menambahkan pengurutan berdasarkan created_at desc
        //         ->get();

        $supplier = MstSupplier::get();


        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Purchase Order';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        if (request()->ajax()) {
            $orderColumn = $request->input('order')[0]['column'];
            $orderDirection = $request->input('order')[0]['dir'];
            $columns = ['id', 'request_number', 'date', 'name', 'nm_requester', 'qc_check', 'note', '', 'type', '', ''];

            // Query dasar
            $query = PurchaseOrders::leftJoin('master_suppliers', 'purchase_orders.id_master_suppliers', '=', 'master_suppliers.id')
                    ->leftJoin('purchase_requisitions', 'purchase_orders.reference_number', '=', 'purchase_requisitions.id')
                    ->select('purchase_orders.*', 'master_suppliers.name', 'purchase_requisitions.request_number')
            ->orderBy($columns[$orderColumn], $orderDirection);

            // Handle pencarian
            if ($request->has('search') && $request->input('search')) {
                $searchValue = $request->input('search');
                $query->where(function ($query) use ($searchValue) {
                    $query->where('request_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('date', 'like', '%' . $searchValue . '%')
                        ->orWhere('b.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('c.nm_requester', 'like', '%' . $searchValue . '%')
                        ->orWhere('qc_check', 'like', '%' . $searchValue . '%')
                        ->orWhere('note', 'like', '%' . $searchValue . '%')
                        ->orWhere('type', 'like', '%' . $searchValue . '%');
                });
            }

            return DataTables::of($query)
                ->addColumn('action', function ($data) {
                    return view('purchase.action_po', compact('data'));
                    // return 'ACTION';
                })
                ->addColumn('pr', function ($data) {
                    return view('purchase.action_reference_number', compact('data'));
                    // return 'ACTION';
                })
                ->addColumn('status', function ($data) {
                    $badgeColor = $data->status == 'Request' ? 'info' : ($data->status == 'Un Posted' ? 'warning' : 'success');
                    return '<span class="badge bg-' . $badgeColor . '" style="font-size: smaller;width: 100%">' . $data->status . '</span>';
                })
                ->addColumn('statusLabel', function ($data) {
                    return $data->status;
                })
                ->rawColumns(['action', 'status', 'statusLabel','pr'])
                ->make(true);
        }

        return view('purchase.purchase_order');

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
    public function hapus_request_number_other(){
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
        return redirect()->intended('/add-pr-other');
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
    public function tambah_pr_other(){
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

        return view('purchase.tambah_pr_other',compact('datas','supplier','rawMaterials','units','formattedCode'
        ,'dt_detailSmt'));

    }
    public function get_supplier(){
        $data = DB::select("SELECT master_suppliers.name,master_suppliers.id  FROM master_suppliers");
        $data['rn'] = DB::select("SELECT purchase_requisitions.request_number,purchase_requisitions.id FROM `purchase_requisitions` where purchase_requisitions.status not in ('Request','Closed','Created PO','Un Posted') ");
        $id = request()->get('id');
        $pr_detail = PurchaseRequisitions::with('masterSupplier')
            ->where('id', $id)
            ->first();
        return response()->json(['data' => $data, 'pr_detail' => $pr_detail]);
    }
    public function get_unit(){
        $data = DB::select("SELECT master_units.unit_code,master_units.id,master_units.unit  FROM master_units");
        $id = request()->get('id');
        $po_detail = PurchaseOrderDetails::with('masterUnit')
            ->where('id', $id)
            ->first();
        return response()->json(['data' => $data, 'po_detail' => $po_detail]);
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

    
        $validatedData = DB::update("UPDATE `purchase_requisitions` SET `status` = 'Created PO' WHERE `id` = '$request->reference_number'");

        $reference_number = $request->input('reference_number');
        $po_number = $request->input('po_number');

        $idValue = DB::table('purchase_orders')
            ->select('id')
            ->where('po_number', $po_number)
            ->first();

        if ($idValue) {
            $id = $idValue->id;
            return redirect('/tambah_detail_po/' . $reference_number.'/'.$id);
        } else {
            // Penanganan jika $id tidak ditemukan
            return redirect()->back()->with('error', 'ID tidak ditemukan');
        }
        
    }
    public function simpan_detail_rm(Request $request, $request_number){

        // dd($request_number);
        // die;

        $id = PurchaseRequisitions::where('request_number', $request_number)->value('id');
        $request_number = $request_number;
        $request->merge([
            'request_number' => $request_number, // Ganti 'request_number' dengan nilai variabel buatan Anda
            'id_purchase_requisitions' => $id,
        ]);

        if($request->has('save_detail')){
        $pesan = [
            'id_purchase_requisitions' => 'id purchase',
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
            'id_purchase_requisitions' => 'required',
            'type_product' => 'required',
            'master_products_id' => 'required',
            'qty' => 'required',
            'master_units_id' => 'required',
            'required_date' => 'required',
            'cc_co' => 'required',
            'remarks' => 'nullable',
            'request_number' => 'required',

        ], $pesan);

        // dd($validatedData);
        // die;
        PurchaseRequisitionsDetail::create($validatedData);

        // return "Tombol Save detail diklik.";
        return Redirect::to('/detail-pr/'.$request_number)->with('pesan', 'Data berhasil disimpan.');
        // return Redirect::to('/detail-pr/'.$request_number);
        }elseif ($request->has('hapus_detail')){
            $validatedData = $request->input('hapus_detail');

            // dd($id);
            // die;
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
    public function simpan_detail_wip(Request $request, $request_number){

        $id = PurchaseRequisitions::where('request_number', $request_number)->value('id');
        $request_number = $request_number;
        $request->merge([
            'request_number' => $request_number, // Ganti 'request_number' dengan nilai variabel buatan Anda
            'id_purchase_requisitions' => $id,
        ]);
        if($request->has('save_detail')){
        $pesan = [
            'id_purchase_requisitions' => 'id purchase',
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
            'id_purchase_requisitions' => 'required',
            'type_product' => 'required',
            'master_products_id' => 'required',
            'qty' => 'required',
            'master_units_id' => 'required',
            'required_date' => 'required',
            'cc_co' => 'required',
            'remarks' => 'nullable',
            'request_number' => 'required',

        ], $pesan);

        // dd($validatedData);
        // die;
        PurchaseRequisitionsDetail::create($validatedData);

        // return "Tombol Save detail diklik.";
        return Redirect::to('/detail-pr-wip/'.$request_number)->with('pesan', 'Data berhasil disimpan.');
        // return Redirect::to('/detail-pr/'.$request_number);
        }elseif ($request->has('hapus_detail')){
            $validatedData = $request->input('hapus_detail');

            // dd($id);
            // die;
            PurchaseRequisitionsDetail::destroy($validatedData);
            return Redirect::to('/detail-pr-wip/'.$request_number)->with('pesan', 'Data berhasil dihapus.');

            // return "Tombol Save detail diklik.";
        }

    }
    public function simpan_pr_wip(Request $request){
        // dd($request->has('save'));
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

            return Redirect::to('/detail-pr-wip/'.$request_number);
        } 
    }
    public function detail_pr_wip($request_number){
        // dd($request_number);
        // die;
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $wip = DB::table('master_wips')
                        ->select('description','id')
                        ->get();
        $units = DB::table('master_units')
                        ->select('unit_code','id')
                        ->get();

         $dt_detailSmt = DB::table('purchase_requisition_details as a')
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
        $activity='Add Purchase Order WIP';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('purchase.detail_pr_wip',compact('datas','supplier','wip','units','dt_detailSmt'
        ,'request_number'));
    }
    public function simpan_detail_fg(Request $request, $request_number){
        // dd($request_number);
        // die;

        $id = PurchaseRequisitions::where('request_number', $request_number)->value('id');
        $request_number = $request_number;
        $request->merge([
            'request_number' => $request_number, // Ganti 'request_number' dengan nilai variabel buatan Anda
            'id_purchase_requisitions' => $id,
        ]);
        if($request->has('save_detail')){
        $pesan = [
            'id_purchase_requisitions' => 'id purchase',
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
            'id_purchase_requisitions' => 'required',
            'type_product' => 'required',
            'master_products_id' => 'required',
            'qty' => 'required',
            'master_units_id' => 'required',
            'required_date' => 'required',
            'cc_co' => 'required',
            'remarks' => 'nullable',
            'request_number' => 'required',

        ], $pesan);

        // dd($validatedData);
        // die;
        PurchaseRequisitionsDetail::create($validatedData);

        // return "Tombol Save detail diklik.";
        return Redirect::to('/detail-pr-fg/'.$request_number)->with('pesan', 'Data berhasil disimpan.');
        // return Redirect::to('/detail-pr/'.$request_number);
        }elseif ($request->has('hapus_detail')){
            $validatedData = $request->input('hapus_detail');

            // dd($id);
            // die;
            PurchaseRequisitionsDetail::destroy($validatedData);
            return Redirect::to('/detail-pr-fg/'.$request_number)->with('pesan', 'Data berhasil dihapus.');

            // return "Tombol Save detail diklik.";
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

            $request_number = $request->input('request_number');

            PurchaseRequisitions::create($validatedData);

            return Redirect::to('/detail-pr-fg/'.$request_number);
        }
    }
    public function detail_pr_fg($request_number){
        // dd($request_number);
        // die;
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $fg = DB::table('master_product_fgs')
                        ->select('description','id','perforasi')
                        ->get();
        $units = DB::table('master_units')
                        ->select('unit_code','id')
                        ->get();

         $dt_detailSmt = DB::table('purchase_requisition_details as a')
         ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
         ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
         ->select('a.*', 'b.description', 'c.unit_code')
         ->where('a.request_number', $request_number)
         ->get();            

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Add Purchase Order WIP';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('purchase.detail_pr_fg',compact('datas','supplier','fg','units','dt_detailSmt'
        ,'request_number'));
    }
    public function simpan_detail_ta(Request $request, $request_number){

        $id = PurchaseRequisitions::where('request_number', $request_number)->value('id');
        $request_number = $request_number;
        $request->merge([
            'request_number' => $request_number, // Ganti 'request_number' dengan nilai variabel buatan Anda
            'id_purchase_requisitions' => $id,
        ]);
        if($request->has('save_detail')){
        $pesan = [
            'id_purchase_requisitions' => 'id purchase',
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
            'id_purchase_requisitions' => 'required',
            'type_product' => 'required',
            'master_products_id' => 'required',
            'qty' => 'required',
            'master_units_id' => 'required',
            'required_date' => 'required',
            'cc_co' => 'required',
            'remarks' => 'nullable',
            'request_number' => 'required',

        ], $pesan);

        // dd($validatedData);
        // die;
        PurchaseRequisitionsDetail::create($validatedData);

        // return "Tombol Save detail diklik.";
        return Redirect::to('/detail-pr-sparepart/'.$request_number)->with('pesan', 'Data berhasil disimpan.');
        // return Redirect::to('/detail-pr/'.$request_number);
        }elseif ($request->has('hapus_detail')){
            $validatedData = $request->input('hapus_detail');

            // dd($id);
            // die;
            PurchaseRequisitionsDetail::destroy($validatedData);
            return Redirect::to('/detail-pr-sparepart/'.$request_number)->with('pesan', 'Data berhasil dihapus.');

            // return "Tombol Save detail diklik.";
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

            $request_number = $request->input('request_number');

            PurchaseRequisitions::create($validatedData);

            return Redirect::to('/detail-pr-sparepart/'.$request_number);
        }
    }
    public function detail_pr_sparepart($request_number){
        // dd($request_number);
        // die;
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $ta = DB::table('master_tool_auxiliaries')
                        ->select('description','id')
                        ->get();
        $units = DB::table('master_units')
                        ->select('unit_code','id')
                        ->get();

         $dt_detailSmt = DB::table('purchase_requisition_details as a')
         ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
         ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
         ->select('a.*', 'b.description', 'c.unit_code')
         ->where('a.request_number', $request_number)
         ->get();            

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Add Purchase Order WIP';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('purchase.detail_pr_sparepart',compact('datas','supplier','ta','units','dt_detailSmt'
        ,'request_number'));
    }
    public function hapus_po(Request $request, $id)
    {
        // dd('test');
        // die;
        PurchaseOrders::destroy($id);

        PurchaseOrderDetails::where('id_purchase_orders', $id)->delete();


        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Hapus Purchase Order';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        if ($id) {
            //redirect dengan pesan sukses
            return Redirect::to('/purchase-order')->with('pesan', 'Data berhasil dihapus.');
        } else {
            //redirect dengan pesan error
            return Redirect::to('/purchase-order')->with('pesan', 'Data gagal berhasil dihapus.');
        }
    }
    public function hapus_po_detail(Request $request, $id, $idx)
    {
        // dd('test');
        // die;
        $data = DB::table('purchase_order_details_smt')
            ->select('id_pr')
            ->where('id', '=', $id) // Ganti 'some_column' dan 'some_value' sesuai kebutuhan
            ->get();

        if ($data->isEmpty()) {
            // Data kosong, lakukan penanganan sesuai kebutuhan Anda
        } else {
            $reference_number = $data[0]->id_pr;
            // Lakukan sesuatu dengan $id_pr
        }

        PurchaseOrderDetailsSMT::destroy($id);

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Hapus Purchase Order Detail';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        if ($id) {
            //redirect dengan pesan sukses
            return Redirect::to('/detail-po/'.$reference_number.'/'.$idx)->with('pesan', 'Data berhasil dihapus.');
        } else {
            //redirect dengan pesan error
            return Redirect::to('/purchase-order')->with('pesan', 'Data gagal berhasil dihapus.');
        }
    }
    public function get_edit_po($id)
    {
        $data['find'] = PurchaseOrders::find($id);
        $data['finddetail'] = PurchaseOrderDetails::find($id);
        $data['produk'] = DB::select("SELECT master_raw_materials.description, master_raw_materials.id FROM master_raw_materials");
        $data['unit'] = DB::select("SELECT master_units.unit_code, master_units.id FROM master_units");
        return response()->json(['data' => $data]);
    }
    public function get_edit_pr($id)
    {
        // $data['find'] = DB::table('purchase_requisition_details as a')
        //                 ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
        //                 ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
        //                 ->select('a.*', 'b.description', 'c.unit_code')
        //                 ->where('a.id', $id)
        //                 ->get();
        $data['find'] = PurchaseRequisitionsDetail::find($id);
        $data['produk'] = DB::select("SELECT master_raw_materials.description, master_raw_materials.id FROM master_raw_materials");
        $data['unit'] = DB::select("SELECT master_units.unit_code, master_units.id FROM master_units");
        $data['requester'] = DB::select("SELECT master_requester.nm_requester, master_requester.id FROM master_requester");
        return response()->json(['data' => $data]);
    }
    public function hapus_pr(Request $request, $request_number)
    {
        // dd($request_number);
        // die;
        PurchaseRequisitions::where('request_number', $request_number)->delete();
        PurchaseRequisitionsDetail::where('request_number', $request_number)->delete();

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Hapus Purchase Requisitions';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return Redirect::to('/purchase')->with('pesan', 'Data berhasil dihapus.');
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
        ->where('purchase_requisitions.id', '=', $request_number)
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
                        ->select('description','id')
                        ->get();
        $wip = DB::table('master_wips')
                        ->select('description','id')
                        ->get();
        
        $data_detail_ta = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code')
                        ->where('a.id_purchase_requisitions', $request_number)
                        ->get();

        $data_detail_rm = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code')
                        ->where('a.id_purchase_requisitions', $request_number)
                        ->get();

        $data_detail_fg = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code')
                        ->where('a.id_purchase_requisitions', $request_number)
                        ->get();

        $data_detail_wip = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code')
                        ->where('a.id_purchase_requisitions', $request_number)
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
    public function update_detail_rm(Request $request, $request_number){
        //    dd($request);
        //     die;
        $request_number = $request_number;
        $request->merge([
            'request_number' => $request_number, // Ganti 'request_number' dengan nilai variabel buatan Anda
        ]);
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
            $request_number = $request_number;
            PurchaseRequisitionsDetail::create($validatedData);
    
            // return "Tombol Save detail diklik.";
            return Redirect::to('/edit-pr/'.$request_number)->with('pesan', 'Data berhasil disimpan.');
            // return Redirect::to('/detail-pr/'.$request_number);
        }elseif ($request->has('hapus_detail')){
            $validatedData = $request->input('hapus_detail');

            // dd($id);
            // die;
            $request_number = $request->input('request_number');
            PurchaseRequisitionsDetail::destroy($validatedData);
            return Redirect::to('/edit-pr/'.$request_number)->with('pesan', 'Data berhasil dihapus.');

            // return "Tombol Save detail diklik.";
        }
    }public function update_pr(Request $request, $request_number){
        $request_number = $request_number;
        // dd($request);
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

        PurchaseRequisitions::where('request_number', $request_number)
            ->update($validatedData);

        $request_number = $request->input('request_number');
        return Redirect::to('/edit-pr/'.$request_number);
    }
    public function posted_pr($request_number)
    {
        $request_numberx=$request_number;
        $validatedData = DB::update("UPDATE `purchase_requisitions` SET `status` = 'Posted' WHERE `request_number` = '$request_numberx';");

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Posted Purchase Requisitions';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        if ($validatedData) {
            //redirect dengan pesan sukses
            return Redirect::to('/purchase')->with('pesan', 'Data berhasil diposted.');
        } else {
            //redirect dengan pesan error
            return Redirect::to('/purchase')->with('pesan', 'Data gagal diposted.');
        }

        
    }public function unposted_pr($request_number)
    {
        $request_numberx=$request_number;
        $validatedData = DB::update("UPDATE `purchase_requisitions` SET `status` = 'Un Posted' WHERE `request_number` = '$request_numberx';");

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Posted Purchase Requisitions';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        if ($validatedData) {
            //redirect dengan pesan sukses
            return Redirect::to('/purchase')->with('pesan', 'Data berhasil di Un Posted.');
        } else {
            //redirect dengan pesan error
            return Redirect::to('/purchase')->with('pesan', 'Data gagal di Un Posted.');
        }

        
    }
    public function detail_po($reference_number,$id){
        // dd($id);
        // die;
        $findtype = DB::table('purchase_order_details_smt')
                        ->select('type_product')
                        ->where('id_pr', $reference_number)
                        ->first();
                        
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $rawMaterials = DB::table('master_raw_materials')
                        ->select('description','id')
                        ->get();

        $ta = DB::table('master_tool_auxiliaries')
                        ->select('description','id')
                        ->get();
        $fg = DB::table('master_product_fgs')
                        ->select('description','id','perforasi')
                        ->get();
        $wip = DB::table('master_wips')
                        ->select('description','id')
                        ->get();


        $units = DB::table('master_units')
                        ->select('unit_code','id','unit')
                        ->get();

        $POSmt = PurchaseOrderDetailsSMT::where('id_pr', $reference_number)->get();

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Add Purchase Order RM';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('purchase.detail_po',compact('datas','supplier','rawMaterials','units'
        ,'reference_number','POSmt','id','ta','fg','wip','findtype'));
    }
    public function tambah_detail_po($reference_number,$id){

        $findtype = DB::table('purchase_orders')
            ->select('type')
            ->where('id', $id)
            ->first();

        // dd($reference_number);
        // die;

        PurchaseOrderDetailsSMT::where('id_pr', $reference_number)->delete(); 

        if ($findtype->type == 'RM') {

            $results = DB::table('purchase_requisition_details')
            ->select(
                'purchase_requisitions.id',
                'purchase_requisition_details.type_product',
                'master_raw_materials.id as id_produk',
                'purchase_requisition_details.qty',
                'purchase_requisitions.request_number',
                'master_units.unit'
            )
            ->rightJoin('purchase_requisitions', 'purchase_requisition_details.request_number', '=', 'purchase_requisitions.request_number')
            ->leftJoin('master_raw_materials', 'purchase_requisition_details.master_products_id', '=', 'master_raw_materials.id')
            ->leftJoin('master_units', 'purchase_requisition_details.master_units_id', '=', 'master_units.id')
            ->where('purchase_requisitions.id', '=', $reference_number)
            ->get();

        
        }elseif ($findtype->type == 'FG') {
            $results = DB::table('purchase_requisition_details')
            ->select(
                'purchase_requisitions.id',
                'purchase_requisition_details.type_product',
                'master_product_fgs.id as id_produk',
                'purchase_requisition_details.qty',
                'purchase_requisitions.request_number',
                'master_units.unit'
            )
            ->rightJoin('purchase_requisitions', 'purchase_requisition_details.request_number', '=', 'purchase_requisitions.request_number')
            ->leftJoin('master_product_fgs', 'purchase_requisition_details.master_products_id', '=', 'master_product_fgs.id')
            ->leftJoin('master_units', 'purchase_requisition_details.master_units_id', '=', 'master_units.id')
            ->where('purchase_requisitions.id', '=', $reference_number)
            ->get();

        }elseif ($findtype->type == 'WIP'){
            $results = DB::table('purchase_requisition_details')
            ->select(
                'purchase_requisitions.id',
                'purchase_requisition_details.type_product',
                'master_wips.id as id_produk',
                'purchase_requisition_details.qty',
                'purchase_requisitions.request_number',
                'master_units.unit'
            )
            ->rightJoin('purchase_requisitions', 'purchase_requisition_details.request_number', '=', 'purchase_requisitions.request_number')
            ->leftJoin('master_wips', 'purchase_requisition_details.master_products_id', '=', 'master_wips.id')
            ->leftJoin('master_units', 'purchase_requisition_details.master_units_id', '=', 'master_units.id')
            ->where('purchase_requisitions.id', '=', $reference_number)
            ->get();

        }elseif ($findtype->type == 'TA') {
            $results = DB::table('purchase_requisition_details')
            ->select(
                'purchase_requisitions.id',
                'purchase_requisition_details.type_product',
                'master_tool_auxiliaries.id as id_produk',
                'purchase_requisition_details.qty',
                'purchase_requisitions.request_number',
                'master_units.unit'
            )
            ->rightJoin('purchase_requisitions', 'purchase_requisition_details.request_number', '=', 'purchase_requisitions.request_number')
            ->leftJoin('master_tool_auxiliaries', 'purchase_requisition_details.master_products_id', '=', 'master_tool_auxiliaries.id')
            ->leftJoin('master_units', 'purchase_requisition_details.master_units_id', '=', 'master_units.id')
            ->where('purchase_requisitions.id', '=', $reference_number)
            ->get();
        }

        // dd($results);
        // die;
        
        // Simpan hasil query ke dalam tabel purchase_order_details_smt
        foreach ($results as $result) {
            DB::table('purchase_order_details_smt')->insert([
                'id_pr' => $result->id,
                'type_product' => $result->type_product,
                'description' => $result->id_produk,
                'qty' => $result->qty,
                'request_number' => $result->request_number,
                'unit' => $result->unit,
            ]);
        }

        return Redirect::to('/detail-po/'. $reference_number. '/' .$id);

    }public function simpan_detail_po(Request $request, $reference_number,$id){
        // dd($id);
        // die;
        $requestNumber = DB::table('purchase_requisitions')
            ->select('request_number')
            ->where('id', $reference_number)
            ->first();
    
        // Periksa jika hasil kueri tidak null sebelum mengakses properti request_number
        if ($requestNumber) {
            $requestNumberValue = $requestNumber->request_number;
        } else {
            // Handle jika hasil kueri tidak ditemukan
            return redirect()->back()->with('error', 'Data request tidak ditemukan');
        }
    
        $validatedData = $request->validate([
            'id_pr' => 'required',
            'type_product' => 'required',
            'description' => 'required',
            'qty' => 'required',
            'unit' => 'required',
            'price' => 'required',
            'discount' => 'required',
            'tax' => 'required',
            'amount' => 'required',
            'note' => 'required',
        ]);
    
        // Set nilai 'request_number' dengan hasil kueri database
        $validatedData['request_number'] = $requestNumberValue;
    
        PurchaseOrderDetailsSMT::create($validatedData);
    
        return Redirect::to('/detail-po/'.$reference_number.'/'.$id)->with('pesan', 'Purchase Requisition Detail berhasil ditambahkan.');
    }public function simpan_detail_po_fix(Request $request, $id, $reference_number){
        
        // dd($id);
        // die;

        $findtype = DB::table('purchase_orders')
            ->select('type')
            ->where('id', $id)
            ->first();

        // dd($findtype->type);
        // die;    

        if ($findtype->type == 'RM') {

            $results = DB::table('purchase_order_details_smt as a')
                        ->select(
                            DB::raw($id.' as id_purchase_order'),
                            'a.type_product',
                            'b.id as master_products_id',
                            'a.note',
                            'a.qty',
                            'c.id as master_units_id',
                            'a.price',
                            'a.discount',
                            'a.tax',
                            'a.amount'
                        )
                        ->leftJoin('master_raw_materials as b', 'a.description', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.unit', '=', 'c.unit')
                        ->where('a.id_pr', '=', $reference_number)
                        ->get();

        }elseif ($findtype->type == 'FG') {
            $results = DB::table('purchase_order_details_smt as a')
            ->select(
                DB::raw($id.' as id_purchase_order'),
                'a.type_product',
                'b.id as master_products_id',
                'a.note',
                'a.qty',
                'c.id as master_units_id',
                'a.price',
                'a.discount',
                'a.tax',
                'a.amount'
            )
            ->leftJoin('master_product_fgs as b', 'a.description', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.unit', '=', 'c.unit')
            ->where('a.id_pr', '=', $reference_number)
            ->get();

        }elseif ($findtype->type == 'WIP') {
            $results = DB::table('purchase_order_details_smt as a')
            ->select(
                DB::raw($id.' as id_purchase_order'),
                'a.type_product',
                'b.id as master_products_id',
                'a.note',
                'a.qty',
                'c.id as master_units_id',
                'a.price',
                'a.discount',
                'a.tax',
                'a.amount'
            )
            ->leftJoin('master_wips as b', 'a.description', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.unit', '=', 'c.unit')
            ->where('a.id_pr', '=', $reference_number)
            ->get();

        }elseif ($findtype->type == 'TA') {
            $results = DB::table('purchase_order_details_smt as a')
            ->select(
                DB::raw($id.' as id_purchase_order'),
                'a.type_product',
                'b.id as master_products_id',
                'a.note',
                'a.qty',
                'c.id as master_units_id',
                'a.price',
                'a.discount',
                'a.tax',
                'a.amount'
            )
            ->leftJoin('master_tool_auxiliaries as b', 'a.description', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.unit', '=', 'c.unit')
            ->where('a.id_pr', '=', $reference_number)
            ->get();

        }


        // dd($results);
        // die;
        
        // Simpan hasil query ke dalam tabel purchase_order_details_smt
        foreach ($results as $result) {
            DB::table('purchase_order_details')->insert([
                'id_purchase_orders' => $result->id_purchase_order,
                'type_product' => $result->type_product,
                'master_products_id' => $result->master_products_id,
                'note' => $result->note,
                'qty' => $result->qty,
                'master_units_id' => $result->master_units_id,
                'price' => $result->price,
                'discount' => $result->discount,
                'tax' => $result->tax,
                'amount' => $result->amount,
            ]);
        }

        $total_discount = PurchaseOrderDetails::where('id_purchase_orders', $id)->sum('discount');
        $sub_total = PurchaseOrderDetails::where('id_purchase_orders', $id)->sum('amount');

        $total_amount = $sub_total-$total_discount;
        // dd($total_discount);
        // die;
        
        $validatedData = DB::update("UPDATE `purchase_orders` SET `total_discount` = '$total_discount', 
        `sub_total` = '$sub_total', `total_amount` = '$total_amount' WHERE `id` = '$id';");
    
        return Redirect::to('/purchase-order')->with('pesan', 'Purchase Order berhasil ditambahkan.');
    }public function posted_po($id)
    {
        $idx=$id;
        $validatedData = DB::update("UPDATE `purchase_orders` SET `status` = 'Posted' WHERE `id` = '$idx';");

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Posted Purchase Order';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        if ($validatedData) {
            //redirect dengan pesan sukses
            return Redirect::to('/purchase-order')->with('pesan', 'Data berhasil diposted.');
        } else {
            //redirect dengan pesan error
            return Redirect::to('/purchase-order')->with('pesan', 'Data gagal diposted.');
        }

        
    }public function unposted_po($id)
    {
        $idx=$id;
        $validatedData = DB::update("UPDATE `purchase_orders` SET `status` = 'Un Posted' WHERE `id` = '$idx';");

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Un Posted Purchase Order';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        if ($validatedData) {
            //redirect dengan pesan sukses
            return Redirect::to('/purchase-order')->with('pesan', 'Data berhasil di Un Posted.');
        } else {
            //redirect dengan pesan error
            return Redirect::to('/purchase-order')->with('pesan', 'Data gagal di Un Posted.');
        }

        
    }
    public function edit_po($id)
    {
        // dd($id);
        //  die;
        $supplier = MstSupplier::get();
        $data_requester = MstRequester::get();
        $units = DB::table('master_units')
                        ->select('unit_code','id')
                        ->get();

        $reference_number = PurchaseRequisitions::get();

        $rawMaterials = DB::table('master_raw_materials')
                        ->select('description','id')
                        ->get();
        $ta = DB::table('master_tool_auxiliaries')
                        ->select('description')
                        ->get();
        $fg = DB::table('master_product_fgs')
                        ->select('description','id','perforasi')
                        ->get();
        $wip = DB::table('master_wips')
                        ->select('description','id')
                        ->get();

        $data_detail_rm = DB::table('purchase_order_details as a')
                ->select('a.type_product', 'b.description', 'a.qty', 'c.unit', 'a.price', 'a.discount', 'a.tax', 'a.amount', 'a.note','a.id')
                ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                ->where('a.id_purchase_orders', '=', $id)
                ->get();

        $data_detail_ta = DB::table('purchase_order_details as a')
                ->select('a.type_product', 'b.description', 'a.qty', 'c.unit', 'a.price', 'a.discount', 'a.tax', 'a.amount', 'a.note','a.id')
                ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                ->where('a.id_purchase_orders', '=', $id)
                ->get();

        $data_detail_fg = DB::table('purchase_order_details as a')
                ->select('a.type_product', 'b.description', 'a.qty', 'c.unit', 'a.price', 'a.discount', 'a.tax', 'a.amount', 'a.note','a.id')
                ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                ->where('a.id_purchase_orders', '=', $id)
                ->get();

        $data_detail_wip = DB::table('purchase_order_details as a')
                ->select('a.type_product', 'b.description', 'a.qty', 'c.unit', 'a.price', 'a.discount', 'a.tax', 'a.amount', 'a.note','a.id')
                ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                ->where('a.id_purchase_orders', '=', $id)
                ->get();

        $results = DB::table('purchase_orders as a')
                ->select(
                    'a.id',
                    'a.po_number',
                    'a.date',
                    'b.request_number',
                    'c.name',
                    'a.qc_check',
                    'a.down_payment',
                    'a.own_remarks',
                    'a.supplier_remarks',
                    'a.status',
                    'a.type',
                    'a.reference_number',
                    'a.id_master_suppliers'
                )
                ->leftJoin('purchase_requisitions as b', 'a.reference_number', '=', 'b.id')
                ->leftJoin('master_suppliers as c', 'a.id_master_suppliers', '=', 'c.id')
                ->where('a.id', '=', $id)
                ->get();

        $selectedId = $results[0]->reference_number;
        $selectedsupplier = $results[0]->id_master_suppliers;
        $radioselectted = $results[0]->qc_check;

    

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Edit Purchase Order';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('purchase.edit_po',compact('supplier','data_requester','units','data_detail_rm','results',
        'reference_number','selectedId','selectedsupplier','radioselectted','rawMaterials','ta','fg','wip',
        'data_detail_ta','data_detail_fg','data_detail_wip'));

    }public function update_po(Request $request, $id){
        $id = $id;
        // dd($request);
        // die;
        $pesan = [
            'po_number.required' => 'po number masih kosong',
            'date.required' => 'date masih kosong',
            'reference_number.required' => 'reference number masih kosong',
            'id_master_suppliers.required' => 'id_master_suppliers masih kosong',
            'qc_check.required' => 'qc_check masih kosong',
            'down_payment.required' => 'down_payment masih kosong',
            'own_remarks.required' => 'own_remarks masih kosong',
            'supplier_remarks.required' => 'supplier_remarks masih kosong',
            'status.required' => 'status masih kosong',
            'type.required' => 'type masih kosong',
            
        ];

        $validatedData = $request->validate([
            'po_number' => 'required',
            'date' => 'required',
            'reference_number' => 'required',
            'id_master_suppliers' => 'required',
            'qc_check' => 'required',
            'down_payment' => 'required',
            'own_remarks' => 'required',
            'supplier_remarks' => 'required',
            'status' => 'required',
            'type' => 'required',

        ], $pesan);

        // dd($validatedData);
        // die;

        PurchaseOrders::where('id', $id)
            ->update($validatedData);

        return Redirect::to('/purchase-order')->with('pesan', 'Data berhasil diupdate.');
    }public function update_detail_po(Request $request, $id){
    //    dd($id);
    //    die;
       $request->merge([
        'id_purchase_orders' => $id, // Ganti 'request_number' dengan nilai variabel buatan Anda
       ]);
        if($request->has('save_detail')){
            $pesan = [
                'id_purchase_orders.required' => 'id_purchase_orders masih kosong',
                'type_product.required' => 'type masih kosong',
                'master_products_id.required' => 'master_products_id masih kosong',
                'qty.required' => 'qty masih kosong',
                'master_units_id.required' => 'master_units_id masih kosong',
                'price.required' => 'price masih kosong',
                'discount.required' => 'discount masih kosong',
                'tax.required' => 'tax masih kosong',
                'amount.required' => 'amount masih kosong',
                'note.required' => 'note masih kosong',
                
            ];
    
            $validatedData = $request->validate([
                'id_purchase_orders' => 'required',
                'type_product' => 'required',
                'master_products_id' => 'required',
                'qty' => 'required',
                'master_units_id' => 'required',
                'price' => 'required',
                'discount' => 'required',
                'tax' => 'required',
                'amount' => 'required',
                'note' => 'required',
                
    
            ], $pesan);
    
            
            $id = $id;
            PurchaseOrderDetails::create($validatedData);

            $total_discount = PurchaseOrderDetails::where('id_purchase_orders', $id)->sum('discount');
            $sub_total = PurchaseOrderDetails::where('id_purchase_orders', $id)->sum('amount');

            $total_amount = $sub_total-$total_discount;
            // dd($total_discount);
            // die;
            
            $validatedData = DB::update("UPDATE `purchase_orders` SET `total_discount` = '$total_discount', 
            `sub_total` = '$sub_total', `total_amount` = '$total_amount' WHERE `id` = '$id';");
    
            // return "Tombol Save detail diklik.";
            return Redirect::to('/edit-po/'.$id)->with('pesan', 'Data berhasil disimpan.');
            // return Redirect::to('/detail-pr/'.$request_number);
        }elseif ($request->has('hapus_detail')){
            $validatedData = $request->input('hapus_detail');

            // dd($validatedData);
            // die;
            $id = $id;
            PurchaseOrderDetails::destroy($validatedData);

            $total_discount = PurchaseOrderDetails::where('id_purchase_orders', $id)->sum('discount');
            $sub_total = PurchaseOrderDetails::where('id_purchase_orders', $id)->sum('amount');

            $total_amount = $sub_total-$total_discount;
            // dd($total_discount);
            // die;
            
            $validatedData = DB::update("UPDATE `purchase_orders` SET `total_discount` = '$total_discount', 
            `sub_total` = '$sub_total', `total_amount` = '$total_amount' WHERE `id` = '$id'");

            return Redirect::to('/edit-po/'.$id)->with('pesan', 'Data berhasil dihapus.');

            // return "Tombol Save detail diklik.";
        }
    }public function update_po_detail(Request $request, $id){
        // dd($id);
        // die;

        $id = $id;
        // dd($request);
        // die;
        $pesan = [
            'type_product.required' => 'type masih kosong',
            'master_products_id.required' => 'master_products_id masih kosong',
            'qty.required' => 'qty masih kosong',
            'master_units_id.required' => 'master_units_id masih kosong',
            'price.required' => 'price masih kosong',
            'discount.required' => 'discount masih kosong',
            'tax.required' => 'tax masih kosong',
            'amount.required' => 'amount masih kosong',
            'note.required' => 'note masih kosong',
            
        ];

        $validatedData = $request->validate([
            'type_product' => 'required',
            'master_products_id' => 'required',
            'qty' => 'required',
            'master_units_id' => 'required',
            'price' => 'required',
            'discount' => 'required',
            'tax' => 'required',
            'amount' => 'required',
            'note' => 'required',
        ], $pesan);

        // dd($validatedData);
        // die;

        PurchaseOrderDetails::where('id', $id)
            ->update($validatedData);

        $id_purchase_orders = $request->input('id_purchase_orders');
        return Redirect::to('/edit-po/'.$id_purchase_orders)->with('pesan', 'Data berhasil diupdate.');
    }public function update_pr_detailx(Request $request, $id){
        $pesan = [
            'type_product.required' => 'type masih kosong',
            'master_products_id.required' => 'master_products_id masih kosong',
            'qty.required' => 'qty masih kosong',
            'master_units_id.required' => 'master_units_id masih kosong',
            'required_date.required' => 'required_date masih kosong',
            'cc_co.required' => 'cc_co masih kosong',
            'remarks.required' => 'remarks masih kosong',
            
        ];

        $validatedData = $request->validate([
            'type_product' => 'required',
            'master_products_id' => 'required',
            'qty' => 'required',
            'master_units_id' => 'required',
            'required_date' => 'required',
            'cc_co' => 'required',
            'remarks' => 'required',

        ], $pesan);

        PurchaseRequisitionsDetail::where('id', $id)
        ->update($validatedData);

        $request_number = $request->input('request_number');
        return Redirect::to('/edit-pr/'.$request_number)->with('pesan', 'Data berhasil diupdate.');
    }public function print_po($id)
    {
        // dd ($id);
        // die;
        $purchaseOrder = PurchaseOrders::findOrFail($id);
        $data_detail_rm = DB::table('purchase_order_details as a')
                ->select('a.type_product', 'b.description', 'a.qty', 'c.unit', 'a.price', 'a.discount', 'a.tax', 'a.amount', 'a.note','a.id')
                ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                ->where('a.id_purchase_orders', '=', $id)
                ->get();

        $data_detail_ta = DB::table('purchase_order_details as a')
                ->select('a.type_product', 'b.description', 'a.qty', 'c.unit', 'a.price', 'a.discount', 'a.tax', 'a.amount', 'a.note','a.id')
                ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                ->where('a.id_purchase_orders', '=', $id)
                ->get();

        $data_detail_wip = DB::table('purchase_order_details as a')
                ->select('a.type_product', 'b.description', 'a.qty', 'c.unit', 'a.price', 'a.discount', 'a.tax', 'a.amount', 'a.note','a.id')
                ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                ->where('a.id_purchase_orders', '=', $id)
                ->get();

        $data_detail_fg = DB::table('purchase_order_details as a')
                ->select('a.type_product', 'b.description', 'a.qty', 'c.unit', 'a.price', 'a.discount', 'a.tax', 'a.amount', 'a.note','a.id')
                ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                ->where('a.id_purchase_orders', '=', $id)
                ->get();

        $results = DB::table('purchase_orders as a')
                ->select(
                    'a.id',
                    'a.po_number',
                    'a.date',
                    'b.request_number',
                    'c.name',
                    'a.qc_check',
                    'a.down_payment',
                    'a.own_remarks',
                    'a.supplier_remarks',
                    'a.status',
                    'a.type',
                    'a.reference_number',
                    'a.id_master_suppliers'
                )
                ->leftJoin('purchase_requisitions as b', 'a.reference_number', '=', 'b.id')
                ->leftJoin('master_suppliers as c', 'a.id_master_suppliers', '=', 'c.id')
                ->where('a.id', '=', $id)
                ->get();

        return view('purchase.print_po',compact('purchaseOrder','data_detail_rm','data_detail_ta','data_detail_wip','data_detail_fg','results'));
    }public function print_pr($request_number)
    {
        // dd($request_number);
        // die;
        $PurchaseRequisitions = PurchaseRequisitions::findOrFail($request_number);

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
        ->where('purchase_requisitions.id', '=', $request_number)
        ->orderBy('purchase_requisitions.created_at', 'desc')
        ->get();

        $data_detail_rm = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code','b.rm_code')
                        ->where('a.id_purchase_requisitions', $request_number)
                        ->get();

        $data_detail_ta = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code','b.code')
                        ->where('a.id_purchase_requisitions', $request_number)
                        ->get();

        $data_detail_wip = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code','b.wip_code')
                        ->where('a.id_purchase_requisitions', $request_number)
                        ->get();

        $data_detail_fg = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code','b.product_code')
                        ->where('a.id_purchase_requisitions', $request_number)
                        ->get();

        return view('purchase.print_pr',compact('datas','data_detail_rm','data_detail_ta','data_detail_wip',
        'data_detail_fg','PurchaseRequisitions'));
    }public function purchase_requisition(Request $request)
    {
        // $data_detail_rm = DB::table('purchase_requisition_details as a')
        //                 ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
        //                 ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
        //                 ->select('a.*', 'b.description', 'c.unit_code')
        //                 ->limit(100)
        //                 ->get();

        // $data_requester = MstRequester::get();

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Purchase';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        if (request()->ajax()) {
            $orderColumn = $request->input('order')[0]['column'];
            $orderDirection = $request->input('order')[0]['dir'];
            $columns = ['id', 'type_product', 'desc', 'qty', 'unit_code', 'required_date', 'cc_co', 'remarks'];

            // Query dasar
            $query = DB::table('purchase_requisition_details as a')
                            ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
                            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                            ->select('a.*', 'b.description as desc', 'c.unit_code')
            ->orderBy($columns[$orderColumn], $orderDirection);

            // Handle pencarian
            if ($request->has('search') && $request->input('search')) {
                $searchValue = $request->input('search');
                $query->where(function ($query) use ($searchValue) {
                    $query->where('a.type_product', 'like', '%' . $searchValue . '%')
                        ->orWhere('b.desc', 'like', '%' . $searchValue . '%')
                        ->orWhere('a.qty', 'like', '%' . $searchValue . '%')
                        ->orWhere('c.unit_code', 'like', '%' . $searchValue . '%')
                        ->orWhere('a.required_date', 'like', '%' . $searchValue . '%')
                        ->orWhere('a.cc_co', 'like', '%' . $searchValue . '%')
                        ->orWhere('a.remarks', 'like', '%' . $searchValue . '%');
                });
            }
            return DataTables::of($query)
                
               
                ->addColumn('statusLabel', function ($data) {
                    return $data->status;
                })
                ->rawColumns(['action', 'status', 'statusLabel'])
                ->make(true);

        
        }

        return view('purchase.purchase_requisition');
    }public function print_pr_ind($request_number)
    {
        // dd($request_number);
        // die;
        $PurchaseRequisitions = PurchaseRequisitions::findOrFail($request_number);
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
        ->where('purchase_requisitions.id', '=', $request_number)
        ->orderBy('purchase_requisitions.created_at', 'desc')
        ->get();

        $data_detail_rm = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code','b.rm_code')
                        ->where('a.id_purchase_requisitions', $request_number)
                        ->get();

        $data_detail_ta = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code','b.code')
                        ->where('a.id_purchase_requisitions', $request_number)
                        ->get();

        $data_detail_wip = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code','b.wip_code')
                        ->where('a.id_purchase_requisitions', $request_number)
                        ->get();

        $data_detail_fg = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.*', 'b.description', 'c.unit_code','b.product_code')
                        ->where('a.id_purchase_requisitions', $request_number)
                        ->get();

        return view('purchase.print_pr_ind',compact('datas','data_detail_rm','data_detail_ta','data_detail_wip',
                'data_detail_fg','PurchaseRequisitions'));
    }public function print_po_ind($id)
    {
        $purchaseOrder = PurchaseOrders::findOrFail($id);
        $data_detail_rm = DB::table('purchase_order_details as a')
                ->select('a.type_product', 'b.description', 'a.qty', 'c.unit', 'a.price', 'a.discount', 'a.tax', 'a.amount', 'a.note','a.id')
                ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                ->where('a.id_purchase_orders', '=', $id)
                ->get();
                
        $data_detail_ta = DB::table('purchase_order_details as a')
                ->select('a.type_product', 'b.description', 'a.qty', 'c.unit', 'a.price', 'a.discount', 'a.tax', 'a.amount', 'a.note','a.id')
                ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                ->where('a.id_purchase_orders', '=', $id)
                ->get();

        $data_detail_wip = DB::table('purchase_order_details as a')
                ->select('a.type_product', 'b.description', 'a.qty', 'c.unit', 'a.price', 'a.discount', 'a.tax', 'a.amount', 'a.note','a.id')
                ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                ->where('a.id_purchase_orders', '=', $id)
                ->get();

        $data_detail_fg = DB::table('purchase_order_details as a')
                ->select('a.type_product', 'b.description', 'a.qty', 'c.unit', 'a.price', 'a.discount', 'a.tax', 'a.amount', 'a.note','a.id')
                ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                ->where('a.id_purchase_orders', '=', $id)
                ->get();

        $results = DB::table('purchase_orders as a')
                ->select(
                    'a.id',
                    'a.po_number',
                    'a.date',
                    'b.request_number',
                    'c.name',
                    'a.qc_check',
                    'a.down_payment',
                    'a.own_remarks',
                    'a.supplier_remarks',
                    'a.status',
                    'a.type',
                    'a.reference_number',
                    'a.id_master_suppliers'
                )
                ->leftJoin('purchase_requisitions as b', 'a.reference_number', '=', 'b.id')
                ->leftJoin('master_suppliers as c', 'a.id_master_suppliers', '=', 'c.id')
                ->where('a.id', '=', $id)
                ->get();

        return view('purchase.print_po_ind',compact('purchaseOrder','data_detail_rm','data_detail_ta','data_detail_wip','data_detail_fg','results'));
    }
    

}
