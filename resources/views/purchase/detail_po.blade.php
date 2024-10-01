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
                    <h4 class="mb-sm-0 font-size-18"> Add Purchase</h4>
                   
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase</a></li>
                            <li class="breadcrumb-item active"> Add Purchase Order {{ $findtype->type_product }}</li>
                        </ol>
                    </div>
                </div>
                <a href="/purchase" class="btn btn-info waves-effect waves-light">Back To List Data Purchase Order</a>
                <div></div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add Purchase Order</h4>
                        <!--  <p class="card-title-desc"> layout options : from inline, horizontal & custom grid implementations</p> -->
                    </div>
                    <div class="card-body p-4">

                    <div class="col-sm-12">
                            <div class="mt-4 mt-lg-0">
                                
                    
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Request Number</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="request_number" class="form-control" value="{{ $request_number->request_number }}" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="row left-content-end">
                                </div>
                                    
                        
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <form method="post" action="/simpan_detail_po/{{ $reference_number }}/{{ $id }}" class="form-material m-t-40" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Purchase Order Detail</h4>
                        <!--  <p class="card-title-desc"> layout options : from inline, horizontal & custom grid implementations</p> -->
                    </div>
                    <div class="card-body p-4">

                    <div class="col-sm-12">
                            <div class="mt-4 mt-lg-0">
                                
                               
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Type Product</label>
                                        <div class="col-sm-9">
                                            <input type="radio" id="html" name="type" value="{{ $findtype->type_product }}" checked>
                                            <input type="hidden" id="html" name="type_product" value="{{ $findtype->type_product }}" checked>
                                            <input type="hidden" name="id_pr" class="form-control" value="{{ $reference_number }}" readonly>
                                            <input type="hidden" name="id_po" class="form-control" value="{{ $id }}" readonly>
                                              <label for="html">{{ $findtype->type_product }}</label>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-email-input" class="col-sm-3 col-form-label">Product {{ $findtype->type_product }}</label>
                                        <div class="col-sm-9">
                                        @if($findtype->type_product=='RM')
                                            <select class="form-select request_number" name="description" id="" onchange="get_unit_smt()">
                                                    <option>Pilih Product RM</option>
                                                @foreach ($rawMaterials as $data)
                                                    <option value="{{ $data->id }}" data-id="{{ $data->id }}">{{ $data->description }}</option>
                                                @endforeach
                                            </select>
                                            @error('description')
                                            <div class="form-group has-danger mb-0">
                                                <div class="form-control-feedback">{{ $message }}</div>
                                            </div>
                                            @enderror
                                        @elseif($findtype->type_product=='FG')
                                            <select class="form-select request_number" name="description" id="" onchange="get_unit_smt()">
                                                    <option>Pilih Product FG</option>
                                                @foreach ($fg as $data)
                                                    <option value="{{ $data->id }}" data-id="{{ $data->id }}">{{ $data->description }} || {{ $data->perforasi }}</option>
                                                @endforeach
                                            </select>
                                            @error('description')
                                            <div class="form-group has-danger mb-0">
                                                <div class="form-control-feedback">{{ $message }}</div>
                                            </div>
                                            @enderror
                                        @elseif($findtype->type_product=='WIP')
                                            <select class="form-select request_number" name="description" id="" onchange="get_unit_smt()">
                                                    <option>Pilih Product WIP</option>
                                                @foreach ($wip as $data)
                                                    <option value="{{ $data->id }}" data-id="{{ $data->id }}">{{ $data->description }}</option>
                                                @endforeach
                                            </select>
                                            @error('description')
                                            <div class="form-group has-danger mb-0">
                                                <div class="form-control-feedback">{{ $message }}</div>
                                            </div>
                                            @enderror
                                        @elseif($findtype->type_product=='TA')
                                            <select class="form-select request_number" name="description" id="" onchange="get_unit_smt()">
                                                    <option>Pilih Product TA</option>
                                                @foreach ($ta as $data)
                                                    <option value="{{ $data->id }}" data-id="{{ $data->id }}">{{ $data->description }}</option>
                                                @endforeach
                                            </select>
                                            @error('description')
                                            <div class="form-group has-danger mb-0">
                                                <div class="form-control-feedback">{{ $message }}</div>
                                            </div>
                                            @enderror
                                        @elseif($findtype->type_product=='Other')
                                        <select class="form-select request_number" name="description" id="" onchange="get_unit_smt()">
                                                <option>Pilih Product Other</option>
                                            @foreach ($other as $data)
                                                <option value="{{ $data->id }}" data-id="{{ $data->id }}">{{ $data->description }}</option>
                                            @endforeach
                                        </select>
                                            @error('description')
                                            <div class="form-group has-danger mb-0">
                                                <div class="form-control-feedback">{{ $message }}</div>
                                            </div>
                                            @enderror
                                        @endif
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Qty</label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" name="qty" id="qty">
                                        </div>
                                        @error('qty')
                                            <div class="form-group has-danger mb-0">
                                                <div class="form-control-feedback">{{ $message }}</div>
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Units </label>
                                        <div class="col-sm-9">
                                            <select class="form-select" name="unit" id="unit_code">
                                                <option>Pilih Unit</option>
                                                @foreach ($units as $data)
                                                <option value="{{ $data->unit }}">{{ $data->unit_code }}</option>
                                                @endforeach
                                            </select>
                                            @error('unit')
                                            <div class="form-group has-danger mb-0">
                                                <div class="form-control-feedback">{{ $message }}</div>
                                            </div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Currency</label>
                                    <div class="col-sm-9">
                                        <input type="radio" id="USD" name="currency" value="USD">
                                        <label for="USD">USD</label>
                                        <input type="radio" id="IDR" name="currency" value="IDR">
                                        <label for="IDR">IDR</label>
                                    </div>
                                    @error('currency')
                                            <div class="form-group has-danger mb-0">
                                                <div class="form-control-feedback">{{ $message }}</div>
                                            </div>
                                    @enderror
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Price </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="price" id="price">
                                        </div>
                                        @error('price')
                                            <div class="form-group has-danger mb-0">
                                                <div class="form-control-feedback">{{ $message }}</div>
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Discount </label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" name="discount" id="discount">
                                        </div>
                                        @error('discount')
                                            <div class="form-group has-danger mb-0">
                                                <div class="form-control-feedback">{{ $message }}</div>
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tax</label>
                                        <div class="col-sm-9">
                                            <input type="radio" id="tax_Y" name="tax" value="Y" checked>
                                            <label for="tax_Y">Y</label>
                                            <input type="radio" id="tax_N" name="tax" value="N">
                                            <label for="tax_N">N</label>
                                        </div>
                                        @error('tax')
                                            <div class="form-group has-danger mb-0">
                                                <div class="form-control-feedback">{{ $message }}</div>
                                            </div>
                                        @enderror
                                    </div>
                                    <style>
                                        .custom-bg-gray {
                                            background-color: #c4c4c4; /* Warna abu-abu yang lebih terang */
                                        }
                                    </style>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Amount </label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control custom-bg-gray" name="amount" id="amount">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Total Amount </label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control custom-bg-gray" name="total_amount" id="total_amount" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Note </label>
                                        <div class="col-sm-9">
                                            <textarea name="note" rows="4" cols="50" class="form-control"></textarea>
                                        </div>
                                    </div>

                                    <div class="row justify-content-end">
                                        <div class="col-sm-9">
                                            <div>
                                                <button type="reset" class="btn btn-info w-md">Reset</button>
                                                <button type="submit" class="btn btn-primary w-md">Add To Table</button>
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

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Table Detail</h4>
                        <!--  <p class="card-title-desc"> layout options : from inline, horizontal & custom grid implementations</p> -->
                    </div>
                    <div class="card-body p-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                <thead>
                                    <tr>
                                    <tr>
                                        <th>Type Product</th>
                                        <th>Product WIP</th>
                                        <th>Qty</th>
                                        <th>Units</th>
                                        <th>Price</th>
                                        <th>Discount</th>
                                        <th>Tax</th>
                                        <th>Amount</th>
                                        <th>Note</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if($findtype->type_product=='RM')    
                                    @foreach ($POSmt as $data)
                                            <tr>
                                                <td>{{ $data->type_product }}</td>
                                                <td>{{ $data->raw_material_description }}</td>
                                                <td>{{ $data->qty }}</td>
                                                <td>{{ $data->unit }}</td>
                                                <td>{{ $data->price }}</td>
                                                <td>{{ $data->discount }}</td>
                                                <td>{{ $data->tax }}</td>
                                                <td>{{ $data->amount }}</td>
                                                <td>{{ $data->note }}</td>
                                                <td>
                                        
                                                        <form action="/hapus_po_detail/{{ $data->id }}/{{ $id }}" method="post"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Anda yakin mau menghapus item ini ?')">
                                                                <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                            </button>
                                                        </form>
                                                        <!-- <button type="button" class="btn btn-sm btn-info " id=""
                                                            data-bs-toggle="modal"
                                                            onclick="edit_pr_smt('{{ $data->id }}')"
                                                            data-bs-target="#edit-pr-smt" data-id="">
                                                            <i class="bx bx-edit-alt" title="edit data"></i>
                                                        </button></center> -->
                                                        <a href="/edit-po-item-smt/{{ $data->id; }}">
                                                        <button type="button" class="btn btn-sm btn-info"><i class="bx bx-edit-alt" title="edit data"></i></button>
                                                </a>
                                                    </td>
                                                        @include('purchase.modal')
                                                
                                            </tr>
                                        <!-- Add more rows as needed -->
                                        @endforeach
                                @elseif($findtype->type_product=='TA')    
                                        @foreach ($POSmtTA as $data)
                                        <tr>
                                            <td>{{ $data->type_product }}</td>
                                            <td>{{ $data->raw_material_description }}</td>
                                            <td>{{ $data->qty }}</td>
                                            <td>{{ $data->unit }}</td>
                                            <td>{{ $data->price }}</td>
                                            <td>{{ $data->discount }}</td>
                                            <td>{{ $data->tax }}</td>
                                            <td>{{ $data->amount }}</td>
                                            <td>{{ $data->note }}</td>
                                            <td>
                                    
                                                    <form action="/hapus_po_detail/{{ $data->id }}/{{ $id }}" method="post"
                                                        class="d-inline">
                                                        @method('delete')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Anda yakin mau menghapus item ini ?')">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button>
                                                    </form>
                                                    <!-- <button type="button" class="btn btn-sm btn-info " id=""
                                                        data-bs-toggle="modal"
                                                        onclick="edit_pr_smt('{{ $data->id }}')"
                                                        data-bs-target="#edit-pr-smt" data-id="">
                                                        <i class="bx bx-edit-alt" title="edit data"></i>
                                                    </button></center> -->
                                                    <a href="/edit-po-item-smt/{{ $data->id; }}">
                                                        <button type="button" class="btn btn-sm btn-info"><i class="bx bx-edit-alt" title="edit data"></i></button>
                                                </a>
                                                </td>
                                                    @include('purchase.modal')
                                            
                                        </tr>
                                        <!-- Add more rows as needed -->
                                        @endforeach
                                    @elseif($findtype->type_product=='WIP')    
                                        @foreach ($POSmtwip as $data)
                                        <tr>
                                            <td>{{ $data->type_product }}</td>
                                            <td>{{ $data->raw_material_description }}</td>
                                            <td>{{ $data->qty }}</td>
                                            <td>{{ $data->unit }}</td>
                                            <td>{{ $data->price }}</td>
                                            <td>{{ $data->discount }}</td>
                                            <td>{{ $data->tax }}</td>
                                            <td>{{ $data->amount }}</td>
                                            <td>{{ $data->note }}</td>
                                            <td>
                                    
                                                    <form action="/hapus_po_detail/{{ $data->id }}/{{ $id }}" method="post"
                                                        class="d-inline">
                                                        @method('delete')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Anda yakin mau menghapus item ini ?')">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button>
                                                    </form>
                                                    <!-- <button type="button" class="btn btn-sm btn-info " id=""
                                                        data-bs-toggle="modal"
                                                        onclick="edit_pr_smt('{{ $data->id }}')"
                                                        data-bs-target="#edit-pr-smt" data-id="">
                                                        <i class="bx bx-edit-alt" title="edit data"></i>
                                                    </button></center> -->
                                                    <a href="/edit-po-item-smt/{{ $data->id; }}">
                                                        <button type="button" class="btn btn-sm btn-info"><i class="bx bx-edit-alt" title="edit data"></i></button>
                                                </a>
                                                </td>
                                                    @include('purchase.modal')
                                            
                                        </tr>
                                    <!-- Add more rows as needed -->
                                        @endforeach
                                    @elseif($findtype->type_product=='FG')    
                                        @foreach ($POSmtfg as $data)
                                        <tr>
                                            <td>{{ $data->type_product }}</td>
                                            <td>{{ $data->raw_material_description }}</td>
                                            <td>{{ $data->qty }}</td>
                                            <td>{{ $data->unit }}</td>
                                            <td>{{ $data->price }}</td>
                                            <td>{{ $data->discount }}</td>
                                            <td>{{ $data->tax }}</td>
                                            <td>{{ $data->amount }}</td>
                                            <td>{{ $data->note }}</td>
                                            <td>
                                    
                                                    <form action="/hapus_po_detail/{{ $data->id }}/{{ $id }}" method="post"
                                                        class="d-inline">
                                                        @method('delete')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Anda yakin mau menghapus item ini ?')">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button>
                                                    </form>
                                                    <!-- <button type="button" class="btn btn-sm btn-info " id=""
                                                        data-bs-toggle="modal"
                                                        onclick="edit_pr_smt('{{ $data->id }}')"
                                                        data-bs-target="#edit-pr-smt" data-id="">
                                                        <i class="bx bx-edit-alt" title="edit data"></i>
                                                    </button></center> -->
                                                    <a href="/edit-po-item-smt/{{ $data->id; }}">
                                                        <button type="button" class="btn btn-sm btn-info"><i class="bx bx-edit-alt" title="edit data"></i></button>
                                                </a>
                                                </td>
                                                    @include('purchase.modal')
                                            
                                        </tr>
                                    <!-- Add more rows as needed -->
                                        @endforeach
                                        @elseif($findtype->type_product=='Other')    
                                        @foreach ($POSmtother as $data)
                                        <tr>
                                            <td>{{ $data->type_product }}</td>
                                            <td>{{ $data->raw_material_description }}</td>
                                            <td>{{ $data->qty }}</td>
                                            <td>{{ $data->unit }}</td>
                                            <td>{{ $data->price }}</td>
                                            <td>{{ $data->discount }}</td>
                                            <td>{{ $data->tax }}</td>
                                            <td>{{ $data->amount }}</td>
                                            <td>{{ $data->note }}</td>
                                            <td>
                                    
                                                    <form action="/hapus_po_detail/{{ $data->id }}/{{ $id }}" method="post"
                                                        class="d-inline">
                                                        @method('delete')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Anda yakin mau menghapus item ini ?')">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button>
                                                    </form>
                                                    <!-- <button type="button" class="btn btn-sm btn-info " id=""
                                                        data-bs-toggle="modal"
                                                        onclick="edit_po_detail_smt('{{ $data->id }}')"
                                                        data-bs-target="#edit-po-detail-smt" data-id="">
                                                        <i class="bx bx-edit-alt" title="edit data"></i>
                                                    </button></center> -->
                                                    <a href="/edit-po-item-smt/{{ $data->id; }}">
                                                        <button type="button" class="btn btn-sm btn-info"><i class="bx bx-edit-alt" title="edit data"></i></button>
                                                </a>
                                                </td>
                                                    @include('purchase.modal')
                                            
                                        </tr>
                                    <!-- Add more rows as needed -->
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row left-content-end">
                        <div class="col-sm-9">
                            <div>
                                <a href="/purchase-order" class="btn btn-info w-md">Back</a>
                                <form action="/simpan_detail_po_fix/{{ $id }}/{{ $reference_number }}" method="post"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-md"
                                    onclick="return confirm('Anda yakin mau simpan Purchase Requisition Detail ?')">Simpan Detail
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    
                    <!-- end row -->
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

    // Tambahkan event listener untuk menghitung jumlah saat nilai berubah
    [qtyInput, priceInput, discountInput].forEach(input => {
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
        const tax = isTaxed ? (amount * 11) / 100 : 0;

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