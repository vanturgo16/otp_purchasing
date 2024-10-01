<!-- input Purchase Order -->
<div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true" data-bs-scroll="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/simpan_po" class="form-material m-t-40" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">PO Number</label>
                    <input class="form-control" type="text" name="po_number" id="generatedCode" readonly>
                    @error('po_number')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Date</label>
                    <input class="form-control" type="date" name="date" value="{{ date('Y-m-d'), old('date') }}">
                    @error('date')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Delivery Date</label>
                    <input class="form-control" type="date" name="delivery_date" value="{{ date('Y-m-d'), old('delivery_date') }}">
                    @error('delivery_date')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Reference Number (PR) *</label>
                    <select class="form-select request_number" name="reference_number" onchange="get_supplier()">
                        <option>Pilih Reference Number</option>
                    </select>
                    @error('reference_number')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Supplier *</label>
                    <select class="form-select name_supplier" name="id_master_suppliers">
                        <option>Pilih Supplier</option>
                    </select>
                    @error('id_master_suppliers')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">QC Check *</label>
                    <input class="form-control" type="text" id="qc_check" name="qc_check" readonly>
                    @error('qc_check')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Down Payment </label>
                    <input class="form-control" type="text" name="down_payment" value="0">
                    @error('down_payment')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Own Remarks</label>
                    <textarea class="form-control" name="own_remarks" rows="4" cols="50"></textarea>
                    @error('own_remarks')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Supplier Remarks</label>
                    <textarea class="form-control" name="supplier_remarks" rows="4" cols="50"></textarea>
                    @error('supplier_remarks')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Status *</label>
                    <input class="form-control" type="text" name="status" value="Request" readonly>
                    @error('status')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Type *</label>
                    <input class="form-control" type="text" name="type" id="type_pr" readonly>
                    <input class="form-control" type="hidden" name="non_invoiceable" value="N">
                    <input class="form-control" type="hidden" name="vendor_taxable" value="N">
                    @error('type')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Back</button>
                <!-- <button type="submit" class="btn btn-primary waves-effect waves-light">Save & Add More</button> -->
                <button type="submit" class="btn btn-primary waves-effect waves-light">Save</button>
            </div>

            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- edit Purchase Order -->
<div id="edit-po" class="modal fade" tabindex="-1" aria-labelledby="edit_poLabel" aria-hidden="true" data-bs-scroll="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_poLabel">Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/simpan_po" class="form-material m-t-40" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">PO Number</label>
                    <input class="form-control" type="text" name="po_number" id="po_number_po" readonly>
                    @error('po_number')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Date</label>
                    <input class="form-control" type="date" name="date" value="{{ old('date') }}" id="date_po">
                    @error('date')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Reference Number (PR) *</label>
                    <select class="form-select request_number" name="request_number" id="request_number_po">
                        <option>Pilih Supplier</option>
                    </select>
                    @error('reference_number')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Supplier *</label>
                    <select class="form-select name_supplier" name="id_master_suppliers" id="">
                        <option>Pilih Supplier</option>
                    </select>
                    @error('id_master_suppliers')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">QC Check *</label>
                    <input class="form-control" type="text"  name="qc_check" value="N" readonly>
                    @error('qc_check')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Down Payment </label>
                    <input class="form-control" type="text" name="down_payment" id="down_payment_po">
                    @error('down_payment')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Own Remarks</label>
                    <textarea name="own_remarks" rows="4" cols="50" id="own_remarks_po"></textarea>
                    @error('own_remarks')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Supplier Remarks</label>
                    <textarea name="supplier_remarks" rows="4" cols="50" id="supplier_remarks_po"></textarea>
                    @error('supplier_remarks')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Status *</label>
                    <input class="form-control" type="text" name="status" value="Request" id="status_po" readonly>
                    @error('status')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Type *</label>
                    <input class="form-control" type="text" name="type" id="type_po">
                    <input class="form-control" type="hidden" name="non_invoiceable" value="N">
                    <input class="form-control" type="hidden" name="vendor_taxable" value="N">
                    @error('type')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Back</button>
                <button type="submit" class="btn btn-primary waves-effect waves-light">Save & Add More</button>
                <button type="submit" class="btn btn-primary waves-effect waves-light">Save</button>
            </div>

            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- edit Purchase Order -->
<div id="edit-po-detail" class="modal fade" tabindex="-1" aria-labelledby="edit_poLabel" aria-hidden="true" data-bs-scroll="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_poLabel">Purchase Order Detail.</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/update_po_detail" class="form-material m-t-40" enctype="multipart/form-data" id="form_po_detail">
            @method('PUT')
            @csrf
            <div class="modal-body">
                
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Type Product</label>
                    <input class="form-control" type="text" name="type_product" id="type_product_po_detail" readonly>
                    <input class="form-control" type="hidden" name="id_purchase_orders" id="id_purchase_orders_po_detail" readonly>
                    @error('type_product')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Product RM</label>
                    
                    <select class="form-select" name="master_products_id" id="master_products_id_po_detail">
                        <option>Pilih Product</option>
                    </select>
                    @error('master_products_id')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Qty *</label>
                    <input class="form-control" type="text" name="qty"  id="qty_po_detail">
                    @error('qty')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Units *</label>
                    <select class="form-select " name="master_units_id" id="master_units_id_po_detail">
                        <option>Pilih Unit</option>
                    </select>
                    @error('master_units_id')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Price</label>
                    <input class="form-control" type="text"  name="price" id="price_po_detail">
                    @error('price')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Discount </label>
                    <input class="form-control" type="text" name="discount" id="discount_po_detail">
                    @error('discount')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Tax</label>
                    <input class="form-control" type="text" name="tax" id="tax_po_detail">
                    @error('tax')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Amount</label>
                    <input class="form-control" type="text" name="amount" id="amount_po_detail">
                    @error('amount')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Note </label>
                    <textarea class="form-control" name="note" rows="4" cols="50" id="note_po_detail"></textarea>
                    @error('note')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Back</button>
                <button type="submit" class="btn btn-primary waves-effect waves-light">Update</button>
            </div>

            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- edit Purchase Order Detail smt -->
<div id="edit-po-detail-smt" class="modal fade" tabindex="-1" aria-labelledby="edit_poLabel" aria-hidden="true" data-bs-scroll="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_poLabel">Purchase Order Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/update_po_detail_smt" class="form-material m-t-40" enctype="multipart/form-data" id="form_po_detail_smt">
            @method('PUT')
            @csrf
            <div class="modal-body">
                
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Type Product</label>
                    <input class="form-control" type="text" name="type_product" id="type_product_po_detail_smt" readonly>
                    <input class="form-control" type="hidden" name="id_pr" id="id_purchase_orders_po_detail_smt" readonly>
                    <input class="form-control" type="hidden" name="id" id="id_po_detail_smt" readonly>
                    @error('type_product')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Product RM</label>
                    
                    <select class="form-select" name="description" id="master_products_id_po_detail_smt">
                        <option>Pilih Product</option>
                    </select>
                    @error('description')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Qty *</label>
                    <input class="form-control" type="text" name="qty"  id="qty_po_detail_smt">
                    @error('qty')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Units *</label>
                    <select class="form-select " name="unit" id="master_units_id_po_detail_smt">
                        <option>Pilih Unit</option>
                    </select>
                    @error('master_units_id')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Price</label>
                    <input class="form-control" type="number"  name="price" id="price_po_detail_smt">
                    @error('price')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Discount </label>
                    <input class="form-control" type="number" name="discount" id="discount_po_detail_smt">
                    @error('discount')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Tax</label>
                    <input class="form-control" type="text" name="tax" id="tax_po_detail_smt">
                    
                    @error('tax')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Amount</label>
                    <input class="form-control" type="text" name="amount" id="amount_po_detail_smt">
                    @error('amount')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Note </label>
                    <textarea class="form-control" name="note" rows="4" cols="50" id="note_po_detail_smt"></textarea>
                    @error('note')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Back</button>
                <button type="submit" class="btn btn-primary waves-effect waves-light">Update</button>
            </div>

            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- edit Purchase RRequisition Sementara -->
<div id="edit-pr-smt" class="modal fade" tabindex="-1" aria-labelledby="edit_poLabel" aria-hidden="true" data-bs-scroll="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_poLabel">Edit Purchase Requisition Sementara</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/update_pr_detail_editx" class="form-material m-t-40" enctype="multipart/form-data"  id="form_pr_detail_edit">
            @method('PUT')
            @csrf
            <div class="modal-body">
                
                <div class="mb-3 required-field">
                    <label for="example-text-input" class="form-label">Type Product</label>
                    <input type="text" class="form-control" id="type_product_detail_pr" name="type_product">
                    <input type="hidden" class="form-control" id="request_number_detail_pr" name="request_number">
                    <input type="hidden" class="form-control" id="id_purchase_requisitions_detail_pr" name="id_purchase_requisitions">
                    @error('type')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3 required-field">
                    <label for="example-text-input" class="form-label">Product</label>
                    <select class="form-select" name="master_products_id" id="master_products_id_detail_pr">
                            <option>Pilih Product</option>
                    </select>
                    @error('master_products_id')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3 required-field">
                    <label for="example-text-input" class="form-label">Qty</label>
                    <input type="number" class="form-control" name="qty" id="qty_detail_pr">
                    @error('qty')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3 required-field">
                    <label for="example-text-input" class="form-label">Units</label>
                    <select class="form-select" name="master_units_id" id="master_units_id_detail_pr">
                        <option>Pilih Units</option>
                    </select>
                    @error('master_units_id')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3 required-field">
                    <label for="example-text-input" class="form-label">Required Date</label>
                    <input type="date" class="form-control" name="required_date" id="required_date_detail_pr">
                    @error('required_date')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3 required-field">
                    <label for="example-text-input" class="form-label">CC / CO </label>
                    <select class="form-select" name="cc_co" id="cc_co_detail_pr">
                        <option>Pilih CC / CO</option>
                    </select>
                    @error('cc_co')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Remarks</label>
                    <input type="text" class="form-control" name="remarks" id="remarks_detail_pr">
                    @error('own_remarks')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary waves-effect waves-light">Update</button>
            </div>

            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- edit Purchase RRequisition -->
<div id="edit-pr" class="modal fade" tabindex="-1" aria-labelledby="edit_poLabel" aria-hidden="true" data-bs-scroll="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_poLabel">Edit Purchase Requisition</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/update_pr_detailx" class="form-material m-t-40" enctype="multipart/form-data" id="form_pr_detail">
            @method('PUT')
            @csrf
            <div class="modal-body">
                
                <div class="mb-3 required-field">
                    <label for="example-text-input" class="form-label">Type Product</label>
                    <input type="text" class="form-control" id="type_product_pr" name="type_product">
                    <input type="hidden" class="form-control" id="request_number_pr" name="request_number">
                    <input type="hidden" class="form-control" id="id_purchase_requisitions_pr" name="id_purchase_requisitions">
                    @error('type')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3 required-field">
                    <label for="example-text-input" class="form-label">Product</label>
                    <select class="form-select" name="master_products_id" id="master_products_id_pr">
                            <option>Pilih Product</option>
                    </select>
                    @error('master_products_id')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3 required-field">
                    <label for="example-text-input" class="form-label">Qty</label>
                    <input type="number" class="form-control" name="qty" id="qty_pr">
                    @error('qty')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3 required-field">
                    <label for="example-text-input" class="form-label">Units</label>
                    <select class="form-select" name="master_units_id" id="master_units_id_pr">
                        <option>Pilih Units</option>
                    </select>
                    @error('master_units_id')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3 required-field">
                    <label for="example-text-input" class="form-label">Required Date</label>
                    <input type="date" class="form-control" name="required_date" id="required_date_pr">
                    @error('required_date')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3 required-field">
                    <label for="example-text-input" class="form-label">CC / CO </label>
                    <select class="form-select" name="cc_co" id="cc_co_pr">
                        <option>Pilih CC / CO</option>
                    </select>
                    @error('cc_co')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Remarks</label>
                    <input type="text" class="form-control" name="remarks" id="remarks_pr">
                    @error('own_remarks')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary waves-effect waves-light">Update</button>
            </div>

            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->