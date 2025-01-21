@extends('layouts.master')
@section('konten')

<div class="page-content">
    <div class="container-fluid">
        @include('layouts.alert')
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div class="page-title-left">
                        <a href="{{ route('edit_po', $data->id_purchase_orders) }}" class="btn btn-light waves-effect btn-label waves-light">
                            <i class="mdi mdi-arrow-left label-icon"></i> Back To Data Purchase Order
                        </a>
                    </div>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase</a></li>
                            <li class="breadcrumb-item active"> Edit Purchase Order Item</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <form method="post" action="/update_po_detail/{{ $data->id; }}" class="form-material m-t-40" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id_purchase_orders" value="{{ $data->id_purchase_orders }}">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Edit Purchase Order Item</h4>
                        </div>

                        <div class="card-body">
                            <div class="row mb-2 field-wrapper required-field">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Type Product</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control custom-bg-gray" placeholder="Masukkan Type Product.." name="type_product" value="{{ $data->type_product }}" readonly required>
                                </div>
                            </div>
                            <div class="row mb-2 field-wrapper required-field">
                                <label for="horizontal-email-input" class="col-sm-3 col-form-label">Product {{ $data->type_product; }}</label>
                                <div class="col-sm-9">
                                    @if($data->type_product=='RM')
                                        <select class="form-select request_number data-select2" name="master_products_id" id="" onchange="get_unit()" style="width: 100%" required>
                                            <option>Pilih Product RM</option>
                                            @foreach ($rawMaterials as $item)
                                                <option value="{{ $item->id }}" data-id="{{ $item->id }}" 
                                                    @if($data->master_products_id == $item->id) selected @endif>
                                                    {{ $item->description }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @elseif($data->type_product=='WIP')
                                        <select class="form-select request_number data-select2" name="master_products_id" id="" onchange="get_unit()" style="width: 100%" required>
                                            <option value="">Pilih Product WIP</option>
                                            @foreach ($wip as $item)
                                                <option value="{{ $item->id }}" data-id="{{ $item->id }}" 
                                                    @if($data->master_products_id == $item->id) selected @endif>
                                                    {{ $item->description }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @elseif($data->type_product=='FG')
                                        <select class="form-select request_number data-select2" name="master_products_id" id="" onchange="get_unit()" style="width: 100%" required>
                                            <option value="">Pilih Product FG</option>
                                            @foreach ($fg as $item)
                                                <option value="{{ $item->id }}" data-id="{{ $item->id }}" 
                                                    @if($data->master_products_id == $item->id) selected @endif>
                                                    {{ $item->description }} || {{ $item->perforasi }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @elseif($data->type_product=='TA')
                                        <select class="form-select request_number data-select2" name="master_products_id" id="" onchange="get_unit()" style="width: 100%" required>
                                            <option value="">Pilih Product Sparepart & Auxiliaries</option>
                                            @foreach ($ta as $item)
                                                <option value="{{ $item->id }}" data-id="{{ $item->id }}" 
                                                    @if($data->master_products_id == $item->id) selected @endif>
                                                    {{ $item->description }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @elseif($data->type_product=='Other')
                                        <select class="form-select request_number data-select2" name="master_products_id" id="" onchange="get_unit()" style="width: 100%" required>
                                            <option value="">Pilih Product Other</option>
                                            @foreach ($other as $item)
                                                <option value="{{ $item->id }}" data-id="{{ $item->id }}" 
                                                    @if($data->master_products_id == $item->id) selected @endif>
                                                    {{ $item->description }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            </div>
                            <br><br>
                            
                            <div class="row mb-2 field-wrapper required-field">
                                <label for="horizontal-password-input" class="col-sm-3 col-form-label">Qty</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" placeholder="Masukkan Qty.." name="qty" id="qty" value="{{ $data->qty }}">
                                </div>
                            </div>
                            <div class="row mb-2 field-wrapper required-field">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Units </label>
                                <div class="col-sm-9">
                                    <select class="form-select data-select2" name="master_units_id" id="unit_code" style="width: 100%" required>
                                        <option>Pilih Units</option>
                                        @foreach ($units as $item)
                                            <option value="{{ $item->id }}" @if($data->master_units_id == $item->id) selected @endif>
                                                {{ $item->unit_code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-2 field-wrapper required-field">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Currency</label>
                                <div class="col-sm-9">
                                    <select class="form-select data-select2" name="currency" id="" style="width: 100%" required>
                                        <option>Pilih Currency</option>
                                        @foreach ($currency as $item)
                                            <option value="{{ $item->currency_code }}" @if($data->currency == $item->currency_code) selected @endif>
                                                {{ $item->currency_code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-2 field-wrapper required-field">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Price </label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control rupiah-input" placeholder="Masukkan Price.." name="price" id="price" value="{{ $data->price }}" required>
                                </div>
                            </div>
                            <div class="row mb-2 field-wrapper required-field">
                                <label class="col-sm-3 col-form-label">Sub Total</label>
                                <div class="col-sm-9">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control custom-bg-gray rupiah-input" placeholder="Sub Total.. (Terisi Otomatis)" name="subTotal" id="subTotal" readonly>
                                        <span class="input-group-text">(Qty * Price)</span>
                                    </div>
                                </div>
                            </div>
                            <br><br>

                            <div class="row mb-2 field-wrapper required-field">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Discount </label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" placeholder="Masukkan Discount.." name="discount" id="discount" value="{{ $data->discount }}" required>
                                </div>
                            </div>
                            <div class="row mb-2 field-wrapper required-field">
                                <label class="col-sm-3 col-form-label">Amount</label>
                                <div class="col-sm-9">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control custom-bg-gray" placeholder="Amount.. (Terisi Otomatis)" name="amount" id="amount" value="{{ $data->amount }}" readonly>
                                        <span class="input-group-text">(Sub Total - Discount)</span>
                                    </div>
                                </div>
                            </div>
                            <br><br>

                            <div class="row mb-2 field-wrapper required-field">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tax</label>
                                <div class="col-sm-9">
                                    <input type="radio" id="tax_Y" name="tax" value="Y" @if($data->tax == 'Y') checked @endif>
                                    <label for="tax_Y">Y</label>
                                    <input type="radio" id="tax_N" name="tax" value="N" @if($data->tax == 'N') checked @endif>
                                    <label for="tax_N">N</label>
                                </div>
                            </div>
                            <div class="row mb-2 field-wrapper required-field">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tax Rate (%)</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" placeholder="Masukkan Tax.." name="tax_rate" id="tax_rate" value="" required>
                                </div>
                            </div>
                            <div class="row mb-2 field-wrapper required-field">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tax Value </label>
                                <div class="col-sm-9">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control custom-bg-gray" placeholder="Tax Value.. (Terisi Otomatis)" name="tax_value" id="tax_value" value="" readonly>
                                        <span class="input-group-text">(Tax Rate/100 * Amount)</span>
                                    </div>
                                </div>
                            </div>
                            <br><br>

                            <div class="row mb-2 field-wrapper required-field">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Total Amount </label>
                                <div class="col-sm-9">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control custom-bg-gray" placeholder="Total Amount.. (Terisi Otomatis)" name="total_amount" id="total_amount" value="" readonly>
                                        <span class="input-group-text">(Amount + Tax)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2 field-wrapper">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Note</label>
                                <div class="col-sm-9">
                                    <textarea name="note" rows="4" cols="50" class="form-control" placeholder="Note.. (Opsional)">{{ $data->note }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="row text-end">
                                <div>
                                    <button type="reset" class="btn btn-secondary w-md">Reset</button>
                                    <button type="submit" class="btn btn-primary w-md" name="update_detail">Update</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function formatPrice(value) {
        let num = parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
        return num;
    }
    function formatPriceDisplay(value) {
        return value.toFixed(3).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function calculateSubTotal() {
        let qty = parseFloat($('#qty').val()) || 0;
        let price = formatPrice($('#price').val()) || 0;
        let subTotal = qty * price;
        subTotal = Math.round(subTotal * 1000) / 1000; // Round to 3 decimal places
        $('#subTotal').val(formatPriceDisplay(subTotal));
        calculateAmount();
        calculateTotalAmount();
    }
    $('#qty, #price').on('input', function () {
        calculateSubTotal();
    });

    function calculateAmount() {
        let subTotal = formatPrice($('#subTotal').val()) || 0;
        let disc = formatPrice($('#discount').val()) || 0; 
        let amount = subTotal - disc;
        amount = Math.round(amount * 1000) / 1000;
        $('#amount').val(formatPriceDisplay(amount));
        calculateTotalAmount();
    }
    $('#discount').on('input', function () {
        calculateAmount();
    });

    function calculateTotalAmount() {
        let amount = formatPrice($('#amount').val()) || 0; 
        let taxRate = parseFloat($('#tax_rate').val()) || 0; 
        let taxValue = (taxRate/100) * amount;
        taxValue = Math.round(taxValue * 1000) / 1000;
        $('#tax_value').val(formatPriceDisplay(taxValue));

        let totalAmount = amount + taxValue;
        totalAmount = Math.round(totalAmount * 1000) / 1000;
        $('#total_amount').val(formatPriceDisplay(totalAmount));
    }
    $('#tax_rate').on('input', function () {
        calculateTotalAmount();
    });

    $('#tax_N').on('click', function () {
        $('#tax_rate').val(0,000).prop('readonly', true).addClass('custom-bg-gray');
        calculateTotalAmount();
    });
    $('#tax_Y').on('click', function () {
        $('#tax_rate').prop('readonly', false).removeClass('custom-bg-gray');
    });
</script>

@endsection