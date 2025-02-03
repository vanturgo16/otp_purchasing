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
use App\Models\MstRules;
use App\Models\MstSupplier;
use App\Models\MstToolAux;
use App\Models\MstUnits;
use App\Models\MstWip;
use App\Models\PurchaseRequisitionsDetail;
use App\Models\PurchaseOrderDetails;
use App\Models\PurchaseRequisitionsPrice;

class PurchaseRequisitionPriceController extends Controller
{
    use AuditLogsTrait;

    //DATA PR
    public function index(Request $request)
    {
        $postedPRs = PurchaseRequisitions::select('id', 'request_number')->where('status', 'Posted')->where('input_price', '!=', 'Y')->get();
        $suppliers = MstSupplier::get();

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

        $datas = $datas->orderBy('purchase_requisitions.created_at', 'desc')->get();

        // Datatables
        if ($request->ajax()) {
            return DataTables::of($datas)
                ->addColumn('action', function ($data){
                    return view('purchase_requisition_price.action', compact('data'));
                })->make(true);
        }

        //Audit Log
        $this->auditLogsShort('View List Purchase Requisition With Price');
        return view('purchase_requisition_price.index', compact('postedPRs', 'suppliers'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'reference_number' => 'required',
            'id_master_suppliers' => 'required',
            'qc_check' => 'required',
            'status' => 'required',
            'type' => 'required',
        ], [
            'request_number.required' => 'Request Number masih kosong.',
            'id_master_suppliers.required' => 'Supplier harus diisi.',
            'qc_check.required' => 'QC Check harus diisi.',
            'status.required' => 'Status harus diisi.',
            'type.required' => 'Type masih kosong.',
        ]);
        
        DB::beginTransaction();
        try{
            $storeData = PurchaseRequisitionsPrice::create([
                'id_purchase_requisitions' => $request->reference_number,
                'status' => $request->status,
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
        $reference_number = PurchaseRequisitions::select('id', 'request_number')->where('status', 'Posted')->where('input_price', '!=', 'Y')->orWhere('id', $data->id)->get();

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
        $dataBefore->input_price = $request->input_price;

        if($dataBefore->isDirty()){
            DB::beginTransaction();
            try{
                PurchaseRequisitions::where('id', $id)->update([
                    'date' => $request->date,
                    'id_master_suppliers' => $request->id_master_suppliers,
                    'requester' => $request->requester,
                    'qc_check' => $request->qc_check,
                    'note' => $request->note,
                    'input_price' => $request->input_price,
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
    public function delete($id)
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
    public function posted($id)
    {
        $id = decrypt($id);

        $inputPrice = PurchaseRequisitions::where('id', $id)->first()->input_price;
        if($inputPrice == 'Y'){
            //Check Set Price In Product Or Not
            $product = PurchaseRequisitionsDetail::where('id_purchase_requisitions', $id)->get();
            $hasNullPrice = $product->contains(function ($order) {
                return is_null($order->price);
            });
            if ($hasNullPrice) {
                return redirect()->back()->with(['fail' => 'Gagal Posted Data PR!, Masih Ada Produk dalam PR yang Belum Memiliki Harga (PR Input Price (Y))']);
            }
        }

        DB::beginTransaction();
        try{
            // Check PO Created Or NOT
            $status = PurchaseOrders::where('reference_number', $id)->exists() ? 'Created PO' : 'Posted';
            // Update Status PR
            PurchaseRequisitions::where('id', $id)->update(['status' => $status]);

            // Audit Log
            $this->auditLogsShort('Posted Purchase Requisitions ('.$id.')');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Posted Data PR']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Posted Data PR!']);
        }
    }
    public function unposted($id)
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
    public function print($lang, $id)
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
            'currency' => $request->input_price == 'Y' ? 'required' : '',
            'price' => $request->input_price == 'Y' ? 'required' : '',
            'sub_total' => $request->input_price == 'Y' ? 'required' : '',
            'discount' => $request->input_price == 'Y' ? 'required' : '',
            'amount' => $request->input_price == 'Y' ? 'required' : '',
        ], [
            'request_number.required' => 'Request Number masih kosong.',
            'type_product.required' => 'Type Produk masih kosong.',
            'master_products_id.required' => 'Produk harus diisi.',
            'qty.required' => 'Qty harus diisi.',
            'master_units_id.required' => 'Unit harus diisi.',
            'required_date.required' => 'Required Date harus diisi.',
            'cc_co.required' => 'CC / CO harus diisi.',
            'currency.required' => 'Currency harus diisi.',
            'price.required' => 'Price harus diisi.',
            'sub_total.required' => 'Subtotal masih kosong.',
            'discount.required' => 'Diskon masih kosong.',
            'amount.required' => 'Jumlah harus diisi.',
        ]);
        if ($request->input_price == 'Y') {
            $totalAmount = str_replace(['.', ','], ['', '.'], $request->total_amount);
            $limitPRPrice = MstRules::where('rule_name', 'Limit Price PR')->first()->rule_value;
            if($totalAmount > $limitPRPrice){
                return redirect()->back()->with(['fail' => 'Gagal Tambah Item PR!, Total Harga Produk Melebihi Limit Harga PR']);
            }
        }
        
        DB::beginTransaction();
        try{
            $storeData = [
                'id_purchase_requisitions' => $id,
                'type_product' => $request->type_product,
                'master_products_id' => $request->master_products_id,
                'qty' => $request->qty,
                'master_units_id' => $request->master_units_id,
                'required_date' => $request->required_date,
                'cc_co' => $request->cc_co,
                'remarks' => $request->remarks,
                'request_number' => $request->request_number,
            ];
            if ($request->input_price == 'Y') {
                $storeData = array_merge($storeData, [
                    'currency' => $request->currency,
                    'price' => str_replace(['.', ','], ['', '.'], $request->price),
                    'sub_total' => str_replace(['.', ','], ['', '.'], $request->sub_total),
                    'discount' => str_replace(['.', ','], ['', '.'], $request->discount),
                    'amount' => str_replace(['.', ','], ['', '.'], $request->amount),
                    'tax' => $request->tax,
                    'tax_rate' => $request->tax_rate,
                    'tax_value' => str_replace(['.', ','], ['', '.'], $request->tax_value),
                    'total_amount' => str_replace(['.', ','], ['', '.'], $request->total_amount),
                ]);
            }
            $storeData = PurchaseRequisitionsDetail::create($storeData);

            // ADD ITEM PRODUCT IN PO DETAIL ALSO IF PO IS ALREADY EXIST
            $dataPO = PurchaseOrders::where('reference_number', $id)->first();
            if($dataPO){
                $storeDataItemPO = [
                    'id_purchase_orders' => $dataPO->id,
                    'type_product' => $request->type_product,
                    'master_products_id' => $request->master_products_id,
                    'qty' => $request->qty,
                    'master_units_id' => $request->master_units_id,
                    'id_purchase_requisition_details' => $storeData->id,
                ];
                if ($request->input_price == 'Y') {
                    $storeDataItemPO = array_merge($storeDataItemPO, [
                        'currency' => $request->currency,
                        'price' => str_replace(['.', ','], ['', '.'], $request->price),
                        'sub_total' => str_replace(['.', ','], ['', '.'], $request->sub_total),
                        'discount' => str_replace(['.', ','], ['', '.'], $request->discount),
                        'amount' => str_replace(['.', ','], ['', '.'], $request->amount),
                        'tax' => $request->tax,
                        'tax_rate' => $request->tax_rate,
                        'tax_value' => str_replace(['.', ','], ['', '.'], $request->tax_value),
                        'total_amount' => str_replace(['.', ','], ['', '.'], $request->total_amount),
                    ]);
                }
                $storeDataItemPO = PurchaseOrderDetails::create($storeDataItemPO);
            }
            
            if ($request->input_price == 'Y') {
                $totals = PurchaseRequisitionsDetail::where('id_purchase_requisitions', $id)
                    ->selectRaw('SUM(sub_total) as total_sub_total, SUM(discount) as total_discount, SUM(amount) as total_sub_amount,
                        SUM(tax_value) as total_ppn, SUM(total_amount) as total_amount')
                    ->first();
                // Round up to 3 decimal places
                $sub_total = round($totals->total_sub_total, 3);
                $total_discount = round($totals->total_discount, 3);
                $total_sub_amount = round($totals->total_sub_amount, 3);
                $total_ppn = round($totals->total_ppn, 3);
                $total_amount = round($totals->total_amount, 3);
                // Update PR Data
                PurchaseRequisitions::where('id', $id)->update([
                    'sub_total' => $sub_total,
                    'total_discount' => $total_discount,
                    'total_sub_amount' => $total_sub_amount,
                    'total_ppn' => $total_ppn,
                    'total_amount' => $total_amount,
                ]);
                // Update PO Data If Exist
                PurchaseOrders::where('reference_number', $id)->update([
                    'sub_total' => $sub_total,
                    'total_discount' => $total_discount,
                    'total_sub_amount' => $total_sub_amount,
                    'total_ppn' => $total_ppn,
                    'total_amount' => $total_amount,
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
        $inputPrice = PurchaseRequisitions::Where('id', $data->id_purchase_requisitions)->first()->input_price;
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
            
        return view('purchase-requisition.item.edit', compact('data', 'inputPrice', 'products', 'units', 'requesters', 'currency'));
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
            'currency' => $request->input_price == 'Y' ? 'required' : '',
            'price' => $request->input_price == 'Y' ? 'required' : '',
            'sub_total' => $request->input_price == 'Y' ? 'required' : '',
            'discount' => $request->input_price == 'Y' ? 'required' : '',
            'amount' => $request->input_price == 'Y' ? 'required' : '',
        ], [
            'master_products_id.required' => 'Produk harus diisi.',
            'qty.required' => 'Qty harus diisi.',
            'master_units_id.required' => 'Unit harus diisi.',
            'required_date.required' => 'Required Date harus diisi.',
            'cc_co.required' => 'CC / CO harus diisi.',
            'currency.required' => 'Currency harus diisi.',
            'price.required' => 'Price harus diisi.',
            'sub_total.required' => 'Subtotal masih kosong.',
            'discount.required' => 'Diskon masih kosong.',
            'amount.required' => 'Jumlah harus diisi.',
        ]);

        if ($request->input_price == 'Y') {
            $totalAmount = str_replace(['.', ','], ['', '.'], $request->total_amount);
            $limitPRPrice = MstRules::where('rule_name', 'Limit Price PR')->first()->rule_value;
            if($totalAmount > $limitPRPrice){
                return redirect()->back()->with(['fail' => 'Gagal Tambah Item PR!, Total Harga Produk Melebihi Limit Harga PR']);
            }
        }

        $dataBefore = PurchaseRequisitionsDetail::where('id', $id)->first();
        $dataBefore->master_products_id = $request->master_products_id;
        $dataBefore->qty = $request->qty;
        $dataBefore->master_units_id = $request->master_units_id;
        $dataBefore->required_date = $request->required_date;
        $dataBefore->cc_co = $request->cc_co;
        $dataBefore->remarks = $request->remarks;
        if($request->input_price == 'Y'){
            $dataBefore->currency = $request->currency;
            $dataBefore->price = str_replace(['.', ','], ['', '.'], $request->price);
            $dataBefore->sub_total = str_replace(['.', ','], ['', '.'], $request->sub_total);
            $dataBefore->discount = str_replace(['.', ','], ['', '.'], $request->discount);
            $dataBefore->amount = str_replace(['.', ','], ['', '.'], $request->amount);
            $dataBefore->tax = $request->tax;
            $dataBefore->tax_rate = $request->tax_rate;
            $dataBefore->tax_value = str_replace(['.', ','], ['', '.'], $request->tax_value);
            $dataBefore->total_amount = str_replace(['.', ','], ['', '.'], $request->total_amount);
        }

        if($dataBefore->isDirty()){
            DB::beginTransaction();
            try{
                $storeData = [
                    'master_products_id' => $request->master_products_id,
                    'qty' => $request->qty,
                    'master_units_id' => $request->master_units_id,
                    'required_date' => $request->required_date,
                    'cc_co' => $request->cc_co,
                    'remarks' => $request->remarks,
                ];
                $storeDataItemPO = [
                    'master_products_id' => $request->master_products_id,
                    'qty' => $request->qty,
                    'master_units_id' => $request->master_units_id,
                ];
                if ($request->input_price == 'Y') {
                    $storeData = array_merge($storeData, [
                        'currency' => $request->currency,
                        'price' => str_replace(['.', ','], ['', '.'], $request->price),
                        'sub_total' => str_replace(['.', ','], ['', '.'], $request->sub_total),
                        'discount' => str_replace(['.', ','], ['', '.'], $request->discount),
                        'amount' => str_replace(['.', ','], ['', '.'], $request->amount),
                        'tax' => $request->tax,
                        'tax_rate' => $request->tax_rate,
                        'tax_value' => str_replace(['.', ','], ['', '.'], $request->tax_value),
                        'total_amount' => str_replace(['.', ','], ['', '.'], $request->total_amount),
                    ]);
                    $storeDataItemPO = array_merge($storeDataItemPO, [
                        'currency' => $request->currency,
                        'price' => str_replace(['.', ','], ['', '.'], $request->price),
                        'sub_total' => str_replace(['.', ','], ['', '.'], $request->sub_total),
                        'discount' => str_replace(['.', ','], ['', '.'], $request->discount),
                        'amount' => str_replace(['.', ','], ['', '.'], $request->amount),
                        'tax' => $request->tax,
                        'tax_rate' => $request->tax_rate,
                        'tax_value' => str_replace(['.', ','], ['', '.'], $request->tax_value),
                        'total_amount' => str_replace(['.', ','], ['', '.'], $request->total_amount),
                    ]);
                }
                $storeData = PurchaseRequisitionsDetail::where('id', $id)->update($storeData);
                $storeDataItemPO = PurchaseOrderDetails::where('id_purchase_requisition_details', $id)->update($storeDataItemPO);

                if ($request->input_price == 'Y') {
                    $totals = PurchaseRequisitionsDetail::where('id_purchase_requisitions', $dataBefore->id_purchase_requisitions)
                        ->selectRaw('SUM(sub_total) as total_sub_total, SUM(discount) as total_discount, SUM(amount) as total_sub_amount,
                            SUM(tax_value) as total_ppn, SUM(total_amount) as total_amount')
                        ->first();
                    // Round up to 3 decimal places
                    $sub_total = round($totals->total_sub_total, 3);
                    $total_discount = round($totals->total_discount, 3);
                    $total_sub_amount = round($totals->total_sub_amount, 3);
                    $total_ppn = round($totals->total_ppn, 3);
                    $total_amount = round($totals->total_amount, 3);
                    // Update PR Data
                    PurchaseRequisitions::where('id', $dataBefore->id_purchase_requisitions)->update([
                        'sub_total' => $sub_total,
                        'total_discount' => $total_discount,
                        'total_sub_amount' => $total_sub_amount,
                        'total_ppn' => $total_ppn,
                        'total_amount' => $total_amount,
                    ]);
                    // Update PO Data
                    PurchaseOrders::where('reference_number', $dataBefore->id_purchase_requisitions)->update([
                        'sub_total' => $sub_total,
                        'total_discount' => $total_discount,
                        'total_sub_amount' => $total_sub_amount,
                        'total_ppn' => $total_ppn,
                        'total_amount' => $total_amount,
                    ]);
                } else {
                    //Re-Calculate Item PO IF QTY CHANGE
                    $dataItemPO = PurchaseOrderDetails::where('id_purchase_requisition_details', $id)->first();
                    if($dataItemPO){
                        if($dataItemPO->price){
                            $qty = $request->qty;
                            $price = $dataItemPO->price;
                            $discount = isset($dataItemPO->discount) ? $dataItemPO->discount : 0;
                            $tax_rate = isset($dataItemPO->tax_rate) ? $dataItemPO->tax_rate : 0;

                            $sub_total = round(($qty * $price), 3);
                            $amount = round(($sub_total - $discount), 3);
                            $tax_value = round((($tax_rate/100) * $amount), 3);
                            $total_amount = round(($amount + $tax_value), 3);

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
                            $sub_total = round($totals->total_sub_total, 3);
                            $total_discount = round($totals->total_discount, 3);
                            $total_sub_amount = round($totals->total_sub_amount, 3);
                            $total_ppn = round($totals->total_ppn, 3);
                            $total_amount = round($totals->total_amount, 3);
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
        DB::beginTransaction();
        try{
            $idPR = PurchaseRequisitionsDetail::where('id', $id)->first()->id_purchase_requisitions;
            $dataPR = PurchaseRequisitions::where('id', $idPR)->first();
            $dataItemPO = PurchaseOrderDetails::where('id_purchase_requisition_details', $id)->first();

            PurchaseRequisitionsDetail::where('id', $id)->delete();
            PurchaseOrderDetails::where('id_purchase_requisition_details', $id)->delete();

            if ($dataPR->input_price == 'Y') {
                $totals = PurchaseRequisitionsDetail::where('id_purchase_requisitions', $dataPR->id)
                    ->selectRaw('SUM(sub_total) as total_sub_total, SUM(discount) as total_discount, SUM(amount) as total_sub_amount,
                        SUM(tax_value) as total_ppn, SUM(total_amount) as total_amount')
                    ->first();
                // Round up to 3 decimal places
                $sub_total = round($totals->total_sub_total, 3);
                $total_discount = round($totals->total_discount, 3);
                $total_sub_amount = round($totals->total_sub_amount, 3);
                $total_ppn = round($totals->total_ppn, 3);
                $total_amount = round($totals->total_amount, 3);
                // Update PR Data
                PurchaseRequisitions::where('id', $dataPR->id)->update([
                    'sub_total' => $sub_total,
                    'total_discount' => $total_discount,
                    'total_sub_amount' => $total_sub_amount,
                    'total_ppn' => $total_ppn,
                    'total_amount' => $total_amount,
                ]);
                // Update PO Data
                PurchaseOrders::where('reference_number', $dataPR->id)->update([
                    'sub_total' => $sub_total,
                    'total_discount' => $total_discount,
                    'total_sub_amount' => $total_sub_amount,
                    'total_ppn' => $total_ppn,
                    'total_amount' => $total_amount,
                ]);
            } else {
                if($dataItemPO){
                    $totals = PurchaseOrderDetails::where('id_purchase_orders', $dataItemPO->id_purchase_orders)
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
            $this->auditLogsShort('Hapus Purchase Requisitions Detail ID : (' . $id . ')');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Hapus Item PR', 'scrollTo' => 'tableItem']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Hapus Item PR!']);
        }
    }
}
