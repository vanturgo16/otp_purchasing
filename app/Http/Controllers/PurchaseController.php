<?php

namespace App\Http\Controllers;

use App\Models\MstCurrencies;
use App\Models\MstProductFG;
use App\Models\MstRawMaterial;
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
use App\Models\MstToolAux;
use App\Models\MstUnits;
use App\Models\MstWip;
use App\Models\PurchaseRequisitionsDetail;
use App\Models\PurchaseRequisitionsDetailSmt;
use App\Models\PurchaseOrderDetailsSMT;
use App\Models\PurchaseOrderDetails;

class PurchaseController extends Controller
{
    use AuditLogsTrait;

    // DATA PR
    public function indexPR(Request $request)
    {
        $datas = PurchaseRequisitions::select('purchase_requisitions.id', 'purchase_requisitions.request_number',
                'purchase_requisitions.date as requisition_date', 'purchase_requisitions.qc_check', 'purchase_requisitions.note',
                'purchase_requisitions.type', 'purchase_requisitions.status',
                'master_suppliers.name as supplier_name', 'master_requester.nm_requester', 'purchase_orders.po_number',
                \DB::raw('(SELECT COUNT(*) FROM purchase_requisition_details WHERE purchase_requisition_details.id_purchase_requisitions = purchase_requisitions.id) as count'))
            ->leftjoin('master_suppliers', 'purchase_requisitions.id_master_suppliers', 'master_suppliers.id')
            ->leftjoin('master_requester', 'purchase_requisitions.requester', 'master_requester.id')
            ->leftjoin('purchase_orders', 'purchase_requisitions.id', 'purchase_orders.reference_number')
            ->orderBy('purchase_requisitions.created_at', 'desc')
            ->get();

        // Datatables
        if ($request->ajax()) {
            return DataTables::of($datas)
                ->addColumn('action', function ($data){
                    return view('purchase-requisition.action', compact('data'));
                })->make(true);
        }

        //Audit Log
        $this->auditLogsShort('View List Purchase Requisition');
        return view('purchase-requisition.index');
    }
    public function addPR($type)
    {
        // dd($type);
        if(!in_array($type, ['RM', 'WIP', 'FG', 'TA', 'Other'])){
            return redirect()->route('dashboard')->with(['fail' => 'Tidak Ada Type '. $type]);
        }
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')->value(DB::raw('RIGHT(request_number, 7)'));
        $lastCode = $lastCode ? $lastCode : 0;
        $nextCode = $lastCode + 1;
        $formattedCode = 'PR' . date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);
        $suppliers = MstSupplier::get();
        $requesters = MstRequester::get();
        PurchaseRequisitionsDetailSmt::where('request_number', $formattedCode)->delete();

        return view('purchase-requisition.add', compact('type', 'formattedCode', 'suppliers', 'requesters'));
    }
    public function storePR(Request $request)
    {
        $request->validate([
            'request_number' => 'required',
            'date' => 'required',
            'id_master_suppliers' => $request->type == 'RM' || $request->type == 'Other' ? '' : 'required',
            'requester' => 'required',
            'qc_check' => 'required',
            'status' => 'required',
            'type' => 'required',
        ], [
            'request_number.required' => 'Request Number masih kosong.',
            'date.required' => 'Date harus diisi.',
            'id_master_suppliers.required' => 'Supplier harus diisi.',
            'requester.required' => 'Requester harus diisi.',
            'qc_check.required' => 'QC Check harus diisi.',
            'status.required' => 'Status harus diisi.',
            'type.required' => 'Type masih kosong.',
        ]);
        
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')->value(DB::raw('RIGHT(request_number, 7)'));
        $lastCode = $lastCode ? $lastCode : 0;
        $nextCode = $lastCode + 1;
        $formattedCode = 'PR' . date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);
        PurchaseRequisitionsDetailSmt::where('request_number', $formattedCode)->delete();
        
        DB::beginTransaction();
        try{
            $storeData = PurchaseRequisitions::create([
                'request_number' => $formattedCode,
                'date' => $request->date,
                'id_master_suppliers' => $request->id_master_suppliers,
                'requester' => $request->requester,
                'qc_check' => $request->qc_check,
                'note' => $request->note,
                'status' => $request->status,
                'type' => $request->type,
            ]);

            // Audit Log
            $this->auditLogsShort('Tambah Purchase Requisitions');
            DB::commit();
            return redirect()->route('pr.edit', encrypt($storeData->id))->with(['success' => 'Berhasil Tambah Data PR, Silahkan Tambahkan Item Produk']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Tambah Data PR!']);
        }
    }
    public function editPR($id)
    {
        $id = decrypt($id);
        // dd($request_number);
        $data = PurchaseRequisitions::where('id', $id)->first();
        if($data->type == 'RM'){
            $products = MstRawMaterial::select('id', 'description')->get();
        } elseif($data->type == 'WIP'){
            $products = MstWip::select('id', 'description')->get();
        } elseif($data->type == 'FG'){
            $products = MstProductFG::select('id', 'description', 'perforasi', 'group_sub_code')->get();
        } elseif($data->type == 'TA'){
            $products = MstToolAux::select('id', 'description')->where('type', '!=', 'Other')->get();
        } elseif($data->type == 'Other'){
            $products = MstToolAux::select('id', 'description')->where('type', 'Other')->get();
        } else {
            $products = [];
        }
        $suppliers = MstSupplier::get();
        $units = MstUnits::select('id', 'unit_code')->get();
        $requesters = MstRequester::get();

        $itemDatas = PurchaseRequisitionsDetail::select(
            'purchase_requisition_details.*',
            'master_units.unit',
            'master_requester.nm_requester as cc_co_name',
            DB::raw('
                CASE 
                    WHEN purchase_requisition_details.type_product = "RM" THEN master_raw_materials.description 
                    WHEN purchase_requisition_details.type_product = "WIP" THEN master_wips.description 
                    WHEN purchase_requisition_details.type_product = "FG" THEN master_product_fgs.description 
                    WHEN purchase_requisition_details.type_product IN ("TA", "Other") THEN master_tool_auxiliaries.description 
                END as product_desc')
        )
            ->leftJoin('master_raw_materials', function ($join) {
                $join->on('purchase_requisition_details.master_products_id', '=', 'master_raw_materials.id')
                    ->on('purchase_requisition_details.type_product', '=', DB::raw('"RM"'));
            })
            ->leftJoin('master_wips', function ($join) {
                $join->on('purchase_requisition_details.master_products_id', '=', 'master_wips.id')
                    ->on('purchase_requisition_details.type_product', '=', DB::raw('"WIP"'));
            })
            ->leftJoin('master_product_fgs', function ($join) {
                $join->on('purchase_requisition_details.master_products_id', '=', 'master_product_fgs.id')
                    ->on('purchase_requisition_details.type_product', '=', DB::raw('"FG"'));
            })
            ->leftJoin('master_tool_auxiliaries', function ($join) {
                $join->on('purchase_requisition_details.master_products_id', '=', 'master_tool_auxiliaries.id')
                    ->on('purchase_requisition_details.type_product', '=', DB::raw('"TA"'))
                    ->orOn('purchase_requisition_details.type_product', '=', DB::raw('"Other"'));
            })
            ->leftJoin('master_units', 'purchase_requisition_details.master_units_id', '=', 'master_units.id')
            ->leftJoin('master_requester', 'purchase_requisition_details.cc_co', '=', 'master_requester.id')
            ->where('purchase_requisition_details.id_purchase_requisitions', $data->id)
            ->orderBy('purchase_requisition_details.created_at')
            ->get();
            
        return view('purchase-requisition.edit', compact('data', 'products', 'suppliers', 'units', 'requesters', 'itemDatas'));
    }
    public function updatePR(Request $request, $id)
    {
        $id = decrypt($id);
        $request->validate([
            'request_number' => 'required',
            'date' => 'required',
            'id_master_suppliers' => $request->type == 'RM' || $request->type == 'Other' ? '' : 'required',
            'requester' => 'required',
            'qc_check' => 'required',
            'status' => 'required',
            'type' => 'required',
        ], [
            'request_number.required' => 'Request Number masih kosong.',
            'date.required' => 'Date harus diisi.',
            'id_master_suppliers.required' => 'Supplier harus diisi.',
            'requester.required' => 'Requester harus diisi.',
            'qc_check.required' => 'QC Check harus diisi.',
            'status.required' => 'Status harus diisi.',
            'type.required' => 'Type masih kosong.',
        ]);

        $dataBefore = PurchaseRequisitions::where('id', $id)->first();
        $dataBefore->date = $request->date;
        $dataBefore->id_master_suppliers = $request->id_master_suppliers;
        $dataBefore->requester = $request->requester;
        $dataBefore->qc_check = $request->qc_check;
        $dataBefore->note = $request->note;

        if($dataBefore->isDirty()){
            DB::beginTransaction();
            try{
                PurchaseRequisitions::where('id', $id)->update([
                    'date' => $request->date,
                    'id_master_suppliers' => $request->id_master_suppliers,
                    'requester' => $request->requester,
                    'qc_check' => $request->qc_check,
                    'note' => $request->note,
                ]);
    
                // Audit Log
                $this->auditLogsShort('Update Purchase Requisitions ID : (' . $id . ')');
                DB::commit();
                return redirect()->back()->with(['success' => 'Berhasil Update Data PR']);
            } catch (Exception $e) {
                DB::rollback();
                return redirect()->back()->with(['fail' => 'Gagal Update Data PR!']);
            }
        } else {
            return redirect()->back()->with(['info' => 'Tidak Ada Yang Dirubah, Data Sama Dengan Sebelumnya']);
        }
    }
    public function deletePR($id)
    {
        $id = decrypt($id);
        DB::beginTransaction();
        try{
            PurchaseRequisitions::where('id', $id)->delete();
            PurchaseRequisitionsDetail::where('id_purchase_requisitions', $id)->delete();

            // Audit Log
            $this->auditLogsShort('Hapus Purchase Requisitions');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Hapus Data PR']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Hapus Data PR!']);
        }
    }
    public function postedPR($id)
    {
        $id = decrypt($id);
        DB::beginTransaction();
        try{
            PurchaseRequisitions::where('id', $id)->update(['status' => 'Posted']);

            // Audit Log
            $this->auditLogsShort('Posted Purchase Requisitions');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Posted Data PR']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Posted Data PR!']);
        }
    }
    public function unpostedPR($id)
    {
        $id = decrypt($id);
        DB::beginTransaction();
        try{
            PurchaseRequisitions::where('id', $id)->update(['status' => 'Un Posted']);

            // Audit Log
            $this->auditLogsShort('Un-Posted Purchase Requisitions');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Un-Posted Data PR']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Un-Posted Data PR!']);
        }
    }
    //ITEM PR
    public function storeItemPR(Request $request, $id)
    {
        $id = decrypt($id); //ID PR
        $request->validate([
            'request_number' => 'required',
            'type_product' => 'required',
            'master_products_id' => 'required',
            'qty' => 'required',
            'master_units_id' => 'required',
            'required_date' => 'required',
            'cc_co' => 'required',
        ], [
            'request_number.required' => 'Request Number masih kosong.',
            'type_product.required' => 'Type Produk masih kosong.',
            'master_products_id.required' => 'Produk harus diisi.',
            'qty.required' => 'Qty harus diisi.',
            'master_units_id.required' => 'Unit harus diisi.',
            'required_date.required' => 'Required Date harus diisi.',
            'cc_co.required' => 'CC / CO harus diisi.',
        ]);
        
        DB::beginTransaction();
        try{
            $storeData = PurchaseRequisitionsDetail::create([
                'id_purchase_requisitions' => $id,
                'type_product' => $request->type_product,
                'master_products_id' => $request->master_products_id,
                'qty' => $request->qty,
                'master_units_id' => $request->master_units_id,
                'required_date' => $request->required_date,
                'cc_co' => $request->cc_co,
                'remarks' => $request->remarks,
                'request_number' => $request->request_number,
            ]);

            // Audit Log
            $this->auditLogsShort('Tambah Purchase Requisitions Detail ID : (' . $storeData->id . ')');
            DB::commit();
            return redirect()->route('pr.edit', encrypt($id))->with(['success' => 'Berhasil Tambah Item PR Ke Dalam Tabel', 'scrollTo' => 'tableItem']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Tambah Item PR!']);
        }
    }
    public function editItemPR($id)
    {
        $id = decrypt($id);
        $data = PurchaseRequisitionsDetail::where('id', $id)->first();
        if($data->type_product == 'RM'){
            $products = MstRawMaterial::select('id', 'description')->get();
        } elseif($data->type_product == 'WIP'){
            $products = MstWip::select('id', 'description')->get();
        } elseif($data->type_product == 'FG'){
            $products = MstProductFG::select('id', 'description', 'perforasi', 'group_sub_code')->get();
        } elseif($data->type_product == 'TA'){
            $products = MstToolAux::select('id', 'description')->where('type', '!=', 'Other')->get();
        } elseif($data->type_product == 'Other'){
            $products = MstToolAux::select('id', 'description')->where('type', 'Other')->get();
        } else {
            $products = [];
        }
        $units = MstUnits::select('id', 'unit_code')->get();
        $requesters = MstRequester::get();
            
        return view('purchase-requisition-detail.edit', compact('data', 'products', 'units', 'requesters'));
    }
    public function updateItemPR(Request $request, $id)
    {
        $id = decrypt($id);
        $request->validate([
            'master_products_id' => 'required',
            'qty' => 'required',
            'master_units_id' => 'required',
            'required_date' => 'required',
            'cc_co' => 'required',
        ], [
            'master_products_id.required' => 'Produk harus diisi.',
            'qty.required' => 'Qty harus diisi.',
            'master_units_id.required' => 'Unit harus diisi.',
            'required_date.required' => 'Required Date harus diisi.',
            'cc_co.required' => 'CC / CO harus diisi.',
        ]);

        $dataBefore = PurchaseRequisitionsDetail::where('id', $id)->first();
        $dataBefore->master_products_id = $request->master_products_id;
        $dataBefore->qty = $request->qty;
        $dataBefore->master_units_id = $request->master_units_id;
        $dataBefore->required_date = $request->required_date;
        $dataBefore->cc_co = $request->cc_co;
        $dataBefore->remarks = $request->remarks;

        if($dataBefore->isDirty()){
            DB::beginTransaction();
            try{
                PurchaseRequisitionsDetail::where('id', $id)->update([
                    'master_products_id' => $request->master_products_id,
                    'qty' => $request->qty,
                    'master_units_id' => $request->master_units_id,
                    'required_date' => $request->required_date,
                    'cc_co' => $request->cc_co,
                    'remarks' => $request->remarks
                ]);

                // Audit Log
                $this->auditLogsShort('Update Purchase Requisitions Detail ID : (' . $id . ')');
                DB::commit();
                return redirect()->route('pr.edit', encrypt($dataBefore->id_purchase_requisitions))->with(['success' => 'Berhasil Update Item PR', 'scrollTo' => 'tableItem']);
            } catch (Exception $e) {
                DB::rollback();
                return redirect()->back()->with(['fail' => 'Gagal Update Item PR!']);
            }
        } else {
            return redirect()->back()->with(['info' => 'Tidak Ada Yang Dirubah, Data Sama Dengan Sebelumnya']);
        }
    }
    public function deleteItemPR($id)
    {
        $id = decrypt($id);
        DB::beginTransaction();
        try{
            PurchaseRequisitionsDetail::where('id', $id)->delete();

            // Audit Log
            $this->auditLogsShort('Hapus Purchase Requisitions Detail ID : (' . $id . ')');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Hapus Item PR', 'scrollTo' => 'tableItem']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Hapus Item PR!']);
        }
    }

    public function indexOld(Request $request)
    {
        // $datas = PurchaseRequisitions::leftJoin('master_suppliers as b', 'purchase_requisitions.id_master_suppliers', '=', 'b.id')
        //         ->leftJoin('master_requester as c', 'purchase_requisitions.requester', '=', 'c.id')
        //         ->select('purchase_requisitions.*', 'b.name', 'c.nm_requester')
        //         ->orderBy('purchase_requisitions.created_at', 'desc')
        //         ->get();
        // $data_requester = MstRequester::get();

        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'View List Purchase';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        if (request()->ajax()) {
            $orderColumn = $request->input('order')[0]['column'];
            $orderDirection = $request->input('order')[0]['dir'];
            $columns = ['id', 'request_number', 'requisition_date', 'supplier_name', 'nm_requester', 'qc_check', 'note', 'po_number', 'type', '', ''];

            // Query dasar
            $query = PurchaseRequisitions::leftJoin('master_suppliers as b', 'purchase_requisitions.id_master_suppliers', '=', 'b.id')
                ->leftJoin('master_requester as c', 'purchase_requisitions.requester', '=', 'c.id')
                ->leftJoin('purchase_orders as d', 'purchase_requisitions.id', '=', 'd.reference_number')
                ->select(
                    'purchase_requisitions.id',
                    'purchase_requisitions.request_number',
                    'purchase_requisitions.date as requisition_date', // Aliaskan kolom 'date'
                    'b.name as supplier_name', // Aliaskan kolom 'name' dari tabel 'master_suppliers'
                    'c.nm_requester',
                    'purchase_requisitions.qc_check',
                    'purchase_requisitions.note',
                    'd.po_number',
                    'purchase_requisitions.type',
                    'purchase_requisitions.status'
                )
                ->orderBy($columns[$orderColumn], $orderDirection);

            // Handle pencarian
            if ($request->has('search') && $request->input('search')) {
                $searchValue = $request->input('search');
                $query->where(function ($query) use ($searchValue) {
                    $query->where('purchase_requisitions.request_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('purchase_requisitions.date', 'like', '%' . $searchValue . '%')
                        ->orWhere('b.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('c.nm_requester', 'like', '%' . $searchValue . '%')
                        ->orWhere('purchase_requisitions.qc_check', 'like', '%' . $searchValue . '%')
                        ->orWhere('purchase_requisitions.note', 'like', '%' . $searchValue . '%')
                        ->orWhere('d.po_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('purchase_requisitions.type', 'like', '%' . $searchValue . '%');
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
    public function purchase_requisition_cari(Request $request, $request_number)
    {

        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'View List Purchase';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        $datas = PurchaseRequisitions::leftJoin('master_suppliers as b', 'purchase_requisitions.id_master_suppliers', '=', 'b.id')
            ->leftJoin('master_requester as c', 'purchase_requisitions.requester', '=', 'c.id')
            ->select('purchase_requisitions.*', 'b.name', 'c.nm_requester')
            ->where('purchase_requisitions.request_number', '=', $request_number) // Kondisi WHERE
            ->orderBy('purchase_requisitions.created_at', 'desc')
            ->get();

        return view('purchase.purchase_purchase_cari', compact('datas'));
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

    public function purchase_order(Request $request)
    {
        $datas = PurchaseRequisitions::get();
        // $datas = PurchaseOrders::leftJoin('master_suppliers', 'purchase_orders.id_master_suppliers', '=', 'master_suppliers.id')
        //         ->leftJoin('purchase_requisitions', 'purchase_orders.reference_number', '=', 'purchase_requisitions.id')
        //         ->select('purchase_orders.*', 'master_suppliers.name', 'purchase_requisitions.request_number')
        //         ->orderBy('purchase_orders.created_at', 'desc') // Menambahkan pengurutan berdasarkan created_at desc
        //         ->get();

        $supplier = MstSupplier::get();


        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'View List Purchase Order';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        if (request()->ajax()) {
            $orderColumn = $request->input('order')[0]['column'];
            $orderDirection = $request->input('order')[0]['dir'];
            $columns = ['id', 'request_number', 'date', 'name', 'nm_requester', 'qc_check', 'note', '', 'type', '', ''];

            // Query dasar
            $query = PurchaseOrders::leftJoin('master_suppliers', 'purchase_orders.id_master_suppliers', '=', 'master_suppliers.id')
                ->leftJoin('purchase_requisitions', 'purchase_orders.reference_number', '=', 'purchase_requisitions.id')
                ->select(
                    'purchase_orders.id',
                    'purchase_orders.po_number',
                    'purchase_orders.date',
                    'purchase_orders.down_payment',
                    'purchase_orders.total_amount',
                    'purchase_orders.qc_check',
                    'purchase_orders.type',
                    'purchase_orders.status',
                    'master_suppliers.name as supplier_name',
                    'purchase_requisitions.request_number as reference_number'
                )
                ->orderBy($columns[$orderColumn], $orderDirection);




            // Handle pencarian
            if ($request->has('search') && $request->input('search')) {
                $searchValue = $request->input('search');
                $query->where(function ($query) use ($searchValue) {
                    $query->where('purchase_requisitions.request_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('purchase_orders.date', 'like', '%' . $searchValue . '%')
                        ->orWhere('master_suppliers.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('purchase_orders.qc_check', 'like', '%' . $searchValue . '%')
                        ->orWhere('purchase_orders.type', 'like', '%' . $searchValue . '%')
                        ->orWhere('purchase_orders.status', 'like', '%' . $searchValue . '%');
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
                ->rawColumns(['action', 'status', 'statusLabel', 'pr'])
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
    public function hapus_request_number()
    {
        // Ambil nomor urut terakhir dari database
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')
            ->value(DB::raw('RIGHT(request_number, 7)'));

        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Format kode dengan panjang 7 karakter
        $formattedCode = 'PR' . date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        PurchaseRequisitionsDetailSmt::where('request_number', $formattedCode)->delete();

        //  return redirect()->route('http://localhost/add-pr-rm');
        return redirect()->intended('/add-pr-rm');
    }
    public function hapus_request_number_wip()
    {
        // Ambil nomor urut terakhir dari database
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')
            ->value(DB::raw('RIGHT(request_number, 7)'));

        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Format kode dengan panjang 7 karakter
        $formattedCode = 'PR' . date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        PurchaseRequisitionsDetailSmt::where('request_number', $formattedCode)->delete();

        //  return redirect()->route('http://localhost/add-pr-rm');
        return redirect()->intended('/add-pr-wip');
    }
    public function hapus_request_number_fg()
    {
        // Ambil nomor urut terakhir dari database
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')
            ->value(DB::raw('RIGHT(request_number, 7)'));

        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Format kode dengan panjang 7 karakter
        $formattedCode = 'PR' . date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        PurchaseRequisitionsDetailSmt::where('request_number', $formattedCode)->delete();

        //  return redirect()->route('http://localhost/add-pr-rm');
        return redirect()->intended('/add-pr-fg');
    }
    public function hapus_request_number_ta()
    {
        // Ambil nomor urut terakhir dari database
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')
            ->value(DB::raw('RIGHT(request_number, 7)'));

        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Format kode dengan panjang 7 karakter
        $formattedCode = 'PR' . date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        PurchaseRequisitionsDetailSmt::where('request_number', $formattedCode)->delete();

        //  return redirect()->route('http://localhost/add-pr-rm');
        return redirect()->intended('/add-pr-sparepart');
    }
    public function hapus_request_number_other()
    {
        // Ambil nomor urut terakhir dari database
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')
            ->value(DB::raw('RIGHT(request_number, 7)'));

        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Format kode dengan panjang 7 karakter
        $formattedCode = 'PR' . date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        PurchaseRequisitionsDetailSmt::where('request_number', $formattedCode)->delete();

        //  return redirect()->route('http://localhost/add-pr-rm');
        return redirect()->intended('/add-pr-other');
    }
    public function tambah_pr_rm()
    {
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $rawMaterials = DB::table('master_raw_materials')
            ->select('description', 'id')
            ->get();
        $units = DB::table('master_units')
            ->select('unit_code', 'id')
            ->get();

        // Ambil nomor urut terakhir dari database
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')
            ->value(DB::raw('RIGHT(request_number, 7)'));

        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Format kode dengan panjang 7 karakter
        $formattedCode = 'PR' . date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        // $dt_detailSmt = PurchaseRequisitionsDetailSmt::where('request_number', $formattedCode)->get();
        $dt_detailSmt = DB::table('purchase_requisition_details_sementara as a')
            ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->select('a.*', 'b.description', 'c.unit_code')
            ->where('a.request_number', $formattedCode)
            ->get();

        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'Add Purchase Order RM';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        return view('purchase.tambah_pr_rm', compact(
            'datas',
            'supplier',
            'rawMaterials',
            'units',
            'formattedCode',
            'dt_detailSmt'
        ));
    }
    public function tambah_pr_wip()
    {
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $wip = DB::table('master_wips')
            ->select('description', 'id')
            ->get();
        $units = DB::table('master_units')
            ->select('unit_code', 'id')
            ->get();

        // Ambil nomor urut terakhir dari database
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')
            ->value(DB::raw('RIGHT(request_number, 7)'));

        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Format kode dengan panjang 7 karakter
        $formattedCode = 'PR' . date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        $dt_detailSmt = DB::table('purchase_requisition_details_sementara as a')
            ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->select('a.*', 'b.description', 'c.unit_code')
            ->where('a.request_number', $formattedCode)
            ->get();

        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'Add Purchase Order WIP';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        return view('purchase.tambah_pr_wip', compact(
            'datas',
            'supplier',
            'wip',
            'units',
            'formattedCode',
            'dt_detailSmt'
        ));
    }
    public function tambah_pr_fg()
    {
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $fg = DB::table('master_product_fgs')
            ->select('description', 'id')
            ->get();
        $units = DB::table('master_units')
            ->select('unit_code', 'id')
            ->get();

        // Ambil nomor urut terakhir dari database
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')
            ->value(DB::raw('RIGHT(request_number, 7)'));

        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Format kode dengan panjang 7 karakter
        $formattedCode = 'PR' . date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        $dt_detailSmt = DB::table('purchase_requisition_details_sementara as a')
            ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->select('a.*', 'b.description', 'c.unit_code')
            ->where('a.request_number', $formattedCode)
            ->get();

        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'Add Purchase Order FG';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        return view('purchase.tambah_pr_fg', compact('datas', 'supplier', 'fg', 'units', 'formattedCode', 'dt_detailSmt'));
    }
    public function tambah_pr_sparepart()
    {
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $ta = DB::table('master_tool_auxiliaries')
            ->select('description', 'id')
            ->get();
        $units = DB::table('master_units')
            ->select('unit_code', 'id')
            ->get();

        // Ambil nomor urut terakhir dari database
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')
            ->value(DB::raw('RIGHT(request_number, 7)'));

        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Format kode dengan panjang 7 karakter
        $formattedCode = 'PR' . date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        $dt_detailSmt = DB::table('purchase_requisition_details_sementara as a')
            ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->select('a.*', 'b.description', 'c.unit_code')
            ->where('a.request_number', $formattedCode)
            ->get();


        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'Add Purchase Order Sparepart';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        return view('purchase.tambah_pr_sparepart', compact('datas', 'supplier', 'ta', 'units', 'formattedCode', 'dt_detailSmt'));
    }
    public function tambah_pr_other()
    {
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $rawMaterials = DB::table('master_raw_materials')
            ->select('description', 'id')
            ->get();
        $units = DB::table('master_units')
            ->select('unit_code', 'id')
            ->get();

        // Ambil nomor urut terakhir dari database
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')
            ->value(DB::raw('RIGHT(request_number, 7)'));

        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Format kode dengan panjang 7 karakter
        $formattedCode = 'PR' . date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        // $dt_detailSmt = PurchaseRequisitionsDetailSmt::where('request_number', $formattedCode)->get();
        $dt_detailSmt = DB::table('purchase_requisition_details_sementara as a')
            ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->select('a.*', 'b.description', 'c.unit_code')
            ->where('a.request_number', $formattedCode)
            ->get();

        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'Add Purchase Order RM';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        return view('purchase.tambah_pr_other', compact(
            'datas',
            'supplier',
            'rawMaterials',
            'units',
            'formattedCode',
            'dt_detailSmt'
        ));
    }
    public function get_supplier()
    {
        $data = DB::select("SELECT master_suppliers.name,master_suppliers.id  FROM master_suppliers");
        $data['rn'] = DB::select("SELECT purchase_requisitions.request_number,purchase_requisitions.id FROM `purchase_requisitions` where purchase_requisitions.status not in ('Request','Closed','Created PO','Un Posted') ");
        $id = request()->get('id');
        $pr_detail = PurchaseRequisitions::with('masterSupplier')
            ->where('id', $id)
            ->first();
        return response()->json(['data' => $data, 'pr_detail' => $pr_detail]);
    }
    public function get_unit()
    {
        $data = DB::select("SELECT master_units.unit_code,master_units.id,master_units.unit  FROM master_units");
        $id = request()->get('id');
        $po_detail = PurchaseOrderDetails::with('masterUnit')
            ->where('id', $id)
            ->first();
        return response()->json(['data' => $data, 'po_detail' => $po_detail]);
    }
    public function simpan_po(Request $request)
    {
        // dd($request);
        // die;
        $pesan = [
            'po_number.required' => 'po number masih kosong',
            'date.required' => 'date masih kosong',
            'delivery_date.required' => 'date masih kosong',
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
            'delivery_date' => 'nullable',
            'reference_number' => 'required',
            'id_master_suppliers' => 'nullable',
            'qc_check' => 'required',
            'non_invoiceable' => 'required',
            'vendor_taxable' => 'required',
            'down_payment' => 'required',
            'own_remarks' => 'nullable',
            'supplier_remarks' => 'nullable',
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
            return redirect('/tambah_detail_po/' . $reference_number . '/' . $id);
        } else {
            // Penanganan jika $id tidak ditemukan
            return redirect()->back()->with('error', 'ID tidak ditemukan');
        }
    }
    public function simpan_detail_rm(Request $request, $request_number)
    {

        // dd($request_number);
        // die;

        $id = PurchaseRequisitions::where('request_number', $request_number)->value('id');
        $request_number = $request_number;
        $request->merge([
            'request_number' => $request_number, // Ganti 'request_number' dengan nilai variabel buatan Anda
            'id_purchase_requisitions' => $id,
        ]);

        if ($request->has('save_detail')) {
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
            return Redirect::to('/detail-pr/' . $request_number)->with('pesan', 'Data berhasil disimpan.');
            // return Redirect::to('/detail-pr/'.$request_number);
        } elseif ($request->has('hapus_detail')) {
            $validatedData = $request->input('hapus_detail');

            // dd($id);
            // die;
            PurchaseRequisitionsDetail::destroy($validatedData);
            return Redirect::to('/detail-pr/' . $request_number)->with('pesan', 'Data berhasil dihapus.');

            // return "Tombol Save detail diklik.";
        }
    }
    public function simpan_pr_rm(Request $request)
    {
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
                'requester.required' => 'requester masih kosong',
                'qc_check.required' => 'qc_check masih kosong',
                'status.required' => 'status masih kosong',
                'type.required' => 'type masih kosong',

            ];

            $validatedData = $request->validate([
                'request_number' => 'required',
                'date' => 'required',
                'id_master_suppliers' => 'nullable',
                'requester' => 'required',
                'qc_check' => 'required',
                'note' => 'nullable',
                'status' => 'required',
                'type' => 'required',

            ], $pesan);

            // dd($validatedData);
            // die;
            $request_number = $request->input('request_number');

            PurchaseRequisitions::create($validatedData);

            return Redirect::to('/detail-pr/' . $request_number);
        }
    }
    public function detail_pr($request_number)
    {
        // dd($request_number);
        // die;
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $rawMaterials = DB::table('master_raw_materials')
            ->select('description', 'id')
            ->get();
        $ta = DB::table('master_tool_auxiliaries')
            ->select('description', 'id')
            ->get();
        $fg = DB::table('master_product_fgs')
            ->select('description', 'id')
            ->get();
        $wip = DB::table('master_wips')
            ->select('description', 'id')
            ->get();
        $units = DB::table('master_units')
            ->select('unit_code', 'id')
            ->get();

        $findtype = DB::table('purchase_requisitions as a')
            ->select('a.type')
            ->where('a.request_number', $request_number)
            ->first();

        $dt_detailSmt = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'd.nm_requester')
            ->where('a.request_number', $request_number)
            ->get();

        $data_detail_ta = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'd.nm_requester')
            ->where('a.request_number', $request_number)
            ->get();

        $data_detail_fg = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'd.nm_requester')
            ->where('a.request_number', $request_number)
            ->get();

        $data_detail_wip = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'd.nm_requester')
            ->where('a.request_number', $request_number)
            ->get();

        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'Add Purchase Order RM';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        return view('purchase.detail_pr', compact(
            'datas',
            'supplier',
            'rawMaterials',
            'units',
            'dt_detailSmt',
            'request_number',
            'data_detail_ta',
            'data_detail_fg',
            'data_detail_wip',
            'findtype'
        ));
    }
    public function simpan_detail_wip(Request $request, $request_number)
    {

        $id = PurchaseRequisitions::where('request_number', $request_number)->value('id');
        $request_number = $request_number;
        $request->merge([
            'request_number' => $request_number, // Ganti 'request_number' dengan nilai variabel buatan Anda
            'id_purchase_requisitions' => $id,
        ]);
        if ($request->has('save_detail')) {
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
            return Redirect::to('/detail-pr-wip/' . $request_number)->with('pesan', 'Data berhasil disimpan.');
            // return Redirect::to('/detail-pr/'.$request_number);
        } elseif ($request->has('hapus_detail')) {
            $validatedData = $request->input('hapus_detail');

            // dd($id);
            // die;
            PurchaseRequisitionsDetail::destroy($validatedData);
            return Redirect::to('/detail-pr-wip/' . $request_number)->with('pesan', 'Data berhasil dihapus.');

            // return "Tombol Save detail diklik.";
        }
    }
    public function simpan_pr_wip(Request $request)
    {
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
                'status.required' => 'status masih kosong',
                'type.required' => 'type masih kosong',

            ];

            $validatedData = $request->validate([
                'request_number' => 'required',
                'date' => 'required',
                'id_master_suppliers' => 'nullable',
                'requester' => 'required',
                'qc_check' => 'required',
                'note' => 'nullable',
                'status' => 'required',
                'type' => 'required',

            ], $pesan);

            // dd($validatedData);
            // die;
            $request_number = $request->input('request_number');

            PurchaseRequisitions::create($validatedData);

            return Redirect::to('/detail-pr-wip/' . $request_number);
        }
    }
    public function detail_pr_wip($request_number)
    {
        // dd($request_number);
        // die;
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $wip = DB::table('master_wips')
            ->select('description', 'id')
            ->get();
        $units = DB::table('master_units')
            ->select('unit_code', 'id')
            ->get();

        $dt_detailSmt = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'd.nm_requester')
            ->where('a.request_number', $request_number)
            ->get();

        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'Add Purchase Order WIP';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        return view('purchase.detail_pr_wip', compact(
            'datas',
            'supplier',
            'wip',
            'units',
            'dt_detailSmt',
            'request_number'
        ));
    }
    public function simpan_detail_fg(Request $request, $request_number)
    {
        // dd($request_number);
        // die;

        $id = PurchaseRequisitions::where('request_number', $request_number)->value('id');
        $request_number = $request_number;
        $request->merge([
            'request_number' => $request_number, // Ganti 'request_number' dengan nilai variabel buatan Anda
            'id_purchase_requisitions' => $id,
        ]);
        if ($request->has('save_detail')) {
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
            return Redirect::to('/detail-pr-fg/' . $request_number)->with('pesan', 'Data berhasil disimpan.');
            // return Redirect::to('/detail-pr/'.$request_number);
        } elseif ($request->has('hapus_detail')) {
            $validatedData = $request->input('hapus_detail');

            // dd($id);
            // die;
            PurchaseRequisitionsDetail::destroy($validatedData);
            return Redirect::to('/detail-pr-fg/' . $request_number)->with('pesan', 'Data berhasil dihapus.');

            // return "Tombol Save detail diklik.";
        }
    }
    public function simpan_pr_fg(Request $request)
    {
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
                'status.required' => 'status masih kosong',
                'type.required' => 'type masih kosong',
            ];

            $validatedData = $request->validate([
                'request_number' => 'required',
                'date' => 'required',
                'id_master_suppliers' => 'nullable',
                'requester' => 'required',
                'qc_check' => 'required',
                'note' => 'nullable',
                'status' => 'required',
                'type' => 'required',

            ], $pesan);

            $request_number = $request->input('request_number');

            PurchaseRequisitions::create($validatedData);

            return Redirect::to('/detail-pr-fg/' . $request_number);
        }
    }
    public function detail_pr_fg($request_number)
    {
        // dd($request_number);
        // die;
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $fg = DB::table('master_product_fgs')
            ->select('description', 'id', 'perforasi', 'group_sub_code')
            ->get();
        $units = DB::table('master_units')
            ->select('unit_code', 'id')
            ->get();

        $dt_detailSmt = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'd.nm_requester')
            ->where('a.request_number', $request_number)
            ->get();

        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'Add Purchase Order WIP';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        return view('purchase.detail_pr_fg', compact(
            'datas',
            'supplier',
            'fg',
            'units',
            'dt_detailSmt',
            'request_number'
        ));
    }
    public function simpan_detail_ta(Request $request, $request_number)
    {

        $id = PurchaseRequisitions::where('request_number', $request_number)->value('id');
        $request_number = $request_number;
        $request->merge([
            'request_number' => $request_number, // Ganti 'request_number' dengan nilai variabel buatan Anda
            'id_purchase_requisitions' => $id,
        ]);
        if ($request->has('save_detail')) {
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
            return Redirect::to('/detail-pr-sparepart/' . $request_number)->with('pesan', 'Data berhasil disimpan.');
            // return Redirect::to('/detail-pr/'.$request_number);
        } elseif ($request->has('hapus_detail')) {
            $validatedData = $request->input('hapus_detail');

            // dd($id);
            // die;
            PurchaseRequisitionsDetail::destroy($validatedData);
            return Redirect::to('/detail-pr-sparepart/' . $request_number)->with('pesan', 'Data berhasil dihapus.');

            // return "Tombol Save detail diklik.";
        }
    }

    public function simpan_pr_ta(Request $request)
    {
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
                'status.required' => 'status masih kosong',
                'type.required' => 'type masih kosong',
            ];

            $validatedData = $request->validate([
                'request_number' => 'required',
                'date' => 'required',
                'id_master_suppliers' => 'nullable',
                'requester' => 'required',
                'qc_check' => 'required',
                'note' => 'nullable',
                'status' => 'required',
                'type' => 'required',

            ], $pesan);

            $request_number = $request->input('request_number');

            PurchaseRequisitions::create($validatedData);

            return Redirect::to('/detail-pr-sparepart/' . $request_number);
        }
    }
    public function detail_pr_sparepart($request_number)
    {
        // dd($request_number);
        // die;
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $ta = DB::table('master_tool_auxiliaries')
            ->select('description', 'id')
            ->get();
        $units = DB::table('master_units')
            ->select('unit_code', 'id')
            ->get();

        $dt_detailSmt = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'd.nm_requester')
            ->where('a.request_number', $request_number)
            ->get();

        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'Add Purchase Order WIP';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        return view('purchase.detail_pr_sparepart', compact(
            'datas',
            'supplier',
            'ta',
            'units',
            'dt_detailSmt',
            'request_number'
        ));
    }
    public function simpan_pr_other(Request $request)
    {
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
                'requester.required' => 'requester masih kosong',
                'qc_check.required' => 'qc_check masih kosong',
                'status.required' => 'status masih kosong',
                'type.required' => 'type masih kosong',

            ];

            $validatedData = $request->validate([
                'request_number' => 'required',
                'date' => 'required',
                'id_master_suppliers' => 'nullable',
                'requester' => 'required',
                'qc_check' => 'required',
                'note' => 'nullable',
                'status' => 'required',
                'type' => 'required',

            ], $pesan);

            // dd($validatedData);
            // die;
            $request_number = $request->input('request_number');

            PurchaseRequisitions::create($validatedData);

            return Redirect::to('/detail-pr-other/' . $request_number);
        }
    }
    public function simpan_detail_other(Request $request, $request_number)
    {

        // dd($request_number);
        // die;

        $id = PurchaseRequisitions::where('request_number', $request_number)->value('id');
        $request_number = $request_number;
        $request->merge([
            'request_number' => $request_number, // Ganti 'request_number' dengan nilai variabel buatan Anda
            'id_purchase_requisitions' => $id,
        ]);

        if ($request->has('save_detail')) {
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
            return Redirect::to('/detail-pr-other/' . $request_number)->with('pesan', 'Data berhasil disimpan.');
            // return Redirect::to('/detail-pr/'.$request_number);
        } elseif ($request->has('hapus_detail')) {
            $validatedData = $request->input('hapus_detail');

            // dd($id);
            // die;
            PurchaseRequisitionsDetail::destroy($validatedData);
            return Redirect::to('/detail-pr-other/' . $request_number)->with('pesan', 'Data berhasil dihapus.');

            // return "Tombol Save detail diklik.";
        }
    }
    public function detail_pr_other($request_number)
    {
        // dd($request_number);
        // die;
        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $rawMaterials = DB::table('master_raw_materials')
            ->select('description', 'id')
            ->get();
        $ta = DB::table('master_tool_auxiliaries')
            ->select('description', 'id')
            ->get();
        $fg = DB::table('master_product_fgs')
            ->select('description', 'id')
            ->get();
        $wip = DB::table('master_wips')
            ->select('description', 'id')
            ->get();
        $units = DB::table('master_units')
            ->select('unit_code', 'id')
            ->get();

        $other = DB::table('master_tool_auxiliaries')
            ->select('description', 'id')
            ->where('type', 'Other') // Ganti 'column_name' dengan nama kolom dan 'value' dengan nilai yang ingin dicari
            ->get();


        $findtype = DB::table('purchase_requisitions as a')
            ->select('a.type')
            ->where('a.request_number', $request_number)
            ->first();

        $dt_detailSmt = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'd.nm_requester')
            ->where('a.request_number', $request_number)
            ->get();

        $data_detail_ta = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'd.nm_requester')
            ->where('a.request_number', $request_number)
            ->get();

        $data_detail_fg = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'd.nm_requester')
            ->where('a.request_number', $request_number)
            ->get();

        $data_detail_wip = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'd.nm_requester')
            ->where('a.request_number', $request_number)
            ->get();

        $data_detail_other = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'd.nm_requester')
            ->where('a.request_number', $request_number)
            ->where('b.type', 'Other') // Kondisi where berdasarkan 'type' dari 'master_tool_auxiliaries'
            ->get();

        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'Add Purchase Order RM';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        return view('purchase.detail_pr_other', compact(
            'datas',
            'supplier',
            'rawMaterials',
            'units',
            'dt_detailSmt',
            'request_number',
            'data_detail_ta',
            'data_detail_fg',
            'data_detail_wip',
            'findtype',
            'other',
            'data_detail_other'
        ));
    }
    public function hapus_po(Request $request, $id)
    {
        // dd('test');
        // die;
        $referenceNumber = DB::table('purchase_orders')
            ->where('id', $id)
            ->value('reference_number');

        $validatedData = DB::update("UPDATE `purchase_requisitions` SET `status` = 'Posted' WHERE `id` = '$referenceNumber'");

        PurchaseOrders::destroy($id);

        PurchaseOrderDetails::where('id_purchase_orders', $id)->delete();


        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'Hapus Purchase Order';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

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
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'Hapus Purchase Order Detail';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        if ($id) {
            //redirect dengan pesan sukses
            return Redirect::to('/detail-po/' . $reference_number . '/' . $idx)->with('pesan', 'Data berhasil dihapus.');
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
    public function get_edit_po_smt($id)
    {
        $data['find'] = PurchaseOrders::find($id);
        $data['finddetail'] = PurchaseOrderDetailsSMT::find($id);

        $typeProduk = PurchaseOrderDetailsSMT::select('type_product')
            ->where('id', $id)
            ->first();

        if ($typeProduk->type_product == 'RM') {
            $data['produk'] = DB::select("SELECT master_raw_materials.description, master_raw_materials.id FROM master_raw_materials");
        } elseif ($typeProduk->type_product == 'TA') {
            $data['produk'] = DB::select("SELECT master_tool_auxiliaries.description, master_tool_auxiliaries.id FROM master_tool_auxiliaries");
        } elseif ($typeProduk->type_product == 'WIP') {
            $data['produk'] = DB::select("SELECT master_wips.description, master_wips.id FROM master_wips");
        } elseif ($typeProduk->type_product == 'FG') {
            $data['produk'] = DB::select("SELECT master_product_fgs.description, master_product_fgs.id FROM master_product_fgs");
        } elseif ($typeProduk->type_product == 'Other') {
            $data['produk'] = DB::select("SELECT master_tool_auxiliaries.description, master_tool_auxiliaries.id FROM master_tool_auxiliaries where type='Other'");
        }
        $data['unit'] = DB::select("SELECT master_units.unit_code, master_units.id,master_units.unit FROM master_units");
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
        $typeProduk = PurchaseRequisitionsDetail::select('type_product')
            ->where('id', $id)
            ->first();

        $data['find'] = PurchaseRequisitionsDetail::find($id);
        if ($typeProduk->type_product == 'RM') {
            $data['produk'] = DB::select("SELECT master_raw_materials.description, master_raw_materials.id FROM master_raw_materials");
        } elseif ($typeProduk->type_product == 'TA') {
            $data['produk'] = DB::select("SELECT master_tool_auxiliaries.description, master_tool_auxiliaries.id FROM master_tool_auxiliaries");
        } elseif ($typeProduk->type_product == 'WIP') {
            $data['produk'] = DB::select("SELECT master_wips.description, master_wips.id FROM master_wips");
        } elseif ($typeProduk->type_product == 'FG') {
            $data['produk'] = DB::select("SELECT master_product_fgs.description, master_product_fgs.id FROM master_product_fgs");
        } elseif ($typeProduk->type_product == 'Other') {
            $data['produk'] = DB::select("SELECT master_tool_auxiliaries.description, master_tool_auxiliaries.id FROM master_tool_auxiliaries where type='Other'");
        }
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
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'Hapus Purchase Requisitions';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

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
            ->select('unit_code', 'id')
            ->get();
        $rawMaterials = DB::table('master_raw_materials')
            ->select('description', 'id')
            ->get();
        $ta = DB::table('master_tool_auxiliaries')
            ->select('description', 'id')
            ->get();
        $fg = DB::table('master_product_fgs')
            ->select('description', 'id', 'perforasi', 'group_sub_code')
            ->get();
        $wip = DB::table('master_wips')
            ->select('description', 'id')
            ->get();

        $other = DB::table('master_tool_auxiliaries')
            ->select('description', 'id')
            ->where('type', 'Other') // Ganti 'column_name' dengan nama kolom dan 'value' dengan nilai yang ingin dicari
            ->get();

        $data_detail_ta = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit', 'd.nm_requester')
            ->where('a.id_purchase_requisitions', $request_number)
            ->get();

        $data_detail_rm = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit', 'd.nm_requester')
            ->where('a.id_purchase_requisitions', $request_number)
            ->get();

        $data_detail_fg = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit', 'd.nm_requester', 'b.perforasi')
            ->where('a.id_purchase_requisitions', $request_number)
            ->get();

        $data_detail_wip = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit', 'd.nm_requester')
            ->where('a.id_purchase_requisitions', $request_number)
            ->get();

        $data_detail_other = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit', 'd.nm_requester')
            ->where('a.id_purchase_requisitions', $request_number)
            ->where('b.type', 'Other') // Kondisi where berdasarkan 'type' dari 'master_tool_auxiliaries'
            ->get();



        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'View List Purchase';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        return view('purchase.edit_pr', compact(
            'datas',
            'data_requester',
            'supplier',
            'units',
            'rawMaterials',
            'selectedId',
            'selectedIdreques',
            'radioselectted',
            'data_detail_ta',
            'ta',
            'fg',
            'wip',
            'data_detail_rm',
            'data_detail_fg',
            'data_detail_wip',
            'other',
            'data_detail_other'
        ));
    }
    public function update_detail_rm(Request $request, $request_number, $id)
    {
        //    dd($id);
        //     die;
        $request_number = $request_number;
        $request->merge([
            'request_number' => $request_number,
            'id_purchase_requisitions' => $id // Ganti 'request_number' dengan nilai variabel buatan Anda
        ]);
        if ($request->has('save_detail')) {
            $pesan = [
                'id_purchase_requisitions.required' => 'type masih kosong',
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
                'cc_co' => 'nullable',
                'remarks' => 'nullable',
                'request_number' => 'required',

            ], $pesan);

            // dd($validatedData);
            // die;
            $request_number = $request_number;
            PurchaseRequisitionsDetail::create($validatedData);

            // return "Tombol Save detail diklik.";
            return Redirect::to('/edit-pr/' . $id)->with('pesan', 'Data berhasil disimpan.');
            // return Redirect::to('/detail-pr/'.$request_number);
        } elseif ($request->has('hapus_detail')) {
            $validatedData = $request->input('hapus_detail');

            // dd($id);
            // die;
            $request_number = $request->input('request_number');
            PurchaseRequisitionsDetail::destroy($validatedData);
            return Redirect::to('/edit-pr/' . $id)->with('pesan', 'Data berhasil dihapus.');

            // return "Tombol Save detail diklik.";
        }
    }
    public function update_pr(Request $request, $request_number)
    {
        $request_number = $request_number;
        // dd($request->id);
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
            'id_master_suppliers' => 'nullable',
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
        return Redirect::to('/edit-pr/' . $request->id)->with('pesan', 'Data berhasil diupdate.');
    }
    public function posted_pr($request_number)
    {
        $request_numberx = $request_number;
        $validatedData = DB::update("UPDATE `purchase_requisitions` SET `status` = 'Posted' WHERE `request_number` = '$request_numberx';");

        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'Posted Purchase Requisitions';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        if ($validatedData) {
            //redirect dengan pesan sukses
            return Redirect::to('/purchase')->with('pesan', 'Data berhasil diposted.');
        } else {
            //redirect dengan pesan error
            return Redirect::to('/purchase')->with('pesan', 'Data gagal diposted.');
        }
    }
    public function unposted_pr($request_number)
    {
        $request_numberx = $request_number;
        $validatedData = DB::update("UPDATE `purchase_requisitions` SET `status` = 'Un Posted' WHERE `request_number` = '$request_numberx';");

        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'Posted Purchase Requisitions';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        if ($validatedData) {
            //redirect dengan pesan sukses
            return Redirect::to('/purchase')->with('pesan', 'Data berhasil di Un Posted.');
        } else {
            //redirect dengan pesan error
            return Redirect::to('/purchase')->with('pesan', 'Data gagal di Un Posted.');
        }
    }
    public function detail_po($reference_number, $id)
    {
        // dd($id);
        // die;
        $findtype = DB::table('purchase_orders')
            ->select('type as type_product')
            ->where('id', $id)
            ->first();

        $request_number = DB::table('purchase_requisitions')
            ->select('request_number')
            ->where('id', $reference_number)
            ->first();

        $datas = MstRequester::get();
        $supplier = MstSupplier::get();
        $rawMaterials = DB::table('master_raw_materials')
            ->select('description', 'id')
            ->get();

        $ta = DB::table('master_tool_auxiliaries')
            ->select('description', 'id')
            ->get();
        $fg = DB::table('master_product_fgs')
            ->select('description', 'id', 'perforasi')
            ->get();
        $wip = DB::table('master_wips')
            ->select('description', 'id')
            ->get();

        $other = DB::table('master_tool_auxiliaries')
            ->select('description', 'id')
            ->where('type', 'Other') // Ganti 'column_name' dengan nama kolom dan 'value' dengan nilai yang ingin dicari
            ->get();


        $units = DB::table('master_units')
            ->select('unit_code', 'id', 'unit')
            ->get();

        $currency = DB::table('master_currencies')
            ->select('currency_code', 'id', 'currency')
            ->get();

        // $POSmt = PurchaseOrderDetailsSMT::where('id_pr', $reference_number)->get();

        $POSmt = PurchaseOrderDetailsSMT::select('purchase_order_details_smt.*', 'master_raw_materials.description as raw_material_description')
            ->leftJoin('master_raw_materials', 'purchase_order_details_smt.description', '=', 'master_raw_materials.id')
            ->where('id_pr', $reference_number)
            ->get();

        $POSmtTA = PurchaseOrderDetailsSMT::select('purchase_order_details_smt.*', 'master_tool_auxiliaries.description as raw_material_description')
            ->leftJoin('master_tool_auxiliaries', 'purchase_order_details_smt.description', '=', 'master_tool_auxiliaries.id')
            ->where('id_pr', $reference_number)
            ->get();

        $POSmtfg = PurchaseOrderDetailsSMT::select('purchase_order_details_smt.*', 'master_product_fgs.description as raw_material_description')
            ->leftJoin('master_product_fgs', 'purchase_order_details_smt.description', '=', 'master_product_fgs.id')
            ->where('id_pr', $reference_number)
            ->get();

        $POSmtwip = PurchaseOrderDetailsSMT::select('purchase_order_details_smt.*', 'master_wips.description as raw_material_description')
            ->leftJoin('master_wips', 'purchase_order_details_smt.description', '=', 'master_wips.id')
            ->where('id_pr', $reference_number)
            ->get();

        $POSmtother = PurchaseOrderDetailsSMT::select('purchase_order_details_smt.*', 'master_tool_auxiliaries.description as raw_material_description')
            ->leftJoin('master_tool_auxiliaries', 'purchase_order_details_smt.description', '=', 'master_tool_auxiliaries.id')
            ->where('id_pr', $reference_number)
            ->where('master_tool_auxiliaries.type', 'Other')
            ->get();


        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'Add Purchase Order RM';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        return view('purchase.detail_po', compact(
            'datas',
            'supplier',
            'rawMaterials',
            'units',
            'reference_number',
            'POSmt',
            'id',
            'ta',
            'fg',
            'wip',
            'findtype',
            'POSmtTA',
            'POSmtwip',
            'POSmtfg',
            'POSmtother',
            'other',
            'request_number',
            'currency'
        ));
    }
    public function tambah_detail_po($reference_number, $id)
    {

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
        } elseif ($findtype->type == 'FG') {
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
        } elseif ($findtype->type == 'WIP') {
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
        } elseif ($findtype->type == 'TA') {
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
        } elseif ($findtype->type == 'Other') {
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
                ->where('master_tool_auxiliaries.type', '=', 'Other')
                ->get();
        }

        // dd($results);
        // die;

        // Simpan hasil query ke dalam tabel purchase_order_details_smt
        foreach ($results as $result) {
            // Pengecekan data yang tidak boleh kosong
            if (!empty($result->type_product) && !empty($result->id_produk) && !empty($result->qty)) {
                DB::table('purchase_order_details_smt')->insert([
                    'id_pr' => $result->id,
                    'type_product' => $result->type_product,
                    'description' => $result->id_produk,
                    'qty' => $result->qty,
                    'request_number' => $result->request_number,
                    'unit' => $result->unit,
                ]);
            }
        }

        return Redirect::to('/detail-po/' . $reference_number . '/' . $id);
    }
    public function simpan_detail_po(Request $request, $reference_number, $id)
    {
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
        $request->merge([
            'amount' => $request->total_amount,
        ]);

        $pesan = [
            'id_pr.required' => 'id_pr masih kosong',
            'id_po.required' => 'id_po masih kosong',
            'type_product.required' => 'type_product masih kosong',
            'description.required' => 'description number masih kosong',
            'qty.required' => 'qty masih kosong',
            'unit.required' => 'unit masih kosong',
            'price.required' => 'price masih kosong',
            'discount.required' => 'discount masih kosong',
            'tax.required' => 'tax masih kosong',
            'note.required' => 'note masih kosong',
            'currency.required' => 'currency masih kosong',

        ];

        $validatedData = $request->validate([
            'id_pr' => 'required',
            'id_po' => 'required',
            'type_product' => 'required',
            'description' => 'required',
            'qty' => 'required',
            'unit' => 'required',
            'price' => 'required',
            'discount' => 'required',
            'tax' => 'required',
            'amount' => 'required',
            'note' => 'nullable',
            'currency' => 'required',
        ]);

        // Set nilai 'request_number' dengan hasil kueri database
        $validatedData['request_number'] = $requestNumberValue;

        PurchaseOrderDetailsSMT::create($validatedData);

        return Redirect::to('/detail-po/' . $reference_number . '/' . $id)->with('pesan', 'Purchase Requisition Detail berhasil ditambahkan.');
    }
    public function simpan_detail_po_fix(Request $request, $id, $reference_number)
    {

        // dd($reference_number);
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
                    DB::raw($id . ' as id_purchase_order'),
                    'a.type_product',
                    'b.id as master_products_id',
                    'a.note',
                    'a.qty',
                    'c.id as master_units_id',
                    'a.price',
                    'a.discount',
                    'a.tax',
                    'a.amount',
                    'a.currency'
                )
                ->leftJoin('master_raw_materials as b', 'a.description', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.unit', '=', 'c.unit')
                ->where('a.id_pr', '=', $reference_number)
                ->get();
        } elseif ($findtype->type == 'FG') {
            $results = DB::table('purchase_order_details_smt as a')
                ->select(
                    DB::raw($id . ' as id_purchase_order'),
                    'a.type_product',
                    'b.id as master_products_id',
                    'a.note',
                    'a.qty',
                    'c.id as master_units_id',
                    'a.price',
                    'a.discount',
                    'a.tax',
                    'a.amount',
                    'a.currency'
                )
                ->leftJoin('master_product_fgs as b', 'a.description', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.unit', '=', 'c.unit')
                ->where('a.id_pr', '=', $reference_number)
                ->get();
        } elseif ($findtype->type == 'WIP') {
            $results = DB::table('purchase_order_details_smt as a')
                ->select(
                    DB::raw($id . ' as id_purchase_order'),
                    'a.type_product',
                    'b.id as master_products_id',
                    'a.note',
                    'a.qty',
                    'c.id as master_units_id',
                    'a.price',
                    'a.discount',
                    'a.tax',
                    'a.amount',
                    'a.currency'
                )
                ->leftJoin('master_wips as b', 'a.description', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.unit', '=', 'c.unit')
                ->where('a.id_pr', '=', $reference_number)
                ->get();
        } elseif ($findtype->type == 'TA') {
            $results = DB::table('purchase_order_details_smt as a')
                ->select(
                    DB::raw($id . ' as id_purchase_order'),
                    'a.type_product',
                    'b.id as master_products_id',
                    'a.note',
                    'a.qty',
                    'c.id as master_units_id',
                    'a.price',
                    'a.discount',
                    'a.tax',
                    'a.amount',
                    'a.currency'
                )
                ->leftJoin('master_tool_auxiliaries as b', 'a.description', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.unit', '=', 'c.unit')
                ->where('a.id_pr', '=', $reference_number)
                ->get();
        } elseif ($findtype->type == 'Other') {
            $results = DB::table('purchase_order_details_smt as a')
                ->select(
                    DB::raw($id . ' as id_purchase_order'),
                    'a.type_product',
                    'b.id as master_products_id',
                    'a.note',
                    'a.qty',
                    'c.id as master_units_id',
                    'a.price',
                    'a.discount',
                    'a.tax',
                    'a.amount',
                    'a.currency'

                )
                ->leftJoin('master_tool_auxiliaries as b', 'a.description', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.unit', '=', 'c.unit')
                ->where('a.id_pr', '=', $reference_number)
                ->where('b.type', '=', 'Other')
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
                'currency' => $result->currency
            ]);
        }

        $total_discount = PurchaseOrderDetails::where('id_purchase_orders', $id)->sum('discount');
        $sub_total = PurchaseOrderDetails::where('id_purchase_orders', $id)->sum('amount');

        $total_amount = $sub_total - $total_discount;
        // dd($total_discount);
        // die;



        $total_ppn = DB::table('purchase_order_details_smt')
            ->where('id_pr', $reference_number)
            ->sum(DB::raw("CASE WHEN tax = 'Y' THEN ((qty * price) - discount) * 0.11 ELSE 0 END"));

        $validatedData = DB::update("UPDATE `purchase_orders` SET `total_discount` = '$total_discount', 
        `sub_total` = '$sub_total', `total_amount` = '$total_amount', total_ppn='$total_ppn' WHERE `id` = '$id';");


        return Redirect::to('/purchase-order')->with('pesan', 'Purchase Order berhasil ditambahkan.');
    }
    public function posted_po($id)
    {
        $idx = $id;
        $validatedData = DB::update("UPDATE `purchase_orders` SET `status` = 'Posted' WHERE `id` = '$idx';");

        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'Posted Purchase Order';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        if ($validatedData) {
            //redirect dengan pesan sukses
            return Redirect::to('/purchase-order')->with('pesan', 'Data berhasil diposted.');
        } else {
            //redirect dengan pesan error
            return Redirect::to('/purchase-order')->with('pesan', 'Data gagal diposted.');
        }
    }
    public function unposted_po($id)
    {
        $idx = $id;
        $validatedData = DB::update("UPDATE `purchase_orders` SET `status` = 'Un Posted' WHERE `id` = '$idx';");

        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'Un Posted Purchase Order';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        if ($validatedData) {
            //redirect dengan pesan sukses
            return Redirect::to('/purchase-order')->with('pesan', 'Data berhasil di Un Posted.');
        } else {
            //redirect dengan pesan error
            return Redirect::to('/purchase-order')->with('pesan', 'Data gagal di Un Posted.');
        }
    }
    public function edit_po_item($id)
    {
        $id = decrypt($id);
        // Dropdown
        $currency = MstCurrencies::get();
        $units = MstUnits::get();
        $rawMaterials = MstRawMaterial::select('id', 'description')->get();
        $ta = MstToolAux::select('id', 'description')->where('type', '!=', 'Other')->get();
        $fg = MstProductFG::select('id', 'description', 'perforasi')->get();
        $wip = MstWip::select('id', 'description')->get();
        $other = MstToolAux::select('id', 'description')->where('type', 'Other')->get();

        $data = PurchaseOrderDetails::where('id', $id)->first();

        return view('purchase.edit_po_item', compact('id', 'currency', 'units', 'rawMaterials', 'ta', 'fg', 'wip', 'other', 'data'));
    }
    public function edit_po_item_smt($id)
    {
        // dd($id);
        // die;
        $currency = DB::table('master_currencies')
            ->select('currency_code', 'id', 'currency')
            ->get();

        $units = DB::table('master_units')
            ->select('unit_code', 'id', 'unit')
            ->get();
        $rawMaterials = DB::table('master_raw_materials')
            ->select('description', 'id')
            ->get();
        $ta = DB::table('master_tool_auxiliaries')
            ->select('description', 'id')
            ->get();
        $fg = DB::table('master_product_fgs')
            ->select('description', 'id', 'perforasi')
            ->get();
        $wip = DB::table('master_wips')
            ->select('description', 'id')
            ->get();

        $other = DB::table('master_tool_auxiliaries')
            ->select('description', 'id')
            ->where('type', 'Other') // Ganti 'column_name' dengan nama kolom dan 'value' dengan nilai yang ingin dicari
            ->get();

        $results = DB::table('purchase_order_details_smt as a')
            ->select(
                'a.*'
            )
            ->where('a.id', '=', $id)
            ->get();


        return view('purchase.edit_po_item_smt', compact(
            'id',
            'results',
            'other',
            'wip',
            'fg',
            'ta',
            'rawMaterials',
            'units',
            'currency'
        ));
    }
    public function update_po(Request $request, $id)
    {
        $id = $id;
        // dd($request);
        // die;
        $pesan = [
            'po_number.required' => 'po number masih kosong',
            'date.required' => 'date masih kosong',
            'delivery_date.required' => 'date masih kosong',
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
            'delivery_date' => 'nullable',
            'reference_number' => 'required',
            'id_master_suppliers' => 'nullable',
            'qc_check' => 'required',
            'down_payment' => 'required',
            'own_remarks' => 'nullable',
            'supplier_remarks' => 'nullable',
            'status' => 'required',
            'type' => 'required',

        ], $pesan);

        // dd($validatedData);
        // die;

        PurchaseOrders::where('id', $id)
            ->update($validatedData);

        return Redirect::to('/purchase-order')->with('pesan', 'Data berhasil diupdate.');
    }

    public function indexPO(Request $request)
    {
        
    }
    public function edit_po($id)
    {
        // Dropdown
        $currency = MstCurrencies::get();
        $units = MstUnits::get();
        $supplier = MstSupplier::get();
        $data_requester = MstRequester::get();
        $reference_number = PurchaseRequisitions::get();
        $rawMaterials = MstRawMaterial::select('id', 'description')->get();
        $ta = MstToolAux::select('id', 'description')->where('type', '!=', 'Other')->get();
        $fg = MstProductFG::select('id', 'description', 'perforasi')->get();
        $wip = MstWip::select('id', 'description')->get();
        $other = MstToolAux::select('id', 'description')->where('type', 'Other')->get();

        $data = PurchaseOrders::select('purchase_orders.*', 'purchase_requisitions.request_number', 'master_suppliers.name')
            ->leftJoin('purchase_requisitions', 'purchase_orders.reference_number', 'purchase_requisitions.id')
            ->leftJoin('master_suppliers', 'purchase_orders.id_master_suppliers', 'master_suppliers.id')
            ->where('purchase_orders.id', $id)
            ->first();

        $itemDatas = PurchaseOrderDetails::select(
            'purchase_order_details.*',
            'master_units.unit',
            DB::raw('
                CASE 
                    WHEN purchase_order_details.type_product = "RM" THEN master_raw_materials.description 
                    WHEN purchase_order_details.type_product = "WIP" THEN master_wips.description 
                    WHEN purchase_order_details.type_product = "FG" THEN master_product_fgs.description 
                    WHEN purchase_order_details.type_product IN ("TA", "Other") THEN master_tool_auxiliaries.description 
                END as product_desc')
        )
            ->leftJoin('master_raw_materials', function ($join) {
                $join->on('purchase_order_details.master_products_id', '=', 'master_raw_materials.id')
                    ->on('purchase_order_details.type_product', '=', DB::raw('"RM"'));
            })
            ->leftJoin('master_wips', function ($join) {
                $join->on('purchase_order_details.master_products_id', '=', 'master_wips.id')
                    ->on('purchase_order_details.type_product', '=', DB::raw('"WIP"'));
            })
            ->leftJoin('master_product_fgs', function ($join) {
                $join->on('purchase_order_details.master_products_id', '=', 'master_product_fgs.id')
                    ->on('purchase_order_details.type_product', '=', DB::raw('"FG"'));
            })
            ->leftJoin('master_tool_auxiliaries', function ($join) {
                $join->on('purchase_order_details.master_products_id', '=', 'master_tool_auxiliaries.id')
                    ->on('purchase_order_details.type_product', '=', DB::raw('"TA"'))
                    ->orOn('purchase_order_details.type_product', '=', DB::raw('"Other"'));
            })
            ->leftJoin('master_units', 'purchase_order_details.master_units_id', '=', 'master_units.id')
            ->where('purchase_order_details.id_purchase_orders', $id)
            ->orderBy('purchase_order_details.created_at')
            ->get();

        //Audit Log
        $this->auditLogsShort('Edit Purchase Order ID : (' . $id . ')');

        return view('purchase.edit_po', compact(
            'currency',
            'units',
            'supplier',
            'data_requester',
            'reference_number',
            'rawMaterials',
            'ta',
            'fg',
            'wip',
            'other',
            'data',
            'itemDatas'
        ));
    }
    public function updatePO(Request $request, $id)
    {
        $id = decrypt($id);
        $request->validate([
            'po_number' => 'required',
            'date' => 'required',
            'reference_number' => 'required',
            'id_master_suppliers' => 'required',
            'qc_check' => 'required',
            'down_payment' => 'required',
            'status' => 'required',
            'type' => 'required',
        ], [
            'po_number.required' => 'PO Number masih kosong.',
            'date.required' => 'Date masih kosong.',
            'reference_number.required' => 'Reference Number harus diisi.',
            'id_master_suppliers.required' => 'Suppliers harus diisi.',
            'qc_check.required' => 'QC Check harus diisi.',
            'down_payment.required' => 'Down Payment harus diisi.',
            'status.required' => 'Status masih kosong.',
            'type.required' => 'Type masih kosong.',
        ]);
        // Compare With Data Before
        $dataBefore = PurchaseOrders::where('id', $id)->first();
        $dataBefore->date = $request->date;
        $dataBefore->delivery_date = $request->delivery_date;
        $dataBefore->reference_number = $request->reference_number;
        $dataBefore->id_master_suppliers = $request->id_master_suppliers;
        $dataBefore->qc_check = $request->qc_check;
        $dataBefore->down_payment = $request->down_payment;
        $dataBefore->own_remarks = $request->own_remarks;
        $dataBefore->supplier_remarks = $request->supplier_remarks;
        $dataBefore->status = $request->status;
        $dataBefore->type = $request->type;

        if($dataBefore->isDirty()){
            DB::beginTransaction();
            try{
                // Update ITEM
                PurchaseOrders::where('id', $id)->update([
                    'date' => $request->date,
                    'delivery_date' => $request->delivery_date,
                    'reference_number' => $request->reference_number,
                    'id_master_suppliers' => $request->id_master_suppliers,
                    'qc_check' => $request->qc_check,
                    'down_payment' => $request->down_payment,
                    'own_remarks' => $request->own_remarks,
                    'supplier_remarks' => $request->supplier_remarks,
                    'status' => $request->status,
                    'type' => $request->type,
                ]);
    
                // Audit Log
                $this->auditLogsShort('Update Data PO ID ('. $id . ')');
    
                DB::commit();
                return redirect()->back()->with(['success' => 'Berhasil Perbaharui Data PO']);
            } catch (Exception $e) {
                DB::rollback();
                return redirect()->back()->with(['fail' => 'Gagal Perbaharui Data PO!']);
            }
        } else {
            return redirect()->back()->with(['info' => 'Tidak Ada Yang Dirubah, Data Sama Dengan Sebelumnya']);
        }
    }
    public function addItemPO(Request $request, $id)
    {
        $id = decrypt($id);
        $request->validate([
            'id_purchase_orders' => 'required',
            'type_product' => 'required',
            'master_products_id' => 'required',
            'qty' => 'required',
            'master_units_id' => 'required',
            'currency' => 'required',
            'price' => 'required',
            'subTotal' => 'required',
            'discount' => 'required',
            'amount' => 'required',
            'tax' => 'required',
        ], [
            'id_purchase_orders.required' => 'ID PO masih kosong.',
            'type_product.required' => 'Tipe produk masih kosong.',
            'master_products_id.required' => 'ID produk harus diisi.',
            'qty.required' => 'Kuantitas harus diisi.',
            'master_units_id.required' => 'ID unit harus diisi.',
            'currency.required' => 'Currency harus diisi.',
            'price.required' => 'Price harus diisi.',
            'subTotal.required' => 'Subtotal masih kosong.',
            'discount.required' => 'Diskon masih kosong.',
            'amount.required' => 'Jumlah harus diisi.',
            'tax.required' => 'Pajak harus diisi.',
        ]);
        
        DB::beginTransaction();
        try{
            // Add ITEM
            PurchaseOrderDetails::create([
                'id_purchase_orders' => $request->id_purchase_orders,
                'type_product' => $request->type_product,
                'master_products_id' => $request->master_products_id,
                'qty' => $request->qty,
                'master_units_id' => $request->master_units_id,
                'currency' => $request->currency,
                'price' => str_replace(['.', ','], ['', '.'], $request->price),
                'sub_total' => str_replace(['.', ','], ['', '.'], $request->subTotal),
                'discount' => str_replace(['.', ','], ['', '.'], $request->discount),
                'amount' => str_replace(['.', ','], ['', '.'], $request->amount),
                'tax' => $request->tax,
                'tax_rate' => $request->tax_rate,
                'tax_value' => str_replace(['.', ','], ['', '.'], $request->tax_value),
                'total_amount' => str_replace(['.', ','], ['', '.'], $request->total_amount),
                'note' => $request->note,
                'status' => 'Open',
            ]);
            $totals = PurchaseOrderDetails::where('id_purchase_orders', $id)
                ->selectRaw('SUM(sub_total) as total_sub_total, SUM(discount) as total_discount, SUM(amount) as total_sub_amount,
                    SUM(tax_value) as total_ppn, SUM(total_amount) as total_amount')
                ->first();

            // Update PO Data
            PurchaseOrders::where('id', $id)->update([
                'sub_total' => $totals->total_sub_total,
                'total_discount' => $totals->total_discount,
                'total_sub_amount' => $totals->total_sub_amount,
                'total_ppn' => $totals->total_ppn,
                'total_amount' => $totals->total_amount,
            ]);

            // Audit Log
            $this->auditLogsShort('Add New Item in PO ID ('. $id . ')');

            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Tambah Item Produk Baru Ke Tabel']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Tambah Item Produk Baru!']);
        }
    }
    public function updateItemPO(Request $request, $id)
    {
        $id = decrypt($id);
        $request->validate([
            'id_purchase_orders' => 'required',
            'type_product' => 'required',
            'master_products_id' => 'required',
            'qty' => 'required',
            'master_units_id' => 'required',
            'currency' => 'required',
            'price' => 'required',
            'subTotal' => 'required',
            'discount' => 'required',
            'amount' => 'required',
            'tax' => 'required',
        ], [
            'id_purchase_orders.required' => 'ID PO masih kosong.',
            'type_product.required' => 'Tipe produk masih kosong.',
            'master_products_id.required' => 'ID produk harus diisi.',
            'qty.required' => 'Kuantitas harus diisi.',
            'master_units_id.required' => 'ID unit harus diisi.',
            'currency.required' => 'Currency harus diisi.',
            'price.required' => 'Price harus diisi.',
            'subTotal.required' => 'Subtotal masih kosong.',
            'discount.required' => 'Diskon masih kosong.',
            'amount.required' => 'Jumlah harus diisi.',
            'tax.required' => 'Pajak harus diisi.',
        ]);
        // Compare With Data Before
        $dataBefore = PurchaseOrderDetails::where('id', $id)->first();
        $dataBefore->master_products_id = $request->master_products_id;
        $dataBefore->qty = $request->qty;
        $dataBefore->master_units_id = $request->master_units_id;
        $dataBefore->currency = $request->currency;
        $dataBefore->price = str_replace(['.', ','], ['', '.'], $request->price);
        $dataBefore->sub_total = str_replace(['.', ','], ['', '.'], $request->subTotal);
        $dataBefore->discount = str_replace(['.', ','], ['', '.'], $request->discount);
        $dataBefore->amount = str_replace(['.', ','], ['', '.'], $request->amount);
        $dataBefore->tax = $request->tax;
        $dataBefore->tax_rate = $request->tax_rate;
        $dataBefore->tax_value = str_replace(['.', ','], ['', '.'], $request->tax_value);
        $dataBefore->total_amount = str_replace(['.', ','], ['', '.'], $request->total_amount);
        $dataBefore->note = $request->note;

        if($dataBefore->isDirty()){
            DB::beginTransaction();
            try{
                // Update ITEM
                PurchaseOrderDetails::where('id', $id)->update([
                    'master_products_id' => $request->master_products_id,
                    'qty' => $request->qty,
                    'master_units_id' => $request->master_units_id,
                    'currency' => $request->currency,
                    'price' => str_replace(['.', ','], ['', '.'], $request->price),
                    'sub_total' => str_replace(['.', ','], ['', '.'], $request->subTotal),
                    'discount' => str_replace(['.', ','], ['', '.'], $request->discount),
                    'amount' => str_replace(['.', ','], ['', '.'], $request->amount),
                    'tax' => $request->tax,
                    'tax_rate' => $request->tax_rate,
                    'tax_value' => str_replace(['.', ','], ['', '.'], $request->tax_value),
                    'total_amount' => str_replace(['.', ','], ['', '.'], $request->total_amount),
                    'note' => $request->note,
                ]);
                $totals = PurchaseOrderDetails::where('id_purchase_orders', $request->id_purchase_orders)
                    ->selectRaw('SUM(sub_total) as total_sub_total, SUM(discount) as total_discount, SUM(amount) as total_sub_amount,
                        SUM(tax_value) as total_ppn, SUM(total_amount) as total_amount')
                    ->first();
                // Update PO Data
                PurchaseOrders::where('id', $request->id_purchase_orders)->update([
                    'sub_total' => $totals->total_sub_total,
                    'total_discount' => $totals->total_discount,
                    'total_sub_amount' => $totals->total_sub_amount,
                    'total_ppn' => $totals->total_ppn,
                    'total_amount' => $totals->total_amount,
                ]);
    
                // Audit Log
                $this->auditLogsShort('Update Item PO ID ('. $id . ')');
    
                DB::commit();
                return redirect()->route('edit_po', $request->id_purchase_orders)->with(['success' => 'Berhasil Perbaharui Item Produk']);
            } catch (Exception $e) {
                DB::rollback();
                return redirect()->back()->with(['fail' => 'Gagal Perbaharui Item Produk!']);
            }
        } else {
            return redirect()->back()->with(['info' => 'Tidak Ada Yang Dirubah, Data Sama Dengan Sebelumnya']);
        }
    }
    public function deleteItemPO(Request $request, $id)
    {
        $id = decrypt($id);
        $request->validate([
            'id_purchase_orders' => 'required',
        ], [
            'id_purchase_orders.required' => 'ID PO masih kosong.',
        ]);

        DB::beginTransaction();
        try{
            // Delete ITEM
            PurchaseOrderDetails::where('id', $id)->delete();
            $totals = PurchaseOrderDetails::where('id_purchase_orders', $request->id_purchase_orders)
                ->selectRaw('SUM(sub_total) as total_sub_total, SUM(discount) as total_discount, SUM(amount) as total_sub_amount,
                    SUM(tax_value) as total_ppn, SUM(total_amount) as total_amount')
                ->first();
            // Update PO Data
            PurchaseOrders::where('id', $request->id_purchase_orders)->update([
                'sub_total' => $totals->total_sub_total,
                'total_discount' => $totals->total_discount,
                'total_sub_amount' => $totals->total_sub_amount,
                'total_ppn' => $totals->total_ppn,
                'total_amount' => $totals->total_amount,
            ]);

            // Audit Log
            $this->auditLogsShort('Delete Item PO ID ('. $id . ')');

            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Hapus Item Produk']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Hapus Item Produk!']);
        }
    }


    public function update_detail_po(Request $request, $id)
    {
        //    dd($id);
        //    die;
        $request->merge([
            'id_purchase_orders' => $id, // Ganti 'request_number' dengan nilai variabel buatan Anda
            'amount' => $request->total_amount,
        ]);
        if ($request->has('save_detail')) {
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
                'currency.required' => 'currency masih kosong',

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
                'note' => 'nullable',
                'currency' => 'required'


            ], $pesan);


            $id = $id;
            PurchaseOrderDetails::create($validatedData);

            $total_discount = PurchaseOrderDetails::where('id_purchase_orders', $id)->sum('discount');
            $sub_total = PurchaseOrderDetails::where('id_purchase_orders', $id)->sum('amount');

            $total_amount = $sub_total - $total_discount;
            // dd($total_discount);
            // die;

            $total_ppn = DB::table('purchase_order_details')
                ->where('id_purchase_orders', $id)
                ->sum(DB::raw("CASE WHEN tax = 'Y' THEN ((qty * price) - discount) * 0.11 ELSE 0 END"));

            $validatedData = DB::update("UPDATE `purchase_orders` SET `total_discount` = '$total_discount', 
            `sub_total` = '$sub_total', `total_amount` = '$total_amount', total_ppn = '$total_ppn' WHERE `id` = '$id';");

            // return "Tombol Save detail diklik.";
            return Redirect::to('/edit-po/' . $id)->with('pesan', 'Data berhasil disimpan.');
            // return Redirect::to('/detail-pr/'.$request_number);
        } elseif ($request->has('hapus_detail')) {
            $validatedData = $request->input('hapus_detail');

            // dd($validatedData);
            // die;
            $id = $id;
            PurchaseOrderDetails::destroy($validatedData);

            $total_discount = PurchaseOrderDetails::where('id_purchase_orders', $id)->sum('discount');
            $sub_total = PurchaseOrderDetails::where('id_purchase_orders', $id)->sum('amount');

            $total_amount = $sub_total - $total_discount;
            // dd($total_discount);
            // die;

            $total_ppn = DB::table('purchase_order_details')
                ->where('id_purchase_orders', $id)
                ->sum(DB::raw("CASE WHEN tax = 'Y' THEN ((qty * price) - discount) * 0.11 ELSE 0 END"));

            $validatedData = DB::update("UPDATE `purchase_orders` SET `total_discount` = '$total_discount', 
            `sub_total` = '$sub_total', `total_amount` = '$total_amount', total_ppn = '$total_ppn' WHERE `id` = '$id'");

            return Redirect::to('/edit-po/' . $id)->with('pesan', 'Data berhasil dihapus.');

            // return "Tombol Save detail diklik.";
        }
    }
    public function update_detail_po_item(Request $request, $id)
    {
        // return "Tombol Save detail diklik.";
        return Redirect::to('/edit-po/' . $id)->with('pesan', 'Data berhasil diupdate.');
    }

    public function update_po_detail(Request $request, $id)
    {
        // dd($id);
        // die;
        $id_po = $request->input('id_purchase_orders');

        $id = $id;
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
            'note' => 'nullable',
            'currency' => 'required'
        ], $pesan);

        // dd($validatedData);
        // die;

        PurchaseOrderDetails::where('id', $id)
            ->update($validatedData);

        $total_discount = PurchaseOrderDetails::where('id_purchase_orders', $id_po)->sum('discount');
        $sub_total = PurchaseOrderDetails::where('id_purchase_orders', $id_po)->sum('amount');

        $total_amount = $sub_total - $total_discount;
        // dd($total_discount);
        // die;

        $total_ppn = DB::table('purchase_order_details')
            ->where('id_purchase_orders', $id_po)
            ->sum(DB::raw("CASE WHEN tax = 'Y' THEN ((qty * price) - discount) * 0.11 ELSE 0 END"));

        $validatedData = DB::update("UPDATE `purchase_orders` SET `total_discount` = '$total_discount', 
        `sub_total` = '$sub_total', `total_amount` = '$total_amount', total_ppn = '$total_ppn' WHERE `id` = '$id_po'");

        // dd($validatedData);
        // die;

        $id_purchase_orders = $request->input('id_purchase_orders');
        return Redirect::to('/edit-po/' . $id_purchase_orders)->with('pesan', 'Data berhasil diupdate.');
    }
    public function update_po_detail_smt(Request $request, $id)
    {
        // dd($request);
        // die;

        // $id = $id_pr;

        $id_purchase_orders = $request->input('id_pr');
        // dd($id_purchase_orders);
        // die;

        $id_po = PurchaseOrders::select('id')
            ->where('reference_number', $id_purchase_orders)
            ->first();

        // dd($id_po->id);
        // die;
        $request->merge([
            'id_po' =>  $id_po->id, // Ganti 'id_po' dengan nilai variabel buatan Anda
        ]);

        $pesan = [
            'id_po.required' => 'type masih kosong',
            'type_product.required' => 'type masih kosong',
            'description.required' => 'description masih kosong',
            'qty.required' => 'qty masih kosong',
            'unit.required' => 'unit masih kosong',
            'currency.required' => 'currency masih kosong',
            'price.required' => 'price masih kosong',
            'discount.required' => 'discount masih kosong',
            'tax.required' => 'tax masih kosong',
            'amount.required' => 'amount masih kosong',
            'note.nullable' => 'note masih kosong',

        ];

        $validatedData = $request->validate([
            'id_po' => 'nullable',
            'type_product' => 'required',
            'description' => 'required',
            'qty' => 'required',
            'unit' => 'required',
            'currency' => 'required',
            'price' => 'required',
            'discount' => 'required',
            'tax' => 'required',
            'amount' => 'required',
            'note' => 'nullable',
        ], $pesan);

        // dd($validatedData);
        // die;

        PurchaseOrderDetailsSMT::where('id', $id)
            ->update($validatedData);

        $id_purchase_orders = $request->input('id_pr');
        return Redirect::to('/detail-po/' . $id_purchase_orders . '/' . $id_po->id)->with('pesan', 'Data berhasil diupdate.');
    }
    public function update_pr_detailx(Request $request, $id)
    {
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
        $id_purchase_requisitions = $request->input('id_purchase_requisitions');
        return Redirect::to('/edit-pr/' . $id_purchase_requisitions)->with('pesan', 'Data berhasil diupdate.');
    }
    public function update_pr_detail_editx(Request $request, $id)
    {
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
        $id_purchase_requisitions = $request->input('id_purchase_requisitions');
        $type_product = $request->input('type_product');
        if ($type_product == 'RM') {
            return Redirect::to('/detail-pr/' . $request_number)->with('pesan', 'Data berhasil diupdate.');
        } elseif ($type_product == 'TA') {
            return Redirect::to('/detail-pr-sparepart/' . $request_number)->with('pesan', 'Data berhasil diupdate.');
        } elseif ($type_product == 'FG') {
            return Redirect::to('/detail-pr-fg/' . $request_number)->with('pesan', 'Data berhasil diupdate.');
        } elseif ($type_product == 'WIP') {
            return Redirect::to('/detail-pr-wip/' . $request_number)->with('pesan', 'Data berhasil diupdate.');
        } elseif ($type_product == 'Other') {
            return Redirect::to('/detail-pr-other/' . $request_number)->with('pesan', 'Data berhasil diupdate.');
        }
    }
    public function print_po($id)
    {
        // dd ($id);
        // die;
        $purchaseOrder = DB::table('purchase_orders as a')
            ->leftJoin('master_suppliers as b', 'a.id_master_suppliers', '=', 'b.id')
            ->leftJoin('master_term_payments as c', 'b.id_master_term_payments', '=', 'c.id')
            ->select('a.*', 'c.term_payment')
            ->where('a.id', $id)
            ->first();

        $data_detail_rm = DB::table('purchase_order_details as a')
            ->select(
                'a.master_products_id',
                DB::raw('MAX(a.type_product) as type_product'),
                DB::raw('MAX(b.description) as description'),
                DB::raw('MAX(a.qty) as qty'),
                DB::raw('MAX(c.unit) as unit'),
                DB::raw('MAX(a.price) as price'),
                DB::raw('MAX(a.discount) as discount'),
                DB::raw('MAX(a.tax) as tax'),
                DB::raw('MAX(a.amount) as amount'),
                DB::raw('MAX(a.note) as note'),
                DB::raw('MAX(a.id) as id'),
                DB::raw('MAX(a.currency) as currency'),
                DB::raw('MAX(f.remarks) as remarks')
            )
            ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('purchase_orders as d', 'a.id_purchase_orders', '=', 'd.id')
            ->leftJoin('purchase_requisitions as e', 'd.reference_number', '=', 'e.id')
            ->leftJoin('purchase_requisition_details as f', 'e.id', '=', 'f.id_purchase_requisitions')
            ->where('a.id_purchase_orders', '=', $id)
            ->groupBy('a.master_products_id')
            ->get();



        $data_detail_ta = DB::table('purchase_order_details as a')
            ->select(
                'a.type_product',
                'b.description',
                'a.qty',
                'c.unit',
                'a.price',
                'a.discount',
                'a.tax',
                'a.amount',
                'a.note',
                'a.id',
                'a.note',
                'a.currency',
                'f.remarks'
            )
            ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('purchase_orders as d', 'a.id_purchase_orders', '=', 'd.id')
            ->leftJoin('purchase_requisitions as e', 'd.reference_number', '=', 'e.id')
            ->leftJoin('purchase_requisition_details as f', 'e.id', '=', 'f.id_purchase_requisitions')
            ->where('a.id_purchase_orders', '=', $id)
            ->distinct()
            ->get();

        // $data_detail_wip = DB::table('purchase_order_details as a')
        //         ->select('a.type_product', 'b.description', 'a.qty', 'c.unit', 'a.price', 'a.discount', 'a.tax', 'a.amount', 'a.note','a.id','a.note','a.currency')
        //         ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
        //         ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
        //         ->where('a.id_purchase_orders', '=', $id)
        //         ->get();

        $data_detail_wip = DB::table('purchase_order_details as a')
            ->select(
                'a.type_product',
                'b.description',
                'a.qty',
                'c.unit',
                'a.price',
                'a.discount',
                'a.tax',
                'a.amount',
                'a.note',
                'a.id',
                'a.currency',
                'f.remarks'
            )
            ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('purchase_orders as d', 'a.id_purchase_orders', '=', 'd.id')
            ->leftJoin('purchase_requisitions as e', 'd.reference_number', '=', 'e.id')
            ->leftJoin('purchase_requisition_details as f', 'e.id', '=', 'f.id_purchase_requisitions')
            ->where('a.id_purchase_orders', '=', $id)  // pastikan $id_purchase_orders sesuai dengan variabel PHP
            ->distinct()
            ->get();


        $data_detail_fg = DB::table('purchase_order_details as a')
            ->select(
                'a.type_product',
                'b.description',
                'b.perforasi',
                'a.qty',
                'c.unit',
                'a.price',
                'a.discount',
                'a.tax',
                'a.amount',
                'a.note',
                'a.id',
                'a.note',
                'a.currency',
                'f.remarks'
            )
            ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('purchase_orders as d', 'a.id_purchase_orders', '=', 'd.id')
            ->leftJoin('purchase_requisitions as e', 'd.reference_number', '=', 'e.id')
            ->leftJoin('purchase_requisition_details as f', 'e.id', '=', 'f.id_purchase_requisitions')
            ->where('a.id_purchase_orders', '=', $id)
            ->distinct()
            ->get();

        $data_detail_other = DB::table('purchase_order_details as a')
            ->select(
                'a.type_product',
                'b.description',
                'a.qty',
                'c.unit',
                'a.price',
                'a.discount',
                'a.tax',
                'a.amount',
                'a.note',
                'a.id',
                'a.note',
                'a.currency',
                'f.remarks'
            )
            ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('purchase_orders as d', 'a.id_purchase_orders', '=', 'd.id')
            ->leftJoin('purchase_requisitions as e', 'd.reference_number', '=', 'e.id')
            ->leftJoin('purchase_requisition_details as f', 'e.id', '=', 'f.id_purchase_requisitions')
            ->where('a.id_purchase_orders', '=', $id)
            ->where('b.type', '=', 'Other')
            ->distinct()
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
                'a.id_master_suppliers',
                'b.note',
                'c.address',
                'c.telephone',
                'c.fax'
            )
            ->leftJoin('purchase_requisitions as b', 'a.reference_number', '=', 'b.id')
            ->leftJoin('master_suppliers as c', 'a.id_master_suppliers', '=', 'c.id')
            ->where('a.id', '=', $id)
            ->get();

        $purchaseOrder_currency = DB::table('purchase_order_details as a')
            ->select('a.currency')
            ->where('a.id_purchase_orders', $id)
            ->first();

        return view('purchase.print_po', compact('purchaseOrder', 'data_detail_rm', 'data_detail_ta', 'data_detail_wip', 'data_detail_fg', 'results', 'data_detail_other', 'purchaseOrder_currency'));
    }
    public function print_pr($request_number)
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
            'purchase_requisition_details.outstanding_qty',
            'purchase_requisition_details.request_number',
            'purchase_requisition_details.remarks',

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
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'b.rm_code', 'd.nm_requester')
            ->where('a.id_purchase_requisitions', $request_number)
            ->get();

        $data_detail_ta = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'b.code', 'd.nm_requester')
            ->where('a.id_purchase_requisitions', $request_number)
            ->get();

        $data_detail_wip = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'b.wip_code', 'd.nm_requester')
            ->where('a.id_purchase_requisitions', $request_number)
            ->get();

        $data_detail_fg = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'b.product_code', 'd.nm_requester', 'b.perforasi')
            ->where('a.id_purchase_requisitions', $request_number)
            ->get();

        $data_detail_other = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'b.code', 'd.nm_requester')
            ->where('a.id_purchase_requisitions', $request_number)
            ->where('b.type', 'Other') // Kondisi where berdasarkan 'type' dari 'master_tool_auxiliaries'
            ->get();

        return view('purchase.print_pr', compact(
            'datas',
            'data_detail_rm',
            'data_detail_ta',
            'data_detail_wip',
            'data_detail_fg',
            'PurchaseRequisitions',
            'data_detail_other'
        ));
    }

    public function purchase_requisition(Request $request)
    {
        // Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = 'View List Purchase';
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);

        // Ambil data dari query
        $data = DB::table('purchase_requisition_details as prd')
            ->join('purchase_requisitions as pr', 'prd.id_purchase_requisitions', '=', 'pr.id')

            ->join('purchase_orders as po', 'pr.id', '=', 'po.reference_number')

            ->join('master_suppliers as ms', 'po.id_master_suppliers', '=', 'ms.id')

            ->join('purchase_order_details as pod', 'pod.id_purchase_orders', '=', 'po.id')

            ->join('good_receipt_notes as grn', 'pr.id', '=', 'grn.reference_number')

            ->join('good_receipt_note_details as grnd', 'grnd.id_good_receipt_notes', '=', 'grn.id')

            ->leftJoin('master_raw_materials as rm', function ($join) {
                $join->on('prd.master_products_id', '=', 'rm.id')
                    ->where('prd.type_product', 'RM');
            })
            ->leftJoin('master_product_fgs as fg', function ($join) {
                $join->on('prd.master_products_id', '=', 'fg.id')
                    ->where('prd.type_product', 'FG');
            })
            ->leftJoin('master_wips as w', function ($join) {
                $join->on('prd.master_products_id', '=', 'w.id')
                    ->where('prd.type_product', 'WIP');
            })
            ->leftJoin('master_tool_auxiliaries as ta', function ($join) {
                $join->on('prd.master_products_id', '=', 'ta.id')
                    ->whereIn('prd.type_product', ['TA', 'Other']);
            })
            ->leftJoin('master_units as c', 'prd.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'prd.cc_co', '=', 'd.id')
            ->select(
                'prd.*',
                'pr.request_number',
                'pr.date as date_prd',
                'pr.requester as requester_prd',
                'pr.status as status_prd',
                'pr.type as type_prd',

                'po.po_number',
                'po.delivery_date',
                'ms.name',
                'pod.price',
                'pod.discount',
                'pod.amount',
                'grnd.outstanding_qty as outstanding_qty_grnd',
                'pod.status as sts_pod',

                DB::raw("CASE 
                    WHEN prd.type_product = 'RM' THEN CONCAT(rm.rm_code, '-', rm.description)
                    WHEN prd.type_product = 'FG' THEN CONCAT(fg.product_code, '-', fg.description)
                    WHEN prd.type_product = 'WIP' THEN CONCAT(w.wip_code, '-', w.description)
                    WHEN prd.type_product = 'TA' THEN CONCAT(ta.code, '-', ta.description)
                    WHEN prd.type_product = 'Other' THEN CONCAT(ta.code, '-', ta.description)
                END as product_desc"),
                'c.unit_code',
                'd.nm_requester',
                'ms.name as supplier_name'
            )
            ->get();  // Ambil semua data

        return view('purchase.purchase_requisition', compact('data'));  // Kirim data ke Blade view
    }



    public function print_pr_ind($request_number)
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
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'b.rm_code', 'd.nm_requester')
            ->where('a.id_purchase_requisitions', $request_number)
            ->get();

        $data_detail_ta = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'b.code', 'd.nm_requester')
            ->where('a.id_purchase_requisitions', $request_number)
            ->get();

        $data_detail_wip = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'b.wip_code', 'd.nm_requester')
            ->where('a.id_purchase_requisitions', $request_number)
            ->get();

        $data_detail_fg = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'b.product_code', 'd.nm_requester', 'b.perforasi')
            ->where('a.id_purchase_requisitions', $request_number)
            ->get();

        $data_detail_other = DB::table('purchase_requisition_details as a')
            ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('master_requester as d', 'a.cc_co', '=', 'd.id')
            ->select('a.*', 'b.description', 'c.unit_code', 'b.code', 'd.nm_requester')
            ->where('a.id_purchase_requisitions', $request_number)
            ->where('b.type', 'Other') // Kondisi where berdasarkan 'type' dari 'master_tool_auxiliaries'
            ->get();

        return view('purchase.print_pr_ind', compact(
            'datas',
            'data_detail_rm',
            'data_detail_ta',
            'data_detail_wip',
            'data_detail_fg',
            'PurchaseRequisitions',
            'data_detail_other'
        ));
    }
    public function print_po_ind($id)
    {
        // $purchaseOrder = PurchaseOrders::findOrFail($id);
        $purchaseOrder = DB::table('purchase_orders as a')
            ->leftJoin('master_suppliers as b', 'a.id_master_suppliers', '=', 'b.id')
            ->leftJoin('master_term_payments as c', 'b.id_master_term_payments', '=', 'c.id')
            ->select('a.*', 'c.term_payment')
            ->where('a.id', $id)
            ->first();

        $data_detail_rm = DB::table('purchase_order_details as a')
            ->select(
                'a.master_products_id',
                DB::raw('MAX(a.type_product) as type_product'),
                DB::raw('MAX(b.description) as description'),
                DB::raw('MAX(a.qty) as qty'),
                DB::raw('MAX(c.unit) as unit'),
                DB::raw('MAX(a.price) as price'),
                DB::raw('MAX(a.discount) as discount'),
                DB::raw('MAX(a.tax) as tax'),
                DB::raw('MAX(a.amount) as amount'),
                DB::raw('MAX(a.note) as note'),
                DB::raw('MAX(a.id) as id'),
                DB::raw('MAX(a.currency) as currency'),
                DB::raw('MAX(f.remarks) as remarks')
            )
            ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('purchase_orders as d', 'a.id_purchase_orders', '=', 'd.id')
            ->leftJoin('purchase_requisitions as e', 'd.reference_number', '=', 'e.id')
            ->leftJoin('purchase_requisition_details as f', 'e.id', '=', 'f.id_purchase_requisitions')
            ->where('a.id_purchase_orders', '=', $id)
            ->groupBy('a.master_products_id')
            ->get();

        $data_detail_ta = DB::table('purchase_order_details as a')
            ->select('a.type_product', 'b.description', 'a.qty', 'c.unit', 'a.price', 'a.discount', 'a.tax', 'a.amount', 'a.note', 'a.id', 'a.note', 'a.currency', 'f.remarks')
            ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('purchase_orders as d', 'a.id_purchase_orders', '=', 'd.id')
            ->leftJoin('purchase_requisitions as e', 'd.reference_number', '=', 'e.id')
            ->leftJoin('purchase_requisition_details as f', 'e.id', '=', 'f.id_purchase_requisitions')
            ->where('a.id_purchase_orders', '=', $id)
            ->distinct()
            ->get();

        $data_detail_wip = DB::table('purchase_order_details as a')
            ->select('a.type_product', 'b.description', 'a.qty', 'c.unit', 'a.price', 'a.discount', 'a.tax', 'a.amount', 'a.note', 'a.id', 'a.note', 'a.currency', 'f.remarks')
            ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('purchase_orders as d', 'a.id_purchase_orders', '=', 'd.id')
            ->leftJoin('purchase_requisitions as e', 'd.reference_number', '=', 'e.id')
            ->leftJoin('purchase_requisition_details as f', 'e.id', '=', 'f.id_purchase_requisitions')
            ->where('a.id_purchase_orders', '=', $id)
            ->distinct()
            ->get();

        $data_detail_fg = DB::table('purchase_order_details as a')
            ->select('a.type_product', 'b.description', 'b.perforasi', 'a.qty', 'c.unit', 'a.price', 'a.discount', 'a.tax', 'a.amount', 'a.note', 'a.id', 'a.note', 'a.currency', 'f.remarks')
            ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('purchase_orders as d', 'a.id_purchase_orders', '=', 'd.id')
            ->leftJoin('purchase_requisitions as e', 'd.reference_number', '=', 'e.id')
            ->leftJoin('purchase_requisition_details as f', 'e.id', '=', 'f.id_purchase_requisitions')
            ->where('a.id_purchase_orders', '=', $id)
            ->distinct()
            ->get();

        $data_detail_other = DB::table('purchase_order_details as a')
            ->select('a.type_product', 'b.description', 'a.qty', 'c.unit', 'a.price', 'a.discount', 'a.tax', 'a.amount', 'a.note', 'a.id', 'a.note', 'a.currency', 'f.remarks')
            ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
            ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
            ->leftJoin('purchase_orders as d', 'a.id_purchase_orders', '=', 'd.id')
            ->leftJoin('purchase_requisitions as e', 'd.reference_number', '=', 'e.id')
            ->leftJoin('purchase_requisition_details as f', 'e.id', '=', 'f.id_purchase_requisitions')
            ->where('a.id_purchase_orders', '=', $id)
            ->where('b.type', '=', 'Other')
            ->distinct()
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
                'a.id_master_suppliers',
                'b.note',
                'c.address',
                'c.telephone',
                'c.fax'
            )
            ->leftJoin('purchase_requisitions as b', 'a.reference_number', '=', 'b.id')
            ->leftJoin('master_suppliers as c', 'a.id_master_suppliers', '=', 'c.id')
            ->where('a.id', '=', $id)
            ->get();

        $purchaseOrder_currency = DB::table('purchase_order_details as a')
            ->select('a.currency')
            ->where('a.id_purchase_orders', $id)
            ->first();

        return view('purchase.print_po_ind', compact('purchaseOrder', 'data_detail_rm', 'data_detail_ta', 'data_detail_wip', 'data_detail_fg', 'results', 'data_detail_other', 'purchaseOrder_currency'));
    }
}
