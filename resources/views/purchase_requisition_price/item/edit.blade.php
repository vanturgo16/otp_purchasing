@extends('layouts.master')
@section('konten')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div class="page-title-left">
                        <a href="{{ route('pr.price.edit', encrypt($idPRPrice)) }}" class="btn btn-light waves-effect btn-label waves-light">
                            <i class="mdi mdi-arrow-left label-icon"></i> Back To Data Purchase Requisition Price
                        </a>
                    </div>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase</a></li>
                            <li class="breadcrumb-item active"> Edit Item PR Price ({{ $data->type_product }})</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.alert')
        {{-- ITEM PR --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Edit Purchase Requisition Detail Price ({{ $data->type_product }})</h4>
            </div>
            <form method="POST" action="{{ route('pr.price.updateItem', encrypt($data->id)) }}" class="form-material m-t-40 formLoad" enctype="multipart/form-data">
                @csrf
                <div class="card-body p-4">
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Type Product</label>
                        <div class="col-sm-9">
                            <input type="radio" name="type_product" value="{{ $data->type_product }}" checked>
                            <label for="html">{{ $data->type_product }}</label>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Product {{ $data->type_product }}</label>
                        <div class="col-sm-9">
                            <select class="form-select request_number data-select2 readonly-select2" name="master_products_id" style="width: 100%" required>
                                <option value="">Pilih Product {{ $data->type_product }}</option>
                                @foreach ($products as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == $data->master_products_id ? 'selected' : '' }}>{{ $item->description }}
                                        @if($data->type_product == 'FG')
                                            @if(!empty($item->perforasi)) || {{ $item->perforasi }} @endif
                                            @if(!empty($item->group_sub_code)) || Group Sub: {{ $item->group_sub_code }} @endif
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Qty</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control custom-bg-gray" name="qty" id="qty" value="{{ $data->qty }}" placeholder="Masukkan Qty.." readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Units</label>
                        <div class="col-sm-9">
                            <select class="form-select data-select2 readonly-select2" name="master_units_id" style="width: 100%" required>
                                <option value="">Pilih Units</option>
                                @foreach ($units as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == $data->master_units_id ? 'selected' : '' }}>{{ $item->unit_code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Required Date</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control custom-bg-gray" name="required_date" value="{{ $data->required_date }}" readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">CC / CO</label>
                        <div class="col-sm-9">
                            <select class="form-select data-select2 readonly-select2" name="cc_co" style="width: 100%" required>
                                <option value="">Pilih CC / CO</option>
                                @foreach ($requesters as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == $data->cc_co ? 'selected' : '' }}>{{ $item->nm_requester }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label class="col-sm-3 col-form-label">Remarks</label>
                        <div class="col-sm-9">
                            <textarea name="remarks" rows="3" cols="50" class="form-control custom-bg-gray" placeholder="Remarks.. (Opsional)" readonly>{{ $data->remarks }}</textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-2 field-wrapper required-field">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Currency</label>
                        <div class="col-sm-9">
                            <select class="form-select data-select2" name="currency" id="" style="width: 100%" required>
                                <option value="">Pilih Currency</option>
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
                            <input type="text" class="form-control number-format" placeholder="Masukkan Price.." name="price" id="price" 
                                value="{{ $data->price ? (strpos($data->price, '.') === false ? number_format($data->price, 0, ',', '.') : number_format($data->price, 3, ',', '.')) : '0' }}" 
                            required>
                        </div>
                    </div>
                    <div class="row mb-2 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Sub Total</label>
                        <div class="col-sm-9">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control custom-bg-gray" placeholder="Sub Total.. (Terisi Otomatis)"
                                    value="{{ $data->sub_total ? (strpos($data->sub_total, '.') === false ? number_format($data->sub_total, 0, ',', '.') : number_format($data->sub_total, 3, ',', '.')) : '' }}" 
                                    name="sub_total" id="sub_total" readonly>
                                <span class="input-group-text">(Qty * Price)</span>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2 field-wrapper required-field">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Discount </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control number-format" placeholder="Masukkan Discount.." name="discount" id="discount" 
                                value="{{ $data->discount ? (strpos($data->discount, '.') === false ? number_format($data->discount, 0, ',', '.') : number_format($data->discount, 3, ',', '.')) : '0' }}" 
                                required>
                        </div>
                    </div>
                    <div class="row mb-2 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Amount</label>
                        <div class="col-sm-9">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control custom-bg-gray" placeholder="Amount.. (Terisi Otomatis)" name="amount" id="amount" 
                                    value="{{ $data->amount ? (strpos($data->amount, '.') === false ? number_format($data->amount, 0, ',', '.') : number_format($data->amount, 3, ',', '.')) : '' }}" 
                                    readonly>
                                <span class="input-group-text">(Sub Total - Discount)</span>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2 field-wrapper required-field">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tax</label>
                        <div class="col-sm-9">
                            <input type="radio" id="tax_Y" name="tax" value="Y" @if($data->tax == 'Y') checked @endif required>
                            <label for="tax_Y">Y</label>
                            <input type="radio" id="tax_N" name="tax" value="N" @if($data->tax == 'N') checked @endif>
                            <label for="tax_N">N</label>
                        </div>
                    </div>
                    <div class="row mb-2 field-wrapper required-field">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tax Rate (%)</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" placeholder="Masukkan Tax.." name="tax_rate" id="tax_rate" value="{{ $data->tax_rate }}" required>
                        </div>
                    </div>
                    <div class="row mb-2 field-wrapper required-field">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tax Value </label>
                        <div class="col-sm-9">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control custom-bg-gray" placeholder="Tax Value.. (Terisi Otomatis)" name="tax_value" id="tax_value" 
                                    value="{{ $data->tax_value ? (strpos($data->tax_value, '.') === false ? number_format($data->tax_value, 0, ',', '.') : number_format($data->tax_value, 3, ',', '.')) : '' }}" 
                                    readonly>
                                <span class="input-group-text">(Tax Rate/100 * Amount)</span>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2 field-wrapper required-field">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Total Amount </label>
                        <div class="col-sm-9">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control custom-bg-gray" placeholder="Total Amount.. (Terisi Otomatis)" name="total_amount" id="total_amount" 
                                    value="{{ $data->total_amount ? (strpos($data->total_amount, '.') === false ? number_format($data->total_amount, 0, ',', '.') : number_format($data->total_amount, 3, ',', '.')) : '' }}" 
                                    readonly>
                                <span class="input-group-text">(Amount + Tax)</span>
                            </div>
                        </div>
                    </div>
                    <script>
                        function formatPrice(value) {
                            let num = parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
                            return num;
                        }
                        function formatPriceDisplay(value) {
                            let formatted = value.toFixed(3).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            if (formatted.endsWith(',000')) {
                                formatted = formatted.slice(0, -4);
                            }
                            return formatted;
                        }
                        function calculateSubTotal() {
                            let qty = parseFloat($('#qty').val()) || 0;
                            let price = formatPrice($('#price').val()) || 0;
                            let subTotal = qty * price;
                            subTotal = Math.round(subTotal * 1000) / 1000; // Round to 3 decimal places
                            $('#sub_total').val(formatPriceDisplay(subTotal));
                            calculateAmount();
                            calculateTotalAmount();
                        }
                        $('#qty, #price').on('input', function () {
                            calculateSubTotal();
                        });

                        function calculateAmount() {
                            let subTotal = formatPrice($('#sub_total').val()) || 0;
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
                </div>
                <div class="card-footer">
                    <div class="row text-end">
                        <div>
                            <button type="reset" class="btn btn-secondary waves-effect btn-label waves-light">
                                <i class="mdi mdi-reload label-icon"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-info waves-effect btn-label waves-light">
                                <i class="mdi mdi-update label-icon"></i>Update
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
