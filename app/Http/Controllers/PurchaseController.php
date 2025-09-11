<?php

namespace App\Http\Controllers;

use App\Models\MstCurrencies;
use App\Models\MstProductFG;
use App\Models\MstRawMaterial;
use DataTables;
use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\PurchaseRequisitionsExport;
use App\Exports\PurchaseRequisitionsItemExport;
use App\Exports\PurchaseOrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

// Model
use App\Models\PurchaseRequisitions;
use App\Models\PurchaseOrders;
use App\Models\MstRequester;
use App\Models\MstRules;
use App\Models\MstSupplier;
use App\Models\MstToolAux;
use App\Models\MstUnits;
use App\Models\MstWip;
use App\Models\PurchaseRequisitionsDetail;
use App\Models\PurchaseOrderDetails;
use App\Models\PurchaseRequisitionsPrice;
use App\Models\GoodReceiptNote;

class PurchaseController extends Controller
{
    use AuditLogsTrait;

    //DATA PR
    public function indexPR(Request $request)
    {
        $reference_number = $request->reference_number;
        $idUpdated = $request->get('idUpdated');

        $datas = PurchaseRequisitions::select('purchase_requisitions.id', 'purchase_requisitions.request_number',
                'purchase_requisitions.date as requisition_date', 'purchase_requisitions.qc_check', 'purchase_requisitions.note',
                'purchase_requisitions.type', 'purchase_requisitions.status', 'purchase_requisitions.input_price',
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

        // Get Page Number
        $page_number = 1;
        if ($idUpdated) {
            $page_size = 5;
            $item = $datas->firstWhere('id', $idUpdated);
            if ($item) {
                $index = $datas->search(function ($value) use ($idUpdated) {
                    return $value->id == $idUpdated;
                });
                $page_number = (int) ceil(($index + 1) / $page_size);
            } else {
                $page_number = 1;
            }
        }

        // Datatables
        if ($request->ajax()) {
            return DataTables::of($datas)
                ->addColumn('action', function ($data){
                    return view('purchase-requisition.action', compact('data'));
                })->make(true);
        }

        //Audit Log
        $this->auditLogsShort('View List Purchase Requisition');
        return view('purchase-requisition.index', compact('idUpdated', 'page_number', 'reference_number'));
    }

    private function previewPRNumber()
    {
        $lastCode = PurchaseRequisitions::whereYear('created_at', date('Y'))
            ->orderBy('created_at', 'desc')
            ->value(DB::raw('RIGHT(request_number, 7)'));
        $lastCode = $lastCode ? (int)$lastCode : 0;
        $nextCode = $lastCode + 1;
        return 'PR' . date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);
    }
    private function generatePRNumber()
    {
        $year = date('Y');
        return DB::transaction(function () use ($year) {
            // Lock rows for current year to avoid race condition
            $lastCode = PurchaseRequisitions::whereYear('created_at', $year)
                ->lockForUpdate()
                ->orderBy('created_at', 'desc')
                ->value(DB::raw('RIGHT(request_number, 7)'));
            $lastCode = $lastCode ? (int)$lastCode : 0;
            $nextCode = $lastCode + 1;
            return 'PR' . date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);
        });
    }
    private function getPRNumber()
    {
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')->value(DB::raw('RIGHT(request_number, 7)'));
        $lastCode = $lastCode ? $lastCode : 0;
        $nextCode = $lastCode + 1;
        $formattedCode = 'PR' . date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        return $formattedCode;
    }


    public function addPR($type)
    {
        // dd($type);
        if(!in_array($type, ['RM', 'WIP', 'FG', 'TA', 'Other'])){
            return redirect()->route('dashboard')->with(['fail' => 'Tidak Ada Type '. $type]);
        }
        // $formattedCode = $this->previewPRNumber();
        $formattedCode = $this->getPRNumber();
        
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
        
        DB::beginTransaction();
        try{
            // $formattedCode = $this->generatePRNumber();
            $formattedCode = $this->getPRNumber();

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
        $currency = MstCurrencies::get();

        $itemDatas = PurchaseRequisitionsDetail::select(
            'purchase_requisition_details.*',
            'master_units.unit', 'master_units.unit_code',
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
                     ->whereIn('purchase_requisition_details.type_product', ['TA', 'Other']);
            })
            ->leftJoin('master_units', 'purchase_requisition_details.master_units_id', '=', 'master_units.id')
            ->leftJoin('master_requester', 'purchase_requisition_details.cc_co', '=', 'master_requester.id')
            ->where('purchase_requisition_details.id_purchase_requisitions', $data->id)
            ->orderBy('purchase_requisition_details.created_at')
            ->get();
            
        return view('purchase-requisition.edit', compact('data', 'products', 'suppliers', 'units', 'requesters', 'currency', 'itemDatas'));
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
                PurchaseOrders::where('reference_number', $id)->update([
                    'id_master_suppliers' => $request->id_master_suppliers,
                    'qc_check' => $request->qc_check,
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
            $dataPR = PurchaseRequisitions::where('id', $id)->first();
            // Delete PR
            PurchaseRequisitions::where('id', $id)->delete();
            PurchaseRequisitionsDetail::where('id_purchase_requisitions', $id)->delete();

            // IF PR Inpit Price
            if($dataPR->input_price == 'Y'){
                PurchaseRequisitionsPrice::where('id_purchase_requisitions', $id)->delete();
            }

            // IF PO has been created
            $dataPO = PurchaseOrders::where('reference_number', $id)->first();
            if($dataPO){
                PurchaseOrders::where('reference_number', $id)->delete();
                PurchaseOrderDetails::where('id_purchase_orders', $dataPO->id)->delete();
            }

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
            // Check PO Created Or NOT
            $status = PurchaseOrders::where('reference_number', $id)->exists() ? 'Created PO' : 'Posted';
            // Update Status PR
            PurchaseRequisitions::where('id', $id)->update(['status' => $status]);

            // Audit Log
            $this->auditLogsShort('Posted Purchase Requisitions ('.$id.')');
            DB::commit();
            return redirect()->route('pr.index', ['idUpdated' => $id])->with(['success' => 'Berhasil Posted Data PR']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->route('pr.index', ['idUpdated' => $id])->with(['fail' => 'Gagal Posted Data PR!']);
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
            return redirect()->route('pr.index', ['idUpdated' => $id])->with(['success' => 'Berhasil Un-Posted Data PR']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->route('pr.index', ['idUpdated' => $id])->with(['fail' => 'Gagal Un-Posted Data PR!']);
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
                     ->whereIn('purchase_requisition_details.type_product', ['TA', 'Other']);
            })
            ->leftJoin('master_units', 'purchase_requisition_details.master_units_id', '=', 'master_units.id')
            ->leftJoin('master_requester', 'purchase_requisition_details.cc_co', '=', 'master_requester.id')
            ->where('purchase_requisition_details.id_purchase_requisitions', $id)
            ->orderBy('purchase_requisition_details.created_at')
            ->get();

        $view = ($lang === 'en') ? 'purchase-requisition.print' : 'purchase-requisition.printIDN';
        return view($view, compact('data', 'itemDatas'));
    }
    public function exportPR(Request $request)
    {
        // dd($request->all());

        $datas = PurchaseRequisitionsDetail::select(
            'purchase_requisitions.id', 'purchase_requisitions.request_number', 'purchase_requisitions.date as requisition_date', 'master_suppliers.name as supplier_name', 'requester.nm_requester as requester_name',
            'purchase_requisitions.qc_check', 'purchase_requisitions.note', 'purchase_orders.po_number', 'purchase_requisitions.type', 'purchase_requisitions.status as statusPR',
            'purchase_requisitions.created_at as createdPR', 'purchase_requisitions.updated_at as updatedPR', 
            DB::raw('
                CASE 
                    WHEN purchase_requisition_details.type_product = "RM" THEN master_raw_materials.description 
                    WHEN purchase_requisition_details.type_product = "WIP" THEN master_wips.description 
                    WHEN purchase_requisition_details.type_product = "FG" THEN master_product_fgs.description 
                    WHEN purchase_requisition_details.type_product IN ("TA", "Other") THEN master_tool_auxiliaries.description 
                END as product_desc'),
            'purchase_requisition_details.required_date', 'cc_co.nm_requester as cc_co_name',
            'purchase_requisition_details.qty', 'purchase_requisition_details.cancel_qty', 'purchase_requisition_details.outstanding_qty',
            'master_units.unit', 'master_units.unit_code',
            'purchase_requisition_details.remarks', 'purchase_requisition_details.status',
            'purchase_requisition_details.created_at as createdItem', 'purchase_requisition_details.updated_at as updatedItem', 
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
                        ->whereIn('purchase_requisition_details.type_product', ['TA', 'Other']);
            })
            ->leftJoin('purchase_requisitions', 'purchase_requisition_details.id_purchase_requisitions', 'purchase_requisitions.id')
            ->leftJoin('master_units', 'purchase_requisition_details.master_units_id', 'master_units.id')
            ->leftJoin('master_requester as requester', 'purchase_requisitions.requester', 'requester.id')
            ->leftJoin('master_suppliers', 'purchase_requisitions.id_master_suppliers', 'master_suppliers.id')
            ->leftJoin('purchase_orders', 'purchase_requisitions.id', 'purchase_orders.reference_number')
            ->leftJoin('master_requester as cc_co', 'purchase_requisition_details.cc_co', 'cc_co.id')
            ->orderBy('purchase_requisitions.created_at');

            if ($request->has('typeItem') && $request->typeItem != '' && $request->typeItem != 'Semua Type') {
                $datas->where('purchase_requisitions.type', $request->typeItem);
            }
            if ($request->has('status') && $request->status != '' && $request->status != 'Semua Status') {
                $datas->where('purchase_requisitions.status', $request->status);
            }
            if($request->has('dateFrom') && $request->dateFrom != '' && $request->has('dateTo') && $request->dateTo != ''){
                $datas->whereBetween('purchase_requisitions.date', [$request->dateFrom, $request->dateTo]);
            }

        $filename = 'Export_PR_' . Carbon::now()->format('d_m_Y_H_i') . '.xlsx';
        return Excel::download(new PurchaseRequisitionsExport($datas->get(), $request), $filename);
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
                'qty' => str_replace(['.', ','], ['', '.'], $request->qty),
                'outstanding_qty' => str_replace(['.', ','], ['', '.'], $request->qty),
                'master_units_id' => $request->master_units_id,
                'required_date' => $request->required_date,
                'cc_co' => $request->cc_co,
                'remarks' => $request->remarks,
                'request_number' => $request->request_number,
            ]);

            // ADD ITEM PRODUCT IN PO DETAIL ALSO IF PO IS ALREADY EXIST
            $dataPO = PurchaseOrders::where('reference_number', $id)->first();
            if($dataPO){
                PurchaseOrderDetails::create([
                    'id_purchase_orders' => $dataPO->id,
                    'type_product' => $request->type_product,
                    'master_products_id' => $request->master_products_id,
                    'qty' => $request->qty,
                    'master_units_id' => $request->master_units_id,
                    'id_purchase_requisition_details' => $storeData->id,
                ]);
            }

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
        $dataBefore->qty = str_replace(['.', ','], ['', '.'], $request->qty);
        $dataBefore->master_units_id = $request->master_units_id;
        $dataBefore->required_date = $request->required_date;
        $dataBefore->cc_co = $request->cc_co;
        $dataBefore->remarks = $request->remarks;

        if($dataBefore->isDirty()){
            DB::beginTransaction();
            try{
                $dataPR = PurchaseRequisitions::where('id', $dataBefore->id_purchase_requisitions)->first();
                PurchaseRequisitionsDetail::where('id', $id)->update([
                    'master_products_id' => $request->master_products_id,
                    'qty' => str_replace(['.', ','], ['', '.'], $request->qty),
                    'outstanding_qty' => str_replace(['.', ','], ['', '.'], $request->qty),
                    'master_units_id' => $request->master_units_id,
                    'required_date' => $request->required_date,
                    'cc_co' => $request->cc_co,
                    'remarks' => $request->remarks,
                ]);
                // Update Item PO Also IF Has Created PO
                PurchaseOrderDetails::where('id_purchase_requisition_details', $id)->update([
                    'master_products_id' => $request->master_products_id,
                    'qty' => str_replace(['.', ','], ['', '.'], $request->qty),
                    'outstanding_qty' => str_replace(['.', ','], ['', '.'], $request->qty),
                    'master_units_id' => $request->master_units_id,
                ]);

                // IF Input Price Re-Calculate Price With New QTY
                if ($dataPR->input_price == 'Y') {
                    $dataItemPR = PurchaseRequisitionsDetail::where('id', $id)->first();
                    if($dataItemPR->price){
                        $qty = str_replace(['.', ','], ['', '.'], $request->qty);
                        $price = isset($dataItemPR->price) ? $dataItemPR->price : 0;
                        $discount = isset($dataItemPR->discount) ? $dataItemPR->discount : 0;
                        $tax_rate = isset($dataItemPR->tax_rate) ? $dataItemPR->tax_rate : 0;
                        $sub_total = round(($qty * $price), 6);
                        $amount = round(($sub_total - $discount), 6);
                        $tax_value = round((($tax_rate/100) * $amount), 6);
                        $total_amount = round(($amount + $tax_value), 6);

                        PurchaseRequisitionsDetail::where('id', $id)->update([
                            'sub_total' => $sub_total,
                            'amount' => $amount,
                            'tax_value' => $tax_value,
                            'total_amount' => $total_amount
                        ]);
                        $totals = PurchaseRequisitionsDetail::where('id_purchase_requisitions', $dataBefore->id_purchase_requisitions)
                            ->selectRaw('SUM(sub_total) as total_sub_total, SUM(discount) as total_discount, SUM(amount) as total_sub_amount,
                                SUM(tax_value) as total_ppn, SUM(total_amount) as total_amount')
                            ->first();
                        // Round up to 6 decimal places
                        $sub_total = round($totals->total_sub_total, 6);
                        $total_discount = round($totals->total_discount, 6);
                        $total_sub_amount = round($totals->total_sub_amount, 6);
                        $total_ppn = round($totals->total_ppn, 6);
                        $total_amount = round($totals->total_amount, 6);
                        // Update PR Data
                        PurchaseRequisitions::where('id', $dataBefore->id_purchase_requisitions)->update([
                            'sub_total' => $sub_total,
                            'total_discount' => $total_discount,
                            'total_sub_amount' => $total_sub_amount,
                            'total_ppn' => $total_ppn,
                            'total_amount' => $total_amount,
                        ]);
                    }
                }

                // IF Has Created PO Re-Calculate Price With New QTY
                $dataItemPO = PurchaseOrderDetails::where('id_purchase_requisition_details', $id)->first();
                if($dataItemPO){
                    if($dataItemPO->price){
                        $qty = str_replace(['.', ','], ['', '.'], $request->qty);
                        $price = $dataItemPO->price;
                        $discount = isset($dataItemPO->discount) ? $dataItemPO->discount : 0;
                        $tax_rate = isset($dataItemPO->tax_rate) ? $dataItemPO->tax_rate : 0;
                        $sub_total = round(($qty * $price), 6);
                        $amount = round(($sub_total - $discount), 6);
                        $tax_value = round((($tax_rate/100) * $amount), 6);
                        $total_amount = round(($amount + $tax_value), 6);

                        PurchaseOrderDetails::where('id_purchase_requisition_details', $id)->update([
                            'sub_total' => $sub_total,
                            'amount' => $amount,
                            'tax_value' => $tax_value,
                            'total_amount' => $total_amount
                        ]);
                        $totals = PurchaseOrderDetails::where('id_purchase_orders', $dataItemPO->id_purchase_orders)
                            ->selectRaw('SUM(sub_total) as total_sub_total, SUM(discount) as total_discount, SUM(amount) as total_sub_amount,
                                SUM(tax_value) as total_ppn, SUM(total_amount) as total_amount')
                            ->first();
                        // Round up to 3 decimal places
                        $sub_total = round($totals->total_sub_total, 6);
                        $total_discount = round($totals->total_discount, 6);
                        $total_sub_amount = round($totals->total_sub_amount, 6);
                        $total_ppn = round($totals->total_ppn, 6);
                        $total_amount = round($totals->total_amount, 6);
                        // Update PO Data
                        PurchaseOrders::where('id', $dataItemPO->id_purchase_orders)->update([
                            'sub_total' => $sub_total,
                            'total_discount' => $total_discount,
                            'total_sub_amount' => $total_sub_amount,
                            'total_ppn' => $total_ppn,
                            'total_amount' => $total_amount,
                        ]);
                    }
                }

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
        $idPR = PurchaseRequisitionsDetail::where('id', $id)->first()->id_purchase_requisitions;
        $dataPR = PurchaseRequisitions::where('id', $idPR)->first();

        DB::beginTransaction();
        try{
            // Delete Item PR
            PurchaseRequisitionsDetail::where('id', $id)->delete();
            // Delete Item PO Also IF Has Created PO
            PurchaseOrderDetails::where('id_purchase_requisition_details', $id)->delete();

            // IF Input Price Re-Calculate Total Price
            if ($dataPR->input_price == 'Y') {
                $totals = PurchaseRequisitionsDetail::where('id_purchase_requisitions', $dataPR->id)
                    ->selectRaw('SUM(sub_total) as total_sub_total, SUM(discount) as total_discount, SUM(amount) as total_sub_amount,
                        SUM(tax_value) as total_ppn, SUM(total_amount) as total_amount')
                    ->first();
                // Round up to 6 decimal places
                $sub_total = round($totals->total_sub_total, 6);
                $total_discount = round($totals->total_discount, 6);
                $total_sub_amount = round($totals->total_sub_amount, 6);
                $total_ppn = round($totals->total_ppn, 6);
                $total_amount = round($totals->total_amount, 6);
                // Update PR Data
                PurchaseRequisitions::where('id', $dataPR->id)->update([
                    'sub_total' => $sub_total,
                    'total_discount' => $total_discount,
                    'total_sub_amount' => $total_sub_amount,
                    'total_ppn' => $total_ppn,
                    'total_amount' => $total_amount,
                ]);
            }

            // IF Has Created PO Re-Calculate Total Price In PO
            $dataItemPO = PurchaseOrderDetails::where('id_purchase_requisition_details', $id)->first();
            if($dataItemPO){
                $totals = PurchaseOrderDetails::where('id_purchase_orders', $dataItemPO->id_purchase_orders)
                    ->selectRaw('SUM(sub_total) as total_sub_total, SUM(discount) as total_discount, SUM(amount) as total_sub_amount,
                        SUM(tax_value) as total_ppn, SUM(total_amount) as total_amount')
                    ->first();
                // Round up to 6 decimal places
                $sub_total = round($totals->total_sub_total, 6);
                $total_discount = round($totals->total_discount, 6);
                $total_sub_amount = round($totals->total_sub_amount, 6);
                $total_ppn = round($totals->total_ppn, 6);
                $total_amount = round($totals->total_amount, 6);
                // Update PO Data
                PurchaseOrders::where('id', $dataItemPO->id_purchase_orders)->update([
                    'sub_total' => $sub_total,
                    'total_discount' => $total_discount,
                    'total_sub_amount' => $total_sub_amount,
                    'total_ppn' => $total_ppn,
                    'total_amount' => $total_amount,
                ]);
            }

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
        $datas = PurchaseRequisitionsDetail::select('purchase_requisition_details.*', 'purchase_requisitions.request_number', 'purchase_orders.po_number', 'master_suppliers.name as supplier_name',
                'purchase_requisitions.date',
                'purchase_orders.po_number', 'purchase_orders.delivery_date',
                'master_units.unit', 'master_units.unit_code',
                'purchase_order_details.currency as currencyPO', 'purchase_order_details.price as pricePO', 'purchase_order_details.discount as discountPO', 'purchase_order_details.amount as amountPO',
                DB::raw('
                CASE 
                    WHEN purchase_requisition_details.type_product = "RM" THEN master_raw_materials.description 
                    WHEN purchase_requisition_details.type_product = "WIP" THEN master_wips.description 
                    WHEN purchase_requisition_details.type_product = "FG" THEN master_product_fgs.description 
                    WHEN purchase_requisition_details.type_product IN ("TA", "Other") THEN master_tool_auxiliaries.description 
                END as product_desc'))
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
                        ->whereIn('purchase_requisition_details.type_product', ['TA', 'Other']);
            })
            ->leftjoin('purchase_requisitions', 'purchase_requisition_details.id_purchase_requisitions', 'purchase_requisitions.id')
            ->leftJoin('master_units', 'purchase_requisition_details.master_units_id', '=', 'master_units.id')
            ->leftjoin('master_suppliers', 'purchase_requisitions.id_master_suppliers', 'master_suppliers.id')
            ->leftjoin('purchase_orders', 'purchase_requisitions.id', 'purchase_orders.reference_number')
            ->leftjoin('purchase_order_details', 'purchase_requisition_details.id', 'purchase_order_details.id_purchase_requisition_details');

            if ($request->has('filterType') && $request->filterType != '' && $request->filterType != 'All') {
                $datas->where('purchase_requisition_details.type_product', $request->filterType);
            }
            if ($request->has('filterStatus') && $request->filterStatus != '' && $request->filterStatus != 'All') {
                $datas->where('purchase_requisition_details.status', 'LIKE', '%' . $request->filterStatus . '%');
            }
    
            $datas = $datas->orderBy('purchase_requisition_details.created_at', 'desc')->get();

        // Datatables
        if ($request->ajax()) {
            return DataTables::of($datas)->make(true);
        }

        //Audit Log
        $this->auditLogsShort('View List Purchase Requisition Items');
        return view('purchase-requisition-detail.index');
    }
    public function exportItemPR(Request $request)
    {
        // dd($request->all());

        $datas = PurchaseRequisitionsDetail::select('purchase_requisition_details.*', 'purchase_requisitions.request_number', 'purchase_orders.po_number', 'master_suppliers.name as supplier_name',
                'purchase_requisitions.date',
                'purchase_orders.po_number', 'purchase_orders.delivery_date',
                'master_units.unit', 'master_units.unit_code',
                'purchase_order_details.currency as currencyPO', 'purchase_order_details.price as pricePO', 'purchase_order_details.sub_total as sub_totalPO', 
                'purchase_order_details.discount as discountPO', 'purchase_order_details.amount as amountPO',
                'purchase_order_details.tax_rate as tax_ratePO', 'purchase_order_details.tax_value as tax_valuePO', 'purchase_order_details.total_amount as total_amountPO',
                'purchase_requisition_details.created_at as createdItem', 'purchase_requisition_details.updated_at as updatedItem', 
                DB::raw('
                CASE 
                    WHEN purchase_requisition_details.type_product = "RM" THEN master_raw_materials.description 
                    WHEN purchase_requisition_details.type_product = "WIP" THEN master_wips.description 
                    WHEN purchase_requisition_details.type_product = "FG" THEN master_product_fgs.description 
                    WHEN purchase_requisition_details.type_product IN ("TA", "Other") THEN master_tool_auxiliaries.description 
                END as product_desc'))
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
                        ->whereIn('purchase_requisition_details.type_product', ['TA', 'Other']);
            })
            ->leftjoin('purchase_requisitions', 'purchase_requisition_details.id_purchase_requisitions', 'purchase_requisitions.id')
            ->leftJoin('master_units', 'purchase_requisition_details.master_units_id', '=', 'master_units.id')
            ->leftjoin('master_suppliers', 'purchase_requisitions.id_master_suppliers', 'master_suppliers.id')
            ->leftjoin('purchase_orders', 'purchase_requisitions.id', 'purchase_orders.reference_number')
            ->leftjoin('purchase_order_details', 'purchase_requisition_details.id', 'purchase_order_details.id_purchase_requisition_details')
            ->orderBy('purchase_requisitions.created_at');

            if ($request->has('typeItem') && $request->typeItem != '' && $request->typeItem != 'Semua Type') {
                $datas->where('purchase_requisition_details.type_product', $request->typeItem);
            }
            if ($request->has('status') && $request->status != '' && $request->status != 'Semua Status') {
                $datas->where('purchase_requisition_details.status', 'LIKE', '%' . $request->status . '%');
            }
            if($request->has('dateFrom') && $request->dateFrom != '' && $request->has('dateTo') && $request->dateTo != ''){
                $datas->whereBetween('purchase_requisitions.date', [$request->dateFrom, $request->dateTo]);
            }

        $filename = 'Export_PR_Item_' . Carbon::now()->format('d_m_Y_H_i') . '.xlsx';
        return Excel::download(new PurchaseRequisitionsItemExport($datas->get(), $request), $filename);
    }
    public function indexItemPROld(Request $request)
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

    private function previewPONumber()
    {
        $year = date('y');
        $month = date('n');
        $lastCode = PurchaseOrders::whereYear('created_at', date('Y'))
            ->whereMonth('created_at', $month)
            ->orderBy('created_at', 'desc')
            ->value(DB::raw('LEFT(po_number, 3)'));
        $lastCode = $lastCode ? (int)$lastCode : 0;
        $nextCode = $lastCode + 1;
        $currentMonth = $this->romanMonth($month);

        return sprintf('%03d/PO/OTP/%s/%02d', $nextCode, $currentMonth, $year);
    }

    private function generatePONumber()
    {
        $year = date('Y');
        $month = date('n');
        return DB::transaction(function () use ($year, $month) {
            $lastCode = PurchaseOrders::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->lockForUpdate()
                ->orderBy('created_at', 'desc')
                ->value(DB::raw('LEFT(po_number, 3)'));
            $lastCode = $lastCode ? (int)$lastCode : 0;
            $nextCode = $lastCode + 1;
            $currentMonth = $this->romanMonth($month);
            return sprintf('%03d/PO/OTP/%s/%02d', $nextCode, $currentMonth, date('y'));
        });
    }

    public function indexPO(Request $request)
    {
        $po_number = $request->po_number;
        $formattedCode = $this->previewPONumber();

        $idUpdated = $request->get('idUpdated');

        $postedPRs = PurchaseRequisitions::select('id', 'request_number')->where('status', 'Posted')->where('input_price', '!=', 'Y')->get();
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

        // Get Page Number
        $page_number = 1;
        if ($idUpdated) {
            $page_size = 5;
            $item = $datas->firstWhere('id', $idUpdated);
            if ($item) {
                $index = $datas->search(function ($value) use ($idUpdated) {
                    return $value->id == $idUpdated;
                });
                $page_number = (int) ceil(($index + 1) / $page_size);
            } else {
                $page_number = 1;
            }
        }
        

        // Datatables
        if ($request->ajax()) {
            return DataTables::of($datas)
                ->addColumn('action', function ($data){
                    return view('purchase-order.action', compact('data'));
                })->make(true);
        }

        //Audit Log
        $this->auditLogsShort('View List Purchase Order');
        return view('purchase-order.index', compact('formattedCode', 'postedPRs', 'suppliers',
            'idUpdated', 'page_number', 'po_number'));
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
        
        DB::beginTransaction();
        try{
            $formattedCode = $this->generatePONumber();
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
                    'outstanding_qty' => $item->outstanding_qty,
                    'master_units_id' => $item->master_units_id,
                    'note' => $item->remarks,
                    'id_purchase_requisition_details' => $item->id,
                ]);
            }

            // Audit Log
            $this->auditLogsShort('Tambah Purchase Order ID : ('.$storeData->id.')');
            DB::commit();
            return redirect()->route('po.edit', encrypt($storeData->id))->with(['success' => 'Berhasil Tambah Data PO, Silahkan Update Harga Item Produk']);
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
        $statusPR = PurchaseRequisitions::where('id', $data->reference_number)->first()->status;

        // Dropdown
        $reference_number = PurchaseRequisitions::select('id', 'request_number')->where('status', 'Posted')->where('input_price', '!=', 'Y')->orWhere('id', $data->reference_number)->get();
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
                     ->whereIn('purchase_order_details.type_product', ['TA', 'Other']);
            })
            ->leftJoin('master_units', 'purchase_order_details.master_units_id', '=', 'master_units.id')
            ->where('purchase_order_details.id_purchase_orders', $id)
            ->orderBy('purchase_order_details.created_at')
            ->get();

        //Audit Log
        $this->auditLogsShort('View Edit Purchase Order ID : (' . $id . ')');

        return view('purchase-order.edit', compact(
            'reference_number',
            'statusPR',
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
                            'note' => $item->remarks,
                            'id_purchase_requisition_details' => $item->id,
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

        //Check IF PR Still in UnPost
        $idPR = PurchaseOrders::where('id', $id)->first()->reference_number;
        $status = PurchaseRequisitions::where('id', $idPR)->first()->status;
        if($status != 'Created PO'){
            return redirect()->back()->with(['fail' => 'Gagal Posted Data PO!, Purchase Requisition Belum Di Posted']);
        }

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
            PurchaseRequisitions::where('id', $idPR)->update(['status' => 'Closed']);
            PurchaseOrders::where('id', $id)->update(['status' => 'Posted']);

            // Audit Log
            $this->auditLogsShort('Posted Purchase Orders ('.$id.')');
            DB::commit();
            return redirect()->route('po.index', ['idUpdated' => $id])->with(['success' => 'Berhasil Posted Data PO']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->route('po.index', ['idUpdated' => $id])->with(['fail' => 'Gagal Posted Data PO!']);
        }
    }
    public function unpostedPO($id)
    {
        $id = decrypt($id);
        $idPR = PurchaseOrders::where('id', $id)->first()->reference_number;
        DB::beginTransaction();
        try{
            PurchaseRequisitions::where('id', $idPR)->update(['status' => 'Created PO']);
            PurchaseOrders::where('id', $id)->update(['status' => 'Un Posted']);

            // Audit Log
            $this->auditLogsShort('Un-Posted Purchase Order ('.$id.')');
            DB::commit();
            return redirect()->route('po.index', ['idUpdated' => $id])->with(['success' => 'Berhasil Un-Posted Data PO']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->route('po.index', ['idUpdated' => $id])->with(['fail' => 'Gagal Un-Posted Data PO!']);
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
                     ->whereIn('purchase_order_details.type_product', ['TA', 'Other']);
            })
            ->leftJoin('master_units', 'purchase_order_details.master_units_id', '=', 'master_units.id')
            ->where('purchase_order_details.id_purchase_orders', $id)
            ->orderBy('purchase_order_details.created_at')
            ->get();

        $view = ($lang === 'en') ? 'purchase-order.print' : 'purchase-order.printIDN';
        return view($view, compact('data', 'itemDatas'));
    }
    public function exportPO(Request $request)
    {
        // dd($request->all());

        $datas = PurchaseOrderDetails::select(
            'purchase_orders.id', 'purchase_orders.po_number', 'purchase_orders.date as po_date', 'purchase_orders.delivery_date', 'purchase_requisitions.request_number', 'master_suppliers.name as supplier_name',
            'purchase_orders.qc_check', 'purchase_orders.own_remarks', 'purchase_orders.supplier_remarks', 'purchase_requisitions.type', 'purchase_orders.down_payment', 'purchase_orders.total_amount as total_amountPO', 'purchase_orders.status as statusPO',
            'purchase_orders.created_at as createdPO', 'purchase_orders.updated_at as updatedPO', 
            DB::raw('
                CASE 
                    WHEN purchase_order_details.type_product = "RM" THEN master_raw_materials.description 
                    WHEN purchase_order_details.type_product = "WIP" THEN master_wips.description 
                    WHEN purchase_order_details.type_product = "FG" THEN master_product_fgs.description 
                    WHEN purchase_order_details.type_product IN ("TA", "Other") THEN master_tool_auxiliaries.description 
                END as product_desc'),
            'purchase_order_details.qty', 'purchase_order_details.cancel_qty', 'purchase_order_details.outstanding_qty',
            'master_units.unit', 'master_units.unit_code',
            'purchase_order_details.note', 'purchase_order_details.status',
            'purchase_order_details.created_at as createdItem', 'purchase_order_details.updated_at as updatedItem', 
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
                    ->whereIn('purchase_order_details.type_product', ['TA', 'Other']);
            })
            ->leftJoin('purchase_orders', 'purchase_order_details.id_purchase_orders', 'purchase_orders.id')
            ->leftJoin('purchase_requisitions', 'purchase_orders.reference_number', 'purchase_requisitions.id')
            ->leftJoin('master_units', 'purchase_order_details.master_units_id', 'master_units.id')
            ->leftJoin('master_suppliers', 'purchase_orders.id_master_suppliers', 'master_suppliers.id')
            ->orderBy('purchase_requisitions.created_at');

            if ($request->has('typeItem') && $request->typeItem != '' && $request->typeItem != 'Semua Type') {
                $datas->where('purchase_orders.type', $request->typeItem);
            }
            if ($request->has('status') && $request->status != '' && $request->status != 'Semua Status') {
                $datas->where('purchase_orders.status', $request->status);
            }
            if($request->has('dateFrom') && $request->dateFrom != '' && $request->has('dateTo') && $request->dateTo != ''){
                $datas->whereBetween('purchase_orders.date', [$request->dateFrom, $request->dateTo]);
            }

        $filename = 'Export_PO_' . Carbon::now()->format('d_m_Y_H_i') . '.xlsx';
        return Excel::download(new PurchaseOrdersExport($datas->get(), $request), $filename);
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
            // Round up to 6 decimal places
            $sub_total = round($totals->total_sub_total, 6);
            $total_discount = round($totals->total_discount, 6);
            $total_sub_amount = round($totals->total_sub_amount, 6);
            $total_ppn = round($totals->total_ppn, 6);
            $total_amount = round($totals->total_amount, 6);
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
        // $dataBefore->master_products_id = $request->master_products_id;
        // $dataBefore->qty = $request->qty;
        // $dataBefore->master_units_id = $request->master_units_id;
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
                    // 'qty' => $request->qty,
                    // 'master_units_id' => $request->master_units_id,
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
                // Round up to 6 decimal places
                $sub_total = round($totals->total_sub_total, 6);
                $total_discount = round($totals->total_discount, 6);
                $total_sub_amount = round($totals->total_sub_amount, 6);
                $total_ppn = round($totals->total_ppn, 6);
                $total_amount = round($totals->total_amount, 6);
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
            // Round up to 6 decimal places
            $sub_total = round($totals->total_sub_total, 6);
            $total_discount = round($totals->total_discount, 6);
            $total_sub_amount = round($totals->total_sub_amount, 6);
            $total_ppn = round($totals->total_ppn, 6);
            $total_amount = round($totals->total_amount, 6);
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
    public function cancelQtyItemPO(Request $request, $id)
    {
        $id = decrypt($id);

        $request->validate([
            'cancel_qty' => 'required',
        ], [
            'cancel_qty.required' => 'Cancel Qty harus diisi.',
        ]);
        $dataBefore = PurchaseOrderDetails::where('id', $id)->first();
        $idPO = $dataBefore->id_purchase_orders;
        //Check GRN Still In Progress Or Not
        if(GoodReceiptNote::where('id_purchase_orders', $idPO)->whereIn('status', ['Hold', 'Un Posted'])->exists()){
            return redirect()->back()->with(['fail' => 'Gagal Cancel Item, Good Receipt Note Masih Dalam Proses']);
        }
        $originOutstandingQty = (float) $dataBefore->outstanding_qty + (float) $dataBefore->cancel_qty;
        // Check Cancel Qty Cannot More Than Outstanding Qty
        if($request->cancel_qty > $originOutstandingQty){
            return redirect()->back()->with(['fail' => 'Gagal, Cancel Qty Tidak Boleh Melebihi Outstanding Qty']);
        }
        $requestOutstandingQty = (float) $originOutstandingQty - str_replace(['.', ','], ['', '.'], $request->cancel_qty);
        $newStatus = ($requestOutstandingQty == 0) ? 'Close' : 'Open';
        $dataBefore->cancel_qty = str_replace(['.', ','], ['', '.'], $request->cancel_qty);

        if($dataBefore->isDirty()){
            DB::beginTransaction();
            try{
                PurchaseOrderDetails::where('id', $id)->update([
                    'cancel_qty' => str_replace(['.', ','], ['', '.'], $request->cancel_qty),
                    'outstanding_qty' => $requestOutstandingQty,
                    'status' => $newStatus,
                ]);
                PurchaseRequisitionsDetail::where('id', $dataBefore->id_purchase_requisition_details)->update([
                    'cancel_qty' => str_replace(['.', ','], ['', '.'], $request->cancel_qty),
                    'outstanding_qty' => $requestOutstandingQty,
                    'status' => $newStatus,
                ]);
                $product = PurchaseOrderDetails::where('id_purchase_orders', $idPO)->get();
                //Check Status Item
                $hasOpenStatus = $product->contains('status', 'Open');
                if(!$hasOpenStatus){
                    PurchaseOrders::where('id', $idPO)->update(['status' => 'Closed']);
                }

                // Audit Log
                $this->auditLogsShort('Cancel PO Item ID : (' . $id . ')');
                DB::commit();
                return redirect()->back()->with(['success' => 'Berhasil Cancel Item PO', 'scrollTo' => 'tableItem']);
            } catch (Exception $e) {
                DB::rollback();
                return redirect()->back()->with(['fail' => 'Gagal Cancel Item PO!']);
            }
        } else {
            return redirect()->back()->with(['info' => 'Tidak Ada Yang Dirubah, Data Sama Dengan Sebelumnya']);
        }

    }
}
