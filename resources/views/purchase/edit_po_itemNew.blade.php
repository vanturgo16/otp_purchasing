@extends('layouts.master')
@section('konten')

<div class="page-content">
    <div class="container-fluid">
        @include('layouts.alert')
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div class="page-title-left">
                        <a href="{{ route('edit_po', $results[0]->id_purchase_orders) }}" class="btn btn-light waves-effect btn-label waves-light">
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

        <form method="post" action="/update_po_detail/{{ $results[0]->id; }}" class="form-material m-t-40" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id_purchase_orders" value="{{ $results[0]->id_purchase_orders }}">
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
                                    <input type="text" class="form-control custom-bg-gray" placeholder="Masukkan Type Product.." name="type_product" value="{{ $results[0]->type_product }}" readonly required>
                                </div>
                            </div>
                            <div class="row mb-2 field-wrapper required-field">
                                <label for="horizontal-email-input" class="col-sm-3 col-form-label">Product {{ $results[0]->type_product; }}</label>
                                <div class="col-sm-9">
                                    @if($results[0]->type_product=='RM')
                                        <select class="form-select request_number data-select2" name="master_products_id" id="" onchange="get_unit()">
                                            <option>Pilih Product RM</option>
                                            @foreach ($rawMaterials as $data)
                                                <option value="{{ $data->id }}" data-id="{{ $data->id }}" 
                                                    @if($results[0]->master_products_id == $data->id) selected @endif>
                                                    {{ $data->description }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @elseif($results[0]->type_product=='WIP')
                                        <select class="form-select request_number data-select2" name="master_products_id" id="" onchange="get_unit()">
                                            <option value="">Pilih Product WIP</option>
                                            @foreach ($wip as $data)
                                                <option value="{{ $data->id }}" data-id="{{ $data->id }}" 
                                                    @if($results[0]->master_products_id == $data->id) selected @endif>
                                                    {{ $data->description }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @elseif($results[0]->type_product=='FG')
                                        <select class="form-select request_number data-select2" name="master_products_id" id="" onchange="get_unit()">
                                            <option value="">Pilih Product FG</option>
                                            @foreach ($fg as $data)
                                                <option value="{{ $data->id }}" data-id="{{ $data->id }}" 
                                                    @if($results[0]->master_products_id == $data->id) selected @endif>
                                                    {{ $data->description }} || {{ $data->perforasi }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @elseif($results[0]->type_product=='TA')
                                        <select class="form-select request_number data-select2" name="master_products_id" id="" onchange="get_unit()">
                                            <option value="">Pilih Product Sparepart & Auxiliaries</option>
                                            @foreach ($ta as $data)
                                                <option value="{{ $data->id }}" data-id="{{ $data->id }}" 
                                                    @if($results[0]->master_products_id == $data->id) selected @endif>
                                                    {{ $data->description }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @elseif($results[0]->type_product=='Other')
                                        <select class="form-select request_number data-select2" name="master_products_id" id="" onchange="get_unit()">
                                            <option value="">Pilih Product Other</option>
                                            @foreach ($other as $data)
                                                <option value="{{ $data->id }}" data-id="{{ $data->id }}" 
                                                    @if($results[0]->master_products_id == $data->id) selected @endif>
                                                    {{ $data->description }}
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
                                    <input type="number" class="form-control" placeholder="Masukkan Qty.." name="qty" id="qty" value="{{ $results[0]->qty }}">
                                </div>
                            </div>
                            <div class="row mb-2 field-wrapper required-field">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Units </label>
                                <div class="col-sm-9">
                                    <select class="form-select data-select2" name="master_units_id" id="unit_code">
                                        <option>Pilih Units</option>
                                        @foreach ($units as $data)
                                            <option value="{{ $data->id }}" @if($results[0]->master_units_id == $data->id) selected @endif>
                                                {{ $data->unit_code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-2 field-wrapper">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Currency</label>
                                <div class="col-sm-9">
                                    <select class="form-select data-select2" name="currency" id="">
                                        <option>Pilih Currency</option>
                                        @foreach ($currency as $data)
                                            <option value="{{ $data->currency_code }}" @if($results[0]->currency == $data->currency_code) selected @endif>
                                                {{ $data->currency_code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-2 field-wrapper required-field">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Price </label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" placeholder="Masukkan Price.." name="price" id="price" value="{{ $results[0]->price }}">
                                </div>
                            </div>
                            <div class="row mb-2 field-wrapper required-field">
                                <label class="col-sm-3 col-form-label">Sub Total</label>
                                <div class="col-sm-9">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control custom-bg-gray" placeholder="Sub Total.. (Terisi Otomatis)" name="subTotal" id="subTotal" readonly>
                                        <span class="input-group-text">(Qty * Price)</span>
                                    </div>
                                </div>
                            </div>
                            <br><br>

                            <div class="row mb-2 field-wrapper required-field">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Discount </label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" placeholder="Masukkan Discount.." name="discount" id="discount" value="{{ $results[0]->discount }}">
                                </div>
                            </div>
                            <div class="row mb-2 field-wrapper required-field">
                                <label class="col-sm-3 col-form-label">Amount</label>
                                <div class="col-sm-9">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control custom-bg-gray" placeholder="Amount.. (Terisi Otomatis)" name="amount" id="amount" value="{{ $results[0]->amount }}" readonly>
                                        <span class="input-group-text">(Sub Total - Discount)</span>
                                    </div>
                                </div>
                            </div>
                            <br><br>

                            <div class="row mb-2 field-wrapper required-field">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tax</label>
                                <div class="col-sm-9">
                                    <input type="radio" id="tax_Y" name="tax" value="Y" @if($results[0]->tax == 'Y') checked @endif>
                                    <label for="tax_Y">Y</label>
                                    <input type="radio" id="tax_N" name="tax" value="N" @if($results[0]->tax == 'N') checked @endif>
                                    <label for="tax_N">N</label>
                                </div>
                            </div>
                            <div class="row mb-2 field-wrapper required-field">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tax Rate (%)</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" placeholder="Masukkan Tax.." name="tax_rate" id="tax_rate" value="">
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
                                    <textarea name="note" rows="4" cols="50" class="form-control" placeholder="Note.. (Opsional)">{{ $results[0]->note }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="row text-end">
                                <div>
                                    <button type="reset" class="btn btn-info w-md">Reset</button>
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
    function calculateSubTotal() {
        let qty = parseFloat($('#qty').val()) || 0;
        let price = parseFloat($('#price').val()) || 0;
        let subTotal = qty * price;
        $('#subTotal').val(subTotal);
        calculateAmount();
        calculateTotalAmount();
    }
    $('#qty, #price').on('input', function () {
        calculateSubTotal();
    });

    function calculateAmount() {
        let subTotal = parseFloat($('#subTotal').val()) || 0;
        let disc = parseFloat($('#discount').val()) || 0;
        let amount = subTotal - disc;
        $('#amount').val(amount);
        calculateTotalAmount();
    }
    $('#discount').on('input', function () {
        calculateAmount();
    });

    function calculateTotalAmount() {
        let amount = parseFloat($('#amount').val()) || 0;
        let taxRate = parseFloat($('#tax_rate').val()) || 0;
        let taxValue = (taxRate/100) * amount;
        $('#tax_value').val(taxValue);
        let totalAmount = amount + taxValue;
        $('#total_amount').val(totalAmount);
    }
    $('#tax_rate').on('input', function () {
        calculateTotalAmount();
    });

    $('#tax_N').on('click', function () {
        $('#tax_rate').val(0).prop('readonly', true).addClass('custom-bg-gray');
        calculateTotalAmount();
    });
    $('#tax_Y').on('click', function () {
        $('#tax_rate').prop('readonly', false).removeClass('custom-bg-gray');
    });
</script>

{{-- <script>
    // Ambil elemen input
    const qtyInput = document.getElementById('qty');
    const priceInput = document.getElementById('price');
    const discountInput = document.getElementById('discount');
    const amountInput = document.getElementById('amount');
    const total_amountInput = document.getElementById('total_amount');
    const taxRadios = document.getElementsByName('tax');
    const taxRateInput = document.getElementById('tax_rate'); // Tambahkan ini untuk nilai pajak

    // Tambahkan event listener untuk menghitung jumlah saat nilai berubah
    [qtyInput, priceInput, discountInput, taxRateInput].forEach(input => {
        input.addEventListener('input', calculateAmount);
    });

    // Tambahkan event listener untuk menghitung jumlah saat radio button berubah
    taxRadios.forEach(radio => {
        radio.addEventListener('change', calculateAmount);
    });

    // Fungsi untuk memformat angka
    function numberFormat(number, decimals, dec_point, thousands_sep) {
        number = (number + '').replace(',', '').replace(' ', '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? '.' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? ',' : dec_point,
            s = '',
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return '' + (Math.round(n * k) / k).toFixed(prec);
            };
        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }

    // Fungsi untuk menghitung jumlah
    function calculateAmount() {
        const qty = parseFloat(qtyInput.value);
        const price = parseFloat(priceInput.value);
        const discount = parseFloat(discountInput.value);
        const taxRate = parseFloat(taxRateInput.value); // Ambil nilai pajak dari input

        if (isNaN(qty) || isNaN(price) || isNaN(discount)) {
            amountInput.value = '';
            total_amountInput.value = '';
            return;
        }

        // Hitung jumlah diskon dalam persen
        const discountAmount = (price * discount) / 100;

        // Hitung jumlah setelah diskon
        const amount = ((qty * price) - discount);

        // Tentukan apakah pajak dihitung atau tidak
        const isTaxed = document.querySelector('input[name="tax"]:checked').value === 'Y';

        // Hitung pajak 11% dari amount jika applicable
        const tax = isTaxed ? (amount * taxRate) / 100 : 0;

        // Hitung total amount
        const total_amount = amount + tax;

        // Masukkan hasil perhitungan ke dalam input amount dan total_amount
        amountInput.value = isNaN(amount) ? '' : amount.toFixed(0); 
        total_amountInput.value = isNaN(total_amount) ? '' : total_amount.toFixed(0); 

         // Masukkan hasil perhitungan ke dalam input amount dan total_amount
        // amountInput.value = isNaN(amount) ? '' : numberFormat(amount, 3, ',', '.');
        // total_amountInput.value = isNaN(total_amount) ? '' : numberFormat(total_amount, 3, ',', '.');
    }
</script> --}}

@endsection