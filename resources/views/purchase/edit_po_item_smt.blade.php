@extends('layouts.master')

@section('konten')

<div class="page-content">
    <div class="container-fluid">
         @if (session('pesan'))
            <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="mdi mdi-check-all label-icon"></i><strong>Success</strong> - {{ session('pesan') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
         @endif
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"> Edit Purchase Order Item</h4>
                   
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase</a></li>
                            <li class="breadcrumb-item active"> Edit Purchase Order </li>
                        </ol>
                    </div>
                </div>
                <a href="javascript:void(0);" class="btn btn-info waves-effect waves-light" onclick="history.back();">Back To Data Purchase Order</a>
                <div></div>
            </div>
        </div>

        <form method="post" action="/update_po_detail_smt/{{ $results[0]->id; }}" class="form-material m-t-40" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Purchase Requisition Detail</h4>
                    </div>
                    <div class="card-body p-4">

                        <div class="col-sm-12">
                            <div class="mt-4 mt-lg-0">
                                
                                <div class="row mb-4 field-wrapper required-field">
                                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Type Product</label>
                                    <div class="col-sm-9">
                                        <input type="radio" id="html" name="type_product" value="{{ $results[0]->type_product }}" checked>
                                        <input type="hidden" name="id_purchase_orders" value="{{ $results[0]->id_po }}">
                                        <input type="hidden" name="id_pr" value="{{ $results[0]->id_pr }}">
                                        <label for="html">{{ $results[0]->type_product; }}</label>
                                    </div>
                                </div>
                                <div class="row mb-4 field-wrapper required-field">
                                    <label for="horizontal-email-input" class="col-sm-3 col-form-label">Product {{ $results[0]->type_product; }}</label>
                                    <div class="col-sm-9">
                                        @if($results[0]->type_product=='RM')
                                        <select class="form-select request_number data-select2" name="description" id="" onchange="get_unit()">
                                                <option value="">Pilih Product RM</option>
                                                @foreach ($rawMaterials as $data)
                                                    <option value="{{ $data->id }}" data-id="{{ $data->id }}" 
                                                        @if($results[0]->description == $data->id) selected @endif>
                                                        {{ $data->description }}
                                                    </option>
                                                @endforeach

                                        </select>
                                        @elseif($results[0]->type_product=='WIP')
                                        <select class="form-select request_number data-select2" name="description" id="" onchange="get_unit()">
                                                <option value="">Pilih Product WIP</option>
                                            @foreach ($wip as $data)
                                                    <option value="{{ $data->id }}" data-id="{{ $data->id }}" 
                                                        @if($results[0]->description == $data->id) selected @endif>
                                                        {{ $data->description }}
                                                    </option>
                                            @endforeach
                                        </select>
                                        @elseif($results[0]->type_product=='FG')
                                        <select class="form-select request_number data-select2" name="description" id="" onchange="get_unit()">
                                                <option value="">Pilih Product FG</option>
                                            @foreach ($fg as $data)
                                                    <option value="{{ $data->id }}" data-id="{{ $data->id }}" 
                                                        @if($results[0]->description == $data->id) selected @endif>
                                                        {{ $data->description }} || {{ $data->perforasi }}
                                                    </option>
                                            @endforeach
                                        </select>
                                        @elseif($results[0]->type_product=='TA')
                                        <select class="form-select request_number data-select2" name="description" id="" onchange="get_unit()">
                                                <option value="">Pilih Product Sparepart & Auxiliaries</option>
                                            @foreach ($ta as $data)
                                                    <option value="{{ $data->id }}" data-id="{{ $data->id }}" 
                                                        @if($results[0]->description == $data->id) selected @endif>
                                                        {{ $data->description }}
                                                    </option>
                                            @endforeach
                                        </select>
                                        @elseif($results[0]->type_product=='Other')
                                        <select class="form-select request_number data-select2" name="description" id="" onchange="get_unit()">
                                                <option value="">Pilih Product Other</option>
                                            @foreach ($other as $data)
                                                    <option value="{{ $data->id }}" data-id="{{ $data->id }}" 
                                                        @if($results[0]->description == $data->id) selected @endif>
                                                        {{ $data->description }}
                                                    </option>
                                            @endforeach
                                        </select>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-4 field-wrapper required-field">
                                    <label for="horizontal-password-input" class="col-sm-3 col-form-label">Qty</label>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control" name="qty" id="qty" value="{{ $results[0]->qty }}">
                                    </div>
                                </div>
                                <div class="row mb-4 field-wrapper required-field">
                                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Units </label>
                                    <div class="col-sm-9">
                                    <select class="form-select data-select2" name="unit" id="unit_code">
                                        <option value="">Pilih Units</option>
                                        @foreach ($units as $data)
                                            <option value="{{ $data->unit }}" @if($results[0]->unit == $data->unit) selected @endif>
                                                {{ $data->unit_code }}
                                            </option>
                                        @endforeach
                                    </select>

                                    </div>
                                </div>
                                <div class="row mb-4 field-wrapper">
                                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Currency</label>
                                    <div class="col-sm-9">
                                        <select class="form-select data-select2" name="currency" id="">
                                            <option value="">Pilih Currency</option>
                                            @foreach ($currency as $data)
                                                <option value="{{ $data->currency_code }}" @if($results[0]->currency == $data->currency_code) selected @endif>
                                                    {{ $data->currency_code }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-4 field-wrapper required-field">
                                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Price </label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="price" id="price" value="{{ $results[0]->price }}">
                                    </div>
                                </div>
                                <div class="row mb-4 field-wrapper required-field">
                                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Discount </label>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control" name="discount" id="discount" value="{{ $results[0]->discount }}">
                                    </div>
                                </div>
                                <div class="row mb-4 field-wrapper required-field">
                                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tax Rate (%)</label>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control" name="tax_rate" id="tax_rate" value="11">
                                    </div>
                                </div>

                                <div class="row mb-4 field-wrapper">
                                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tax</label>
                                    <div class="col-sm-9">
                                        <input type="radio" id="tax_Y" name="tax" value="Y" 
                                            @if($results[0]->tax == 'Y') checked @endif>
                                        <label for="tax_Y">Y</label>

                                        <input type="radio" id="tax_N" name="tax" value="N" 
                                            @if($results[0]->tax == 'N') checked @endif>
                                        <label for="tax_N">N</label>
                                    </div>
                                </div>

                                <style>
                                    .custom-bg-gray {
                                        background-color: #c4c4c4; /* Warna abu-abu yang lebih terang */
                                    }
                                </style>
                                <div class="row mb-4 field-wrapper required-field">
                                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Amount </label>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control custom-bg-gray" name="amount" id="amount" value="{{ $results[0]->amount }}" readonly>
                                    </div>
                                </div>
                                <div class="row mb-4 field-wrapper required-field">
                                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Total Amount </label>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control custom-bg-gray" name="total_amount" id="total_amount" value="" readonly>
                                    </div>
                                </div>
                                <div class="row mb-4 field-wrapper">
                                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Note</label>
                                    <div class="col-sm-9">
                                        <textarea name="note" rows="4" cols="50" class="form-control">{{ $results[0]->note }}</textarea>
                                    </div>
                                </div>
                                <div class="row justify-content-end">
                                    <div class="col-sm-9">
                                        <div>
                                            <button type="reset" class="btn btn-info w-md">Reset</button>
                                            <button type="submit" class="btn btn-primary w-md" name="update_detail">Update</button>
                                        </div>
                                    </div>
                                </div>
                                
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
</script>

@endsection