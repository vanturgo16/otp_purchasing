<?php

namespace App\Http\Controllers;

use DataTables;
use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\PurchaseRequisitionsPriceExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

// Model
use App\Models\MstRawMaterial;
use App\Models\MstCurrencies;
use App\Models\MstProductFG;
use App\Models\PurchaseRequisitions;
use App\Models\MstRequester;
use App\Models\MstSupplier;
use App\Models\MstToolAux;
use App\Models\MstUnits;
use App\Models\MstWip;
use App\Models\PurchaseOrders;
use App\Models\PurchaseRequisitionsDetail;
use App\Models\PurchaseRequisitionsPrice;
use App\Models\GoodReceiptNote;

class PurchaseRequisitionPriceController extends Controller
{
    use AuditLogsTrait;
    
    public function getDataPRGRN()
    {
        $datas = GoodReceiptNote::select('good_receipt_notes.receipt_number', 'good_receipt_notes.status',
                'purchase_requisitions.request_number', 'good_receipt_notes.created_at as createdGRN')
            ->leftJoin('purchase_requisitions', 'good_receipt_notes.reference_number', 'purchase_requisitions.id')
            ->whereNull('good_receipt_notes.id_purchase_orders')
            ->orderBy('good_receipt_notes.created_at', 'desc')
            ->get();
        return $datas;
    }

    //DATA PR PRICE
    public function index(Request $request)
    {
        $idPRCreatedPO = PurchaseOrders::pluck('reference_number')->toArray();
        $postedPRs = PurchaseRequisitions::whereNotIn('id', $idPRCreatedPO)
            ->whereIn('status', ['Created GRN', 'Closed'])
            ->where('input_price', '!=', 'Y')
            ->get(['id', 'request_number']);
        $suppliers = MstSupplier::get();

        $idUpdated = $request->get('idUpdated');

        $datas = PurchaseRequisitionsPrice::select('purchase_requisitions_price.id', 'purchase_requisitions_price.id_purchase_requisitions', 'purchase_requisitions_price.status', 
                'purchase_requisitions.request_number', 'purchase_requisitions.date as requisition_date', 'purchase_requisitions.qc_check', 'purchase_requisitions.note', 'purchase_requisitions.type',
                'master_suppliers.name as supplier_name', 'master_requester.nm_requester', 'purchase_orders.po_number',
                \DB::raw('(SELECT COUNT(*) FROM purchase_requisition_details WHERE purchase_requisition_details.id_purchase_requisitions = purchase_requisitions.id) as count'))
            ->leftjoin('purchase_requisitions', 'purchase_requisitions_price.id_purchase_requisitions', 'purchase_requisitions.id')
            ->leftjoin('master_suppliers', 'purchase_requisitions.id_master_suppliers', 'master_suppliers.id')
            ->leftjoin('master_requester', 'purchase_requisitions.requester', 'master_requester.id')
            ->leftjoin('purchase_orders', 'purchase_requisitions.id', 'purchase_orders.reference_number');

        if ($request->has('filterType') && $request->filterType != '' && $request->filterType != 'All') {
            $datas->where('purchase_requisitions.type', $request->filterType);
        }
        if ($request->has('filterStatus') && $request->filterStatus != '' && $request->filterStatus != 'All') {
            $datas->where('purchase_requisitions.status', $request->filterStatus);
        }

        $datas = $datas->orderBy('purchase_requisitions_price.created_at', 'desc')->get();

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
                    return view('purchase_requisition_price.action', compact('data'));
                })->make(true);
        }

        //Audit Log
        $this->auditLogsShort('View List Purchase Requisition With Price');
        return view('purchase_requisition_price.index', compact('postedPRs', 'suppliers',
            'idUpdated', 'page_number'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'reference_number' => 'required',
            'id_master_suppliers' => 'required',
            'requester' => 'required',
            'qc_check' => 'required',
        ], [
            'request_number.required' => 'Request Number masih kosong.',
            'id_master_suppliers.required' => 'Supplier masih kosong.',
            'requester.required' => 'Requester masih kosong.',
            'qc_check.required' => 'QC Check masih kosong.',
        ]);

        // Check GRN Has Finished or Still Progress
        if(GoodReceiptNote::where('reference_number', $request->reference_number)->whereIn('status', ['Hold', 'Un Posted'])->exists()){
            return redirect()->back()->with(['fail' => 'Gagal Tambah Data, Good Receipt Note Masih Dalam Proses']);
        }
        
        DB::beginTransaction();
        try{
            $storeData = PurchaseRequisitionsPrice::create([
                'id_purchase_requisitions' => $request->reference_number,
                'status' => 'Request',
            ]);
            PurchaseRequisitions::where('id', $request->reference_number)->update(['input_price' => 'Y']);

            // Audit Log
            $this->auditLogsShort('Tambah Purchase Requisitions Price ID ('.$storeData->id.')');
            DB::commit();
            return redirect()->route('pr.price.edit', encrypt($storeData->id))->with(['success' => 'Berhasil Tambah Data, Silahkan Update Harga Pada Item Produk']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Tambah Data!']);
        }
    }
    public function edit($id)
    {
        $id = decrypt($id);
        $prPrice = PurchaseRequisitionsPrice::where('id', $id)->first();
        $data = PurchaseRequisitions::select('purchase_requisitions.*', 'master_suppliers.name as supplier_name', 'master_requester.nm_requester')
            ->leftjoin('master_suppliers', 'purchase_requisitions.id_master_suppliers', 'master_suppliers.id')
            ->leftjoin('master_requester', 'purchase_requisitions.requester', 'master_requester.id')
            ->where('purchase_requisitions.id', $prPrice->id_purchase_requisitions)
            ->first();

        $idPRCreatedPO = PurchaseOrders::pluck('reference_number')->toArray();
        $reference_number = PurchaseRequisitions::whereNotIn('id', $idPRCreatedPO)
            ->whereIn('status', ['Created GRN', 'Closed'])
            ->where('input_price', '!=', 'Y')
            ->orWhere('id', $data->id)
            ->get(['id', 'request_number']);

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
            
        return view('purchase_requisition_price.edit', compact('prPrice', 'data', 'reference_number', 'products', 'suppliers', 'units', 'requesters', 'currency', 'itemDatas'));
    }
    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $request->validate([
            'reference_number_before' => 'required',
            'reference_number' => 'required',
            'id_master_suppliers' => 'required',
            'requester' => 'required',
            'qc_check' => 'required',
        ], [
            'request_number.required' => 'Request Number masih kosong.',
            'id_master_suppliers.required' => 'Supplier masih kosong.',
            'requester.required' => 'Requester masih kosong.',
            'qc_check.required' => 'QC Check masih kosong.',
        ]);

        DB::beginTransaction();
        try{
            // Rollback Data PR Before
            PurchaseRequisitions::where('id', $request->reference_number_before)->update([
                'input_price' => 'N', 'sub_total' => null, 'total_discount' => null, 
                'total_sub_amount' => null, 'total_ppn' => null, 'total_amount' => null,
            ]);
            PurchaseRequisitionsDetail::where('id_purchase_requisitions', $request->reference_number_before)->update([
                'currency' => null, 'price' => null, 'sub_total' => null, 'discount' => null, 'amount' => null,
                'tax' => null, 'tax_rate' => null, 'tax_value' => null, 'total_amount' => null,
            ]);
            // Update Data PR Baru
            PurchaseRequisitions::where('id', $request->reference_number)->update([ 'input_price' => 'Y' ]);
            PurchaseRequisitionsPrice::where('id', $id)->update(['id_purchase_requisitions' => $request->reference_number]);

            // Audit Log
            $this->auditLogsShort('Update Purchase Requisitions Price ID : (' . $id . ')');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Update Data PR Price']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Update Data PR Price!']);
        }
    }
    public function delete($id)
    {
        $id = decrypt($id);
        DB::beginTransaction();
        try{
            // Rollback Data PR Before
            $idPR = PurchaseRequisitionsPrice::where('id', $id)->first()->id_purchase_requisitions;
            PurchaseRequisitions::where('id', $idPR)->update([
                'input_price' => 'N', 'sub_total' => null, 'total_discount' => null, 
                'total_sub_amount' => null, 'total_ppn' => null, 'total_amount' => null,
            ]);
            PurchaseRequisitionsDetail::where('id_purchase_requisitions', $idPR)->update([
                'currency' => null, 'price' => null, 'sub_total' => null, 'discount' => null, 'amount' => null,
                'tax' => null, 'tax_rate' => null, 'tax_value' => null, 'total_amount' => null,
            ]);

            PurchaseRequisitionsPrice::where('id', $id)->delete();

            // Audit Log
            $this->auditLogsShort('Hapus Purchase Requisitions');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Hapus Data PR']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Hapus Data PR!']);
        }
    }
    public function posted($id)
    {
        $id = decrypt($id);

        $idPR = PurchaseRequisitionsPrice::where('id', $id)->first()->id_purchase_requisitions;
        //Check IF PR Still in UnPost
        $status = PurchaseRequisitions::where('id', $idPR)->first()->status;
        if(!in_array($status, ['Created GRN', 'Closed'])){
            return redirect()->back()->with(['fail' => 'Gagal Posted Data PR Price!, Purchase Requisition '. $status]);
        }
        //Check Set Price In Product Or Not
        $product = PurchaseRequisitionsDetail::where('id_purchase_requisitions', $idPR)->get();
        $hasNullPrice = $product->contains(function ($order) {
            return is_null($order->price);
        });
        if ($hasNullPrice) {
            return redirect()->back()->with(['fail' => 'Gagal Posted Data PR Price!, Masih Ada Produk dalam PR yang Belum Memiliki Harga']);
        }
        //Check Status Item
        $hasOpenStatus = $product->contains('status', 'Open');

        DB::beginTransaction();
        try{
            PurchaseRequisitionsPrice::where('id', $id)->update(['status' => 'Posted']);
            if(!$hasOpenStatus){
                PurchaseRequisitions::where('id', $idPR)->update(['status' => 'Closed']);
            }

            // Audit Log
            $this->auditLogsShort('Posted Purchase Requisitions Price ID ('.$id.')');
            DB::commit();
            return redirect()->route('pr.price.index', ['idUpdated' => $id])->with(['success' => 'Berhasil Posted Data PR Price']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->route('pr.price.index', ['idUpdated' => $id])->with(['fail' => 'Gagal Posted Data PR Price!']);
        }
    }
    public function unposted($id)
    {
        $id = decrypt($id);
        $idPR = PurchaseRequisitionsPrice::where('id', $id)->first()->id_purchase_requisitions;
        $isClosedPR = PurchaseRequisitions::where('id', $idPR)->first()->status;

        DB::beginTransaction();
        try{
            PurchaseRequisitionsPrice::where('id', $id)->update(['status' => 'Un Posted']);
            if($isClosedPR == 'Closed'){
                PurchaseRequisitions::where('id', $idPR)->update(['status' => 'Created GRN']);
            }

            // Audit Log
            $this->auditLogsShort('Un-Posted Purchase Requisitions Price ID ('.$id.')');
            DB::commit();
            return redirect()->route('pr.price.index', ['idUpdated' => $id])->with(['success' => 'Berhasil Un-Posted Data PR Price']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->route('pr.price.index', ['idUpdated' => $id])->with(['fail' => 'Gagal Un-Posted Data PR Price!']);
        }
    }
    public function getPRDetails(Request $request)
    {
        $referenceId = $request->input('reference_id');
        $purchaseRequest = PurchaseRequisitions::select('id_master_suppliers', 'qc_check', 'type')->where('id', $referenceId)->first();

        $purchaseRequest = PurchaseRequisitions::select('purchase_requisitions.date', 'purchase_requisitions.qc_check', 'purchase_requisitions.note', 'master_suppliers.name as supplier_name', 'master_requester.nm_requester')
            ->leftjoin('master_suppliers', 'purchase_requisitions.id_master_suppliers', 'master_suppliers.id')
            ->leftjoin('master_requester', 'purchase_requisitions.requester', 'master_requester.id')
            ->where('purchase_requisitions.id', $referenceId)
            ->first();


        if ($purchaseRequest) {
            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $purchaseRequest->date,
                    'supplier_name' => $purchaseRequest->supplier_name,
                    'nm_requester' => $purchaseRequest->nm_requester,
                    'qc_check' => $purchaseRequest->qc_check,
                    'note' => $purchaseRequest->note,
                ]
            ]);
        } else {
            return response()->json(['success' => false]);
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

        $view = ($lang === 'en') ? 'purchase_requisition_price.print' : 'purchase_requisition_price.printIDN';
        return view($view, compact('data', 'itemDatas'));
    }
    public function exportPR(Request $request)
    {
        // dd($request->all());

        $datas = PurchaseRequisitionsDetail::select(
            'purchase_requisitions.id', 'purchase_requisitions.request_number', 'purchase_requisitions.date as requisition_date', 'master_suppliers.name as supplier_name', 'requester.nm_requester as requester_name',
            'purchase_requisitions.qc_check', 'purchase_requisitions.note', 'purchase_requisitions.type', 
            'purchase_requisitions.total_amount as total_amountPR', 'purchase_requisitions.status as statusPR', 'purchase_requisitions_price.status as statusPRPrice',
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
            'purchase_requisition_details.currency', 'purchase_requisition_details.price', 'purchase_requisition_details.sub_total', 'purchase_requisition_details.discount',
            'purchase_requisition_details.amount', 'purchase_requisition_details.tax_rate', 'purchase_requisition_details.tax_value', 'purchase_requisition_details.total_amount',
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
            ->leftJoin('purchase_requisitions_price', 'purchase_requisitions.id', 'purchase_requisitions_price.id_purchase_requisitions')
            ->leftJoin('master_units', 'purchase_requisition_details.master_units_id', 'master_units.id')
            ->leftJoin('master_requester as requester', 'purchase_requisitions.requester', 'requester.id')
            ->leftJoin('master_suppliers', 'purchase_requisitions.id_master_suppliers', 'master_suppliers.id')
            ->leftJoin('master_requester as cc_co', 'purchase_requisition_details.cc_co', 'cc_co.id')
            ->where('purchase_requisitions.input_price', 'Y')
            ->orderBy('purchase_requisitions.created_at');

            if ($request->has('typeItem') && $request->typeItem != '' && $request->typeItem != 'Semua Type') {
                $datas->where('purchase_requisitions.type', $request->typeItem);
            }
            if ($request->has('status') && $request->status != '' && $request->status != 'Semua Status') {
                $datas->where('purchase_requisitions_price.status', $request->status);
            }
            if($request->has('dateFrom') && $request->dateFrom != '' && $request->has('dateTo') && $request->dateTo != ''){
                $datas->whereBetween('purchase_requisitions.date', [$request->dateFrom, $request->dateTo]);
            }

        $filename = 'Export_PR_With_Price_' . Carbon::now()->format('d_m_Y_H_i') . '.xlsx';
        return Excel::download(new PurchaseRequisitionsPriceExport($datas->get(), $request), $filename);
    }

    //ITEM PR PRICE
    public function editItem($id)
    {
        $id = decrypt($id);
        $data = PurchaseRequisitionsDetail::where('id', $id)->first();
        $idPRPrice = PurchaseRequisitionsPrice::where('id_purchase_requisitions', $data->id_purchase_requisitions)->first()->id;
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
        $currency = MstCurrencies::get();
            
        return view('purchase_requisition_price.item.edit', compact('data', 'idPRPrice', 'products', 'units', 'requesters', 'currency'));
    }
    public function updateItem(Request $request, $id)
    {
        $id = decrypt($id);
        $request->validate([
            'master_products_id' => 'required',
            'qty' => 'required',
            'master_units_id' => 'required',
            'currency' => 'required',
            'price' => 'required',
            'sub_total' => 'required',
            'discount' => 'required',
            'amount' => 'required',
        ], [
            'master_products_id.required' => 'Produk harus diisi.',
            'qty.required' => 'Qty harus diisi.',
            'master_units_id.required' => 'Unit harus diisi.',
            'currency.required' => 'Currency harus diisi.',
            'price.required' => 'Price harus diisi.',
            'sub_total.required' => 'Subtotal masih kosong.',
            'discount.required' => 'Diskon masih kosong.',
            'amount.required' => 'Jumlah harus diisi.',
        ]);

        $dataBefore = PurchaseRequisitionsDetail::where('id', $id)->first();

        $originOutstandingQty = (float) $dataBefore->outstanding_qty + (float) $dataBefore->cancel_qty;
        // Check Cancel Qty Cannot More Than Outstanding Qty
        if($request->cancel_qty > $originOutstandingQty){
            return redirect()->back()->with(['fail' => 'Gagal Update Item PR Price, Cancel Qty Tidak Boleh Melebihi Outstanding Qty']);
        }
        $requestOutstandingQty = (float) $originOutstandingQty - str_replace(['.', ','], ['', '.'], $request->cancel_qty);
        $newStatus = ($requestOutstandingQty == 0) ? 'Close' : 'Open';

        $idPRPrice = PurchaseRequisitionsPrice::where('id_purchase_requisitions', $dataBefore->id_purchase_requisitions)->first()->id;
        $dataBefore->cancel_qty = str_replace(['.', ','], ['', '.'], $request->cancel_qty);
        $dataBefore->outstanding_qty = str_replace(['.', ','], ['', '.'], $request->outstanding_qty);
        $dataBefore->currency = $request->currency;
        $dataBefore->price = str_replace(['.', ','], ['', '.'], $request->price);
        $dataBefore->sub_total = str_replace(['.', ','], ['', '.'], $request->sub_total);
        $dataBefore->discount = str_replace(['.', ','], ['', '.'], $request->discount);
        $dataBefore->amount = str_replace(['.', ','], ['', '.'], $request->amount);
        $dataBefore->tax = $request->tax;
        $dataBefore->tax_rate = $request->tax_rate;
        $dataBefore->tax_value = str_replace(['.', ','], ['', '.'], $request->tax_value);
        $dataBefore->total_amount = str_replace(['.', ','], ['', '.'], $request->total_amount);

        if($dataBefore->isDirty()){
            DB::beginTransaction();
            try{
                PurchaseRequisitionsDetail::where('id', $id)->update([
                    'cancel_qty' => str_replace(['.', ','], ['', '.'], $request->cancel_qty),
                    'outstanding_qty' => $requestOutstandingQty,
                    'currency' => $request->currency,
                    'price' => str_replace(['.', ','], ['', '.'], $request->price),
                    'sub_total' => str_replace(['.', ','], ['', '.'], $request->sub_total),
                    'discount' => str_replace(['.', ','], ['', '.'], $request->discount),
                    'amount' => str_replace(['.', ','], ['', '.'], $request->amount),
                    'tax' => $request->tax,
                    'tax_rate' => $request->tax_rate,
                    'tax_value' => str_replace(['.', ','], ['', '.'], $request->tax_value),
                    'total_amount' => str_replace(['.', ','], ['', '.'], $request->total_amount),
                    'status' => $newStatus,
                ]);
                $totals = PurchaseRequisitionsDetail::where('id_purchase_requisitions', $dataBefore->id_purchase_requisitions)
                    ->selectRaw('SUM(sub_total) as total_sub_total, SUM(discount) as total_discount, SUM(amount) as total_sub_amount,
                        SUM(tax_value) as total_ppn, SUM(total_amount) as total_amount')
                    ->first();
                // Round up to 3 decimal places
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

                // Audit Log
                $this->auditLogsShort('Update Purchase Requisitions Detail Price ID : (' . $id . ')');
                DB::commit();
                return redirect()->route('pr.price.edit', encrypt($idPRPrice))->with(['success' => 'Berhasil Update Item PR Price', 'scrollTo' => 'tableItem']);
            } catch (Exception $e) {
                DB::rollback();
                return redirect()->back()->with(['fail' => 'Gagal Update Item PR Price!']);
            }
        } else {
            return redirect()->back()->with(['info' => 'Tidak Ada Yang Dirubah, Data Sama Dengan Sebelumnya']);
        }
    }
}
