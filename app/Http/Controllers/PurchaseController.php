<?php

namespace App\Http\Controllers;

use App\Models\MstCurrencies;
use App\Models\MstProductFG;
use App\Models\MstRawMaterial;
use DataTables;
use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Model
use App\Models\PurchaseRequisitions;
use App\Models\PurchaseOrders;
use App\Models\MstRequester;
use App\Models\MstSupplier;
use App\Models\MstToolAux;
use App\Models\MstUnits;
use App\Models\MstWip;
use App\Models\PurchaseRequisitionsDetail;
use App\Models\PurchaseOrderDetails;

class PurchaseController extends Controller
{
    use AuditLogsTrait;

    //DATA PR
    public function indexPR(Request $request)
    {
        $datas = PurchaseRequisitions::select('purchase_requisitions.id', 'purchase_requisitions.request_number',
                'purchase_requisitions.date as requisition_date', 'purchase_requisitions.qc_check', 'purchase_requisitions.note',
                'purchase_requisitions.type', 'purchase_requisitions.status',
                'master_suppliers.name as supplier_name', 'master_requester.nm_requester', 'purchase_orders.po_number',
                \DB::raw('(SELECT COUNT(*) FROM purchase_requisition_details WHERE purchase_requisition_details.id_purchase_requisitions = purchase_requisitions.id) as count'))
            ->leftjoin('master_suppliers', 'purchase_requisitions.id_master_suppliers', 'master_suppliers.id')
            ->leftjoin('master_requester', 'purchase_requisitions.requester', 'master_requester.id')
            ->leftjoin('purchase_orders', 'purchase_requisitions.id', 'purchase_orders.reference_number');

        if ($request->has('filterType') && $request->filterType != '' && $request->filterType != 'All') {
            $datas->where('purchase_requisitions.type', $request->filterType);
        }
        if ($request->has('filterStatus') && $request->filterStatus != '' && $request->filterStatus != 'All') {
            $datas->where('purchase_requisitions.status', $request->filterStatus);
        }

        $datas = $datas->orderBy('purchase_requisitions.created_at', 'desc')->get();

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
            $this->auditLogsShort('Posted Purchase Requisitions ('.$id.')');
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
            $this->auditLogsShort('Un-Posted Purchase Requisitions ('.$id.')');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Un-Posted Data PR']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Un-Posted Data PR!']);
        }
    }
    public function printPR($lang, $id)
    {
        $id = decrypt($id);
        $data = PurchaseRequisitions::select('purchase_requisitions.*', 'master_suppliers.name')
            ->leftJoin('master_suppliers', 'purchase_requisitions.id_master_suppliers', 'master_suppliers.id')
            ->where('purchase_requisitions.id', $id)
            ->first();

        $itemDatas = PurchaseRequisitionsDetail::select(
            'purchase_requisition_details.*',
            'master_units.unit',
            'master_units.unit_code',
            'master_requester.nm_requester as cc_co_name',
            DB::raw('
                CASE 
                    WHEN purchase_requisition_details.type_product = "RM" THEN master_raw_materials.description 
                    WHEN purchase_requisition_details.type_product = "WIP" THEN master_wips.description 
                    WHEN purchase_requisition_details.type_product = "FG" THEN master_product_fgs.description 
                    WHEN purchase_requisition_details.type_product IN ("TA", "Other") THEN master_tool_auxiliaries.description 
                END as product_desc'),
            DB::raw('
                CASE 
                    WHEN purchase_requisition_details.type_product = "RM" THEN master_raw_materials.rm_code 
                    WHEN purchase_requisition_details.type_product = "WIP" THEN master_wips.wip_code 
                    WHEN purchase_requisition_details.type_product = "FG" THEN master_product_fgs.product_code 
                    WHEN purchase_requisition_details.type_product IN ("TA", "Other") THEN master_tool_auxiliaries.code 
                END as code'),
            DB::raw('
                CASE 
                    WHEN purchase_requisition_details.type_product = "FG" THEN master_product_fgs.perforasi 
                END as perforasi')
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
            ->where('purchase_requisition_details.id_purchase_requisitions', $id)
            ->orderBy('purchase_requisition_details.created_at')
            ->get();

        $view = ($lang === 'en') ? 'purchase-requisition.print' : 'purchase-requisition.printIDN';
        return view($view, compact('data', 'itemDatas'));
    }
    public function getPRDetails(Request $request)
    {
        $referenceId = $request->input('reference_id');
        $purchaseRequest = PurchaseRequisitions::select('id_master_suppliers', 'qc_check', 'type')->where('id', $referenceId)->first();
        if ($purchaseRequest) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id_master_suppliers' => $purchaseRequest->id_master_suppliers,
                    'qc_check' => $purchaseRequest->qc_check,
                    'type' => $purchaseRequest->type,
                ]
            ]);
        } else {
            return response()->json(['success' => false]);
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
            
        return view('purchase-requisition.item.edit', compact('data', 'products', 'units', 'requesters'));
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

    //PR ITEM INDEX
    public function indexItemPR(Request $request)
    {
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
            ->get();
            
        // Audit Log
        $this->auditLogsShort('View List Purchase Requisition Items');

        return view('purchase-requisition-detail.index', compact('data'));
    }

    //DATA PO
    private function romanMonth($month)
    {
        // Fungsi untuk mengonversi bulan dalam format angka menjadi format romawi
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
    public function indexPO(Request $request)
    {
        $year = date('y');
        $lastCode = PurchaseOrders::orderBy('created_at', 'desc')->value(DB::raw('LEFT(po_number, 3)'));
        $lastCode = $lastCode ? $lastCode : 0;
        $nextCode = $lastCode + 1;
        $currentMonth = $this->romanMonth(date('n'));
        $formattedCode = sprintf('%03d/PO/OTP/%s/%02d', $nextCode, $currentMonth, $year);

        $postedPRs = PurchaseRequisitions::select('id', 'request_number')->where('status', 'Posted')->get();
        $suppliers = MstSupplier::get();

        $datas = PurchaseOrders::select(
            'purchase_orders.id',
            'purchase_orders.po_number',
            'purchase_orders.date',
            'purchase_orders.down_payment',
            'purchase_orders.total_amount',
            'purchase_orders.qc_check',
            'purchase_orders.type',
            'purchase_orders.status',
            'purchase_requisitions.request_number as reference_number',
            'master_suppliers.name as supplier_name',
            \DB::raw('(SELECT COUNT(*) FROM purchase_order_details WHERE purchase_order_details.id_purchase_orders = purchase_orders.id) as count')
        )
        ->leftJoin('purchase_requisitions', 'purchase_orders.reference_number', 'purchase_requisitions.id')
        ->leftJoin('master_suppliers', 'purchase_orders.id_master_suppliers', 'master_suppliers.id');

        if ($request->has('filterType') && $request->filterType != '' && $request->filterType != 'All') {
            $datas->where('purchase_orders.type', $request->filterType);
        }
        if ($request->has('filterStatus') && $request->filterStatus != '' && $request->filterStatus != 'All') {
            $datas->where('purchase_orders.status', $request->filterStatus);
        }

        $datas = $datas->orderBy('purchase_orders.created_at', 'desc')->get();

        // Datatables
        if ($request->ajax()) {
            return DataTables::of($datas)
                ->addColumn('action', function ($data){
                    return view('purchase-order.action', compact('data'));
                })->make(true);
        }

        //Audit Log
        $this->auditLogsShort('View List Purchase Order');
        return view('purchase-order.index', compact('formattedCode', 'postedPRs', 'suppliers'));
    }
    public function storePO(Request $request)
    {
        $request->validate([
            'po_number' => 'required',
            'date' => 'required',
            'reference_number' => 'required',
            'id_master_suppliers' => 'required',
            'qc_check' => 'required',
            'non_invoiceable' => 'required',
            'vendor_taxable' => 'required',
            'down_payment' => 'required',
            'status' => 'required',
            'type' => 'required',
        ], [
            'po_number.required' => 'PO Number masih kosong.',
            'date.required' => 'Date masih kosong.',
            'reference_number.required' => 'Reference Number harus diisi.',
            'id_master_suppliers.required' => 'Suppliers harus diisi.',
            'qc_check.required' => 'QC Check harus diisi.',
            'non_invoiceable.required' => 'Non Invoiceable harus diisi.',
            'vendor_taxable.required' => 'Vendor Taxable harus diisi.',
            'down_payment.required' => 'Down Payment harus diisi.',
            'status.required' => 'Status masih kosong.',
            'type.required' => 'Type masih kosong.',
        ]);
        
        $year = date('y');
        $lastCode = PurchaseOrders::orderBy('created_at', 'desc')->value(DB::raw('LEFT(po_number, 3)'));
        $lastCode = $lastCode ? $lastCode : 0;
        $nextCode = $lastCode + 1;
        $currentMonth = $this->romanMonth(date('n'));
        $formattedCode = sprintf('%03d/PO/OTP/%s/%02d', $nextCode, $currentMonth, $year);
        
        DB::beginTransaction();
        try{
            $storeData = PurchaseOrders::create([
                'po_number' => $formattedCode,
                'date' => $request->date,
                'delivery_date' => $request->delivery_date,
                'reference_number' => $request->reference_number,
                'id_master_suppliers' => $request->id_master_suppliers,
                'qc_check' => $request->qc_check,
                'non_invoiceable' => $request->non_invoiceable,
                'vendor_taxable' => $request->vendor_taxable,
                'down_payment' => str_replace(['.', ','], ['', '.'], $request->down_payment),
                'own_remarks' => $request->own_remarks,
                'supplier_remarks' => $request->supplier_remarks,
                'status' => $request->status,
                'type' => $request->type,
            ]);
            PurchaseRequisitions::where('id', $request->reference_number)->update(['status' => 'Created PO']);
            // Get Item PR
            $dataItemPR = PurchaseRequisitionsDetail::where('id_purchase_requisitions', $request->reference_number)->get();
            foreach($dataItemPR as $item){
                PurchaseOrderDetails::create([
                    'id_purchase_orders' => $storeData->id,
                    'type_product' => $item->type_product,
                    'master_products_id' => $item->master_products_id,
                    'qty' => $item->qty,
                    'master_units_id' => $item->master_units_id,
                ]);
            }

            // Audit Log
            $this->auditLogsShort('Tambah Purchase Order ID : ('.$storeData->id.')');
            DB::commit();
            return redirect()->route('po.edit', encrypt($storeData->id))->with(['success' => 'Berhasil Tambah Data PO, Silahkan Tambahkan / Update Item Produk']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Tambah Data PO!']);
        }
    }
    public function editPO($id)
    {
        $id = decrypt($id);
        $data = PurchaseOrders::select('purchase_orders.*', 'purchase_requisitions.request_number', 'master_suppliers.name')
            ->leftJoin('purchase_requisitions', 'purchase_orders.reference_number', 'purchase_requisitions.id')
            ->leftJoin('master_suppliers', 'purchase_orders.id_master_suppliers', 'master_suppliers.id')
            ->where('purchase_orders.id', $id)
            ->first();

        // Dropdown
        $reference_number = PurchaseRequisitions::select('id', 'request_number')->where('status', 'Posted')->orWhere('id', $data->reference_number)->get();
        $suppliers = MstSupplier::get();

        $currency = MstCurrencies::get();
        $units = MstUnits::get();
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

        $itemDatas = PurchaseOrderDetails::select(
            'purchase_order_details.*',
            'master_units.unit',
            'master_units.unit_code',
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
        $this->auditLogsShort('View Edit Purchase Order ID : (' . $id . ')');

        return view('purchase-order.edit', compact(
            'reference_number',
            'suppliers',
            'currency',
            'units',
            'products',
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
        $changeRefNumber = $dataBefore->reference_number != $request->reference_number;

        $dataBefore->date = $request->date;
        $dataBefore->delivery_date = $request->delivery_date;
        $dataBefore->reference_number = $request->reference_number;
        $dataBefore->id_master_suppliers = $request->id_master_suppliers;
        $dataBefore->qc_check = $request->qc_check;
        $dataBefore->down_payment = str_replace(['.', ','], ['', '.'], $request->down_payment);
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
                    'down_payment' => str_replace(['.', ','], ['', '.'], $request->down_payment),
                    'own_remarks' => $request->own_remarks,
                    'supplier_remarks' => $request->supplier_remarks,
                    'status' => $request->status,
                    'type' => $request->type,
                ]);

                //IF Ref Number Change
                if($changeRefNumber){
                    //Rollback Status PR Before
                    PurchaseRequisitions::where('id', $request->reference_number_before)->update(['status' => 'Posted']);
                    //Update Status PR After
                    PurchaseRequisitions::where('id', $request->reference_number)->update(['status' => 'Created PO']);
                    //Delete PO Detail Before
                    PurchaseOrderDetails::where('id_purchase_orders', $id)->delete();
                    //Get Item PR After
                    $dataItemPR = PurchaseRequisitionsDetail::where('id_purchase_requisitions', $request->reference_number)->get();
                    foreach($dataItemPR as $item){
                        PurchaseOrderDetails::create([
                            'id_purchase_orders' => $id,
                            'type_product' => $item->type_product,
                            'master_products_id' => $item->master_products_id,
                            'qty' => $item->qty,
                            'master_units_id' => $item->master_units_id,
                        ]);
                    }
                    //Set Null All Total
                    PurchaseOrders::where('id', $id)->update([
                        'sub_total' => null,
                        'total_discount' => null,
                        'total_sub_amount' => null,
                        'total_ppn' => null,
                        'total_amount' => null,
                    ]);
                }
    
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
    public function deletePO($id)
    {
        $id = decrypt($id);
        DB::beginTransaction();
        try{
            //Rollback Status PR
            $reference_number = PurchaseOrders::where('id', $id)->first()->reference_number;
            PurchaseRequisitions::where('id', $reference_number)->update(['status' => 'Posted']);
            //Delete
            PurchaseOrders::where('id', $id)->delete();
            PurchaseOrderDetails::where('id_purchase_orders', $id)->delete();

            // Audit Log
            $this->auditLogsShort('Hapus Purchase Order');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Hapus Data PO']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Hapus Data PO!']);
        }
    }
    public function postedPO($id)
    {
        $id = decrypt($id);

        //Check Set Price In Product Or Not
        $product = PurchaseOrderDetails::where('id_purchase_orders', $id)->get();
        $hasNullPrice = $product->contains(function ($order) {
            return is_null($order->price);
        });
        if ($hasNullPrice) {
            return redirect()->back()->with(['fail' => 'Gagal Posted Data PO!, Masih Ada Produk dalam PO yang Belum Memiliki Harga']);
        }

        DB::beginTransaction();
        try{
            PurchaseOrders::where('id', $id)->update(['status' => 'Posted']);

            // Audit Log
            $this->auditLogsShort('Posted Purchase Orders ('.$id.')');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Posted Data PO']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Posted Data PO!']);
        }
    }
    public function unpostedPO($id)
    {
        $id = decrypt($id);
        DB::beginTransaction();
        try{
            PurchaseOrders::where('id', $id)->update(['status' => 'Un Posted']);

            // Audit Log
            $this->auditLogsShort('Un-Posted Purchase Order ('.$id.')');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Un-Posted Data PO']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Un-Posted Data PO!']);
        }
    }
    public function printPO($lang, $id)
    {
        $id = decrypt($id);
        $data = PurchaseOrders::select('purchase_orders.*', 
                'purchase_requisitions.request_number',
                'master_suppliers.name', 'master_suppliers.address', 'master_suppliers.telephone', 'master_suppliers.fax',
                'master_term_payments.term_payment')
            ->leftJoin('purchase_requisitions', 'purchase_orders.reference_number','purchase_requisitions.id')
            ->leftJoin('master_suppliers', 'purchase_orders.id_master_suppliers', 'master_suppliers.id')
            ->leftJoin('master_term_payments', 'master_suppliers.id_master_term_payments', 'master_term_payments.id')
            ->where('purchase_orders.id', $id)
            ->first();
            
        $itemDatas = PurchaseOrderDetails::select(
            'purchase_order_details.*',
            'master_units.unit',
            'master_units.unit_code',
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

        $view = ($lang === 'en') ? 'purchase-order.print' : 'purchase-order.printIDN';
        return view($view, compact('data', 'itemDatas'));

        $itemDatas = PurchaseRequisitionsDetail::select(
            'purchase_requisition_details.*',
            'master_units.unit',
            'master_units.unit_code',
            'master_requester.nm_requester as cc_co_name',
            DB::raw('
                CASE 
                    WHEN purchase_requisition_details.type_product = "RM" THEN master_raw_materials.description 
                    WHEN purchase_requisition_details.type_product = "WIP" THEN master_wips.description 
                    WHEN purchase_requisition_details.type_product = "FG" THEN master_product_fgs.description 
                    WHEN purchase_requisition_details.type_product IN ("TA", "Other") THEN master_tool_auxiliaries.description 
                END as product_desc'),
            DB::raw('
                CASE 
                    WHEN purchase_requisition_details.type_product = "RM" THEN master_raw_materials.rm_code 
                    WHEN purchase_requisition_details.type_product = "WIP" THEN master_wips.wip_code 
                    WHEN purchase_requisition_details.type_product = "FG" THEN master_product_fgs.product_code 
                    WHEN purchase_requisition_details.type_product IN ("TA", "Other") THEN master_tool_auxiliaries.code 
                END as code'),
            DB::raw('
                CASE 
                    WHEN purchase_requisition_details.type_product = "FG" THEN master_product_fgs.perforasi 
                END as perforasi')
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
            ->where('purchase_requisition_details.id_purchase_requisitions', $id)
            ->orderBy('purchase_requisition_details.created_at')
            ->get();

        $view = ($lang === 'en') ? 'purchase-requisition.print' : 'purchase-requisition.printIDN';
        return view($view, compact('data', 'itemDatas'));


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
    //ITEM PO
    function formatNumber($value) 
    {
        return str_replace(['.', ','], ['', '.'], $value);
    }
    public function storeItemPO(Request $request, $id)
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
            'sub_total' => 'required',
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
            'sub_total.required' => 'Subtotal masih kosong.',
            'discount.required' => 'Diskon masih kosong.',
            'amount.required' => 'Jumlah harus diisi.',
            'tax.required' => 'Pajak harus diisi.',
        ]);
        
        DB::beginTransaction();
        try{
            // Add ITEM
            $storeData = PurchaseOrderDetails::create([
                'id_purchase_orders' => $request->id_purchase_orders,
                'type_product' => $request->type_product,
                'master_products_id' => $request->master_products_id,
                'qty' => $request->qty,
                'master_units_id' => $request->master_units_id,
                'currency' => $request->currency,
                'price' => str_replace(['.', ','], ['', '.'], $request->price),
                'sub_total' => str_replace(['.', ','], ['', '.'], $request->sub_total),
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
            // Round up to 3 decimal places
            $sub_total = round($totals->total_sub_total, 3);
            $total_discount = round($totals->total_discount, 3);
            $total_sub_amount = round($totals->total_sub_amount, 3);
            $total_ppn = round($totals->total_ppn, 3);
            $total_amount = round($totals->total_amount, 3);
            // Update PO Data
            PurchaseOrders::where('id', $id)->update([
                'sub_total' => $sub_total,
                'total_discount' => $total_discount,
                'total_sub_amount' => $total_sub_amount,
                'total_ppn' => $total_ppn,
                'total_amount' => $total_amount,
            ]);

            // Audit Log
            $this->auditLogsShort('Add New Item PO ID ('. $storeData->id . ')');

            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Tambah Item Produk Baru Ke Tabel', 'scrollTo' => 'tableItem']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Tambah Item Produk Baru!', 'scrollTo' => 'tableItem']);
        }
    }
    public function editItemPO($id)
    {
        $id = decrypt($id);
        $data = PurchaseOrderDetails::where('id', $id)->first();

        // Dropdown
        $currency = MstCurrencies::get();
        $units = MstUnits::get();
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

        return view('purchase-order.item.edit', compact('id', 'data', 'currency', 'units', 'products'));
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
            'sub_total' => 'required',
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
            'sub_total.required' => 'Subtotal masih kosong.',
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
        $dataBefore->sub_total = str_replace(['.', ','], ['', '.'], $request->sub_total);
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
                    'sub_total' => str_replace(['.', ','], ['', '.'], $request->sub_total),
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
                // Round up to 3 decimal places
                $sub_total = round($totals->total_sub_total, 3);
                $total_discount = round($totals->total_discount, 3);
                $total_sub_amount = round($totals->total_sub_amount, 3);
                $total_ppn = round($totals->total_ppn, 3);
                $total_amount = round($totals->total_amount, 3);
                // Update PO Data
                PurchaseOrders::where('id', $request->id_purchase_orders)->update([
                    'sub_total' => $sub_total,
                    'total_discount' => $total_discount,
                    'total_sub_amount' => $total_sub_amount,
                    'total_ppn' => $total_ppn,
                    'total_amount' => $total_amount,
                ]);
                // Audit Log
                $this->auditLogsShort('Update Item PO ID ('. $id . ')');
    
                DB::commit();
                return redirect()->route('po.edit', encrypt($request->id_purchase_orders))->with(['success' => 'Berhasil Perbaharui Item Produk', 'scrollTo' => 'tableItem']);
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
            // Round up to 3 decimal places
            $sub_total = round($totals->total_sub_total, 3);
            $total_discount = round($totals->total_discount, 3);
            $total_sub_amount = round($totals->total_sub_amount, 3);
            $total_ppn = round($totals->total_ppn, 3);
            $total_amount = round($totals->total_amount, 3);
            // Update PO Data
            PurchaseOrders::where('id', $request->id_purchase_orders)->update([
                'sub_total' => $sub_total,
                'total_discount' => $total_discount,
                'total_sub_amount' => $total_sub_amount,
                'total_ppn' => $total_ppn,
                'total_amount' => $total_amount,
            ]);

            // Audit Log
            $this->auditLogsShort('Delete Item PO ID ('. $id . ')');

            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Hapus Item Produk', 'scrollTo' => 'tableItem']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Hapus Item Produk!', 'scrollTo' => 'tableItem']);
        }
    }
}
