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
                    <h4 class="mb-sm-0 font-size-18"> Edit Purchase Order</h4>
                   
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase</a></li>
                            <li class="breadcrumb-item active"> Edit Purchase Order {{ $results[0]->type; }}</li>
                        </ol>
                    </div>
                </div>
                <a href="/purchase-order" class="btn btn-info waves-effect waves-light">Back To List Data Purchase Order</a>
                <div></div>
            </div>
        </div>
        <form method="post" action="/update_po/{{ $results[0]->id; }}" class="form-material m-t-40" enctype="multipart/form-data">
            @method('PUT')
            @csrf
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Edit Purchase Order</h4>
                        <!--  <p class="card-title-desc"> layout options : from inline, horizontal & custom grid implementations</p> -->
                    </div>
                    <div class="card-body p-4">

                    <div class="col-sm-12">
                            <div class="mt-4 mt-lg-0">
                                
                            
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Po Number</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="po_number" class="form-control" value="{{ $results[0]->po_number; }}" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-email-input" class="col-sm-3 col-form-label">Date</label>
                                        <div class="col-sm-9">
                                            <input type="date" name="date" class="form-control" value="{{ $results[0]->date; }}">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Reference Number (PR) </label>
                                        <div class="col-sm-9">
                                        <select class="form-select" name="reference_number" id="">
                                            <option value="">Pilih Reference Number</option>
                                            @foreach ($reference_number as $data)
                                            <option value="{{ $data->id }}" {{ $data->id == $selectedId ? 'selected' : '' }}>{{ $data->request_number }}</option>
                                            @endforeach
                                        </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Supplier </label>
                                        <div class="col-sm-9">
                                        <select class="form-select" name="id_master_suppliers" id="">
                                            <option value="">Pilih Suppliers</option>
                                            @foreach ($supplier as $data)
                                            <option value="{{ $data->id }}" {{ $data->id == $selectedsupplier ? 'selected' : '' }}>{{ $data->name }}</option>
                                            @endforeach
                                        </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Qc Check </label>
                                        <div class="col-sm-9">
                                            <input type="radio" id="qc_check_Y" name="qc_check" value="Y" {{ $radioselectted == 'Y' ? 'checked' : '' }}>
                                            <label for="qc_check_Y">Y</label>
                                            <input type="radio" id="qc_check_N" name="qc_check" value="N" {{ $radioselectted == 'N' ? 'checked' : '' }}>
                                            <label for="qc_check_N">N</label>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Down Payment </label>
                                        <div class="col-sm-9">
                                        <input type="text" class="form-control" name="down_payment" value="{{ $results[0]->down_payment; }}">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Own Remarks </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="own_remarks" value="{{ $results[0]->own_remarks; }}" >
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Supplier Remarks </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="supplier_remarks" value="{{ $results[0]->supplier_remarks; }}" >
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Status </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="status" value="{{ $results[0]->status; }}" >
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Type </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="type" value="{{ $results[0]->type; }}" readonly>
                                        </div>
                                    </div>
                                    <div class="row justify-content-end">
                                        <div class="col-sm-9">
                                            <div>
                                                <!-- <button type="reset" class="btn btn-info w-md">Reset</button> -->
                                                <button type="submit" class="btn btn-primary w-md">Update</button>
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
        <form method="post" action="/update_detail_po/{{ $results[0]->id; }}" class="form-material m-t-40" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Purchase Requisition Detail</h4>
                        <!--  <p class="card-title-desc"> layout options : from inline, horizontal & custom grid implementations</p> -->
                    </div>
                    <div class="card-body p-4">

                    <div class="col-sm-12">
                            <div class="mt-4 mt-lg-0">
                                
                               
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Type Product</label>
                                        <div class="col-sm-9">
                                            <input type="radio" id="html" name="type_product" value="{{ $results[0]->type }}" checked>
                                
                                              <label for="html">{{ $results[0]->type; }}</label>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-email-input" class="col-sm-3 col-form-label">Product {{ $results[0]->type; }}</label>
                                        <div class="col-sm-9">
                                            @if($results[0]->type=='RM')
                                            <select class="form-select" name="master_products_id" id="">
                                                    <option>Pilih Product RM</option>
                                                @foreach ($rawMaterials as $data)
                                                    <option value="{{ $data->id }}">{{ $data->description }}</option>
                                                @endforeach
                                            </select>
                                            @elseif($results[0]->type=='WIP')
                                            <select class="form-select" name="master_products_id" id="">
                                                    <option>Pilih Product WIP</option>
                                                @foreach ($wip as $data)
                                                    <option value="{{ $data->id }}">{{ $data->description }}</option>
                                                @endforeach
                                            </select>
                                            @elseif($results[0]->type=='FG')
                                            <select class="form-select" name="master_products_id" id="">
                                                    <option value="{{ $data->id }}">Pilih Product FG</option>
                                                @foreach ($fg as $data)
                                                    <option>{{ $data->description }}</option>
                                                @endforeach
                                            </select>
                                            @elseif($results[0]->type=='TA')
                                            <select class="form-select" name="product" id="">
                                                    <option value="{{ $data->id }}">Pilih Product Sparepart & Auxiliaries</option>
                                                @foreach ($ta as $data)
                                                    <option>{{ $data->description }}</option>
                                                @endforeach
                                            </select>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Qty</label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" name="qty" id="qty" value="old('qty') }}">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Units </label>
                                        <div class="col-sm-9">
                                            <select class="form-select" name="master_units_id" id="">
                                                <option>Pilih Units</option>
                                                @foreach ($units as $data)
                                                    <option value="{{ $data->id }}">{{ $data->unit_code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Price </label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" name="price" id="price" value="{{ old('price') }}">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Discount </label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" name="discount" id="discount" value="{{ old('discount') }}">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tax</label>
                                        <div class="col-sm-9">
                                        <input type="radio" id="tax_Y" name="tax" value="Y" checked>
                                            <label for="tax_Y">Y</label>
                                            <input type="radio" id="tax_N" name="tax" value="N" >
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
                                            <input type="number" class="form-control custom-bg-gray" name="amount" id="amount">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Note</label>
                                        <div class="col-sm-9">
                                        <textarea name="note" rows="4" cols="50" class="form-control">{{ old('note') }}</textarea>
                                        </div>
                                    </div>

                                    <div class="row justify-content-end">
                                        <div class="col-sm-9">
                                            <div>
                                                <button type="reset" class="btn btn-info w-md">Reset</button>
                                                <button type="submit" class="btn btn-primary w-md" name="save_detail">Add To Table</button>
                                            </div>
                                        </div>
                                    </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                                @if($results[0]->type=='RM')
                                    @foreach ($data_detail_rm as $data)
                                                <tr>
                                                    <td>{{ $data->type_product }}</td>
                                                    <td>{{ $data->description }}</td>
                                                    <td>{{ $data->qty }}</td>
                                                    <td>{{ $data->unit }}</td>
                                                    <td>{{ $data->price }}</td>
                                                    <td>{{ $data->discount }}</td>
                                                    <td>{{ $data->tax }}</td>
                                                    <td>{{ $data->amount }}</td>
                                                    <td>{{ $data->note }}</td>
                                                    <td>
                                                        <button type="submit" class="btn btn-sm btn-danger" name="hapus_detail" value="{{ $data->id }}">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-info " id=""
                                                            data-bs-toggle="modal"
                                                            onclick="edit_po_detail('{{ $data->id }}')"
                                                            data-bs-target="#edit-po-detail" data-id="">
                                                            <i class="bx bx-edit-alt" title="edit data"></i>
                                                        </button></center>
                                                        @include('purchase.modal')
                                                            </td>
                                                    
                                                </tr>
                                            <!-- Add more rows as needed -->
                                    @endforeach
                                @elseif($results[0]->type=='WIP')
                                    @foreach ($data_detail_wip as $data)
                                                <tr>
                                                    <td>{{ $data->type_product }}</td>
                                                    <td>{{ $data->description }}</td>
                                                    <td>{{ $data->qty }}</td>
                                                    <td>{{ $data->unit }}</td>
                                                    <td>{{ $data->price }}</td>
                                                    <td>{{ $data->discount }}</td>
                                                    <td>{{ $data->tax }}</td>
                                                    <td>{{ $data->amount }}</td>
                                                    <td>{{ $data->note }}</td>
                                                    <td>
                                                        <button type="submit" class="btn btn-sm btn-danger" name="hapus_detail" value="{{ $data->id }}">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-info " id=""
                                                            data-bs-toggle="modal"
                                                            onclick="edit_po_detail('{{ $data->id }}')"
                                                            data-bs-target="#edit-po-detail" data-id="">
                                                            <i class="bx bx-edit-alt" title="edit data"></i>
                                                        </button></center>
                                                        @include('purchase.modal')
                                                            </td>
                                                    
                                                </tr>
                                            <!-- Add more rows as needed -->
                                    @endforeach
                                @elseif($results[0]->type=='FG')
                                    @foreach ($data_detail_fg as $data)
                                                <tr>
                                                    <td>{{ $data->type_product }}</td>
                                                    <td>{{ $data->description }}</td>
                                                    <td>{{ $data->qty }}</td>
                                                    <td>{{ $data->unit }}</td>
                                                    <td>{{ $data->price }}</td>
                                                    <td>{{ $data->discount }}</td>
                                                    <td>{{ $data->tax }}</td>
                                                    <td>{{ $data->amount }}</td>
                                                    <td>{{ $data->note }}</td>
                                                    <td>
                                                        <button type="submit" class="btn btn-sm btn-danger" name="hapus_detail" value="{{ $data->id }}">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-info " id=""
                                                            data-bs-toggle="modal"
                                                            onclick="edit_po_detail('{{ $data->id }}')"
                                                            data-bs-target="#edit-po-detail" data-id="">
                                                            <i class="bx bx-edit-alt" title="edit data"></i>
                                                        </button></center>
                                                        @include('purchase.modal')
                                                            </td>
                                                    
                                                </tr>
                                            <!-- Add more rows as needed -->
                                    @endforeach
                                @elseif($results[0]->type=='TA')
                                    @foreach ($data_detail_ta as $data)
                                                <tr>
                                                    <td>{{ $data->type_product }}</td>
                                                    <td>{{ $data->description }}</td>
                                                    <td>{{ $data->qty }}</td>
                                                    <td>{{ $data->unit }}</td>
                                                    <td>{{ $data->price }}</td>
                                                    <td>{{ $data->discount }}</td>
                                                    <td>{{ $data->tax }}</td>
                                                    <td>{{ $data->amount }}</td>
                                                    <td>{{ $data->note }}</td>
                                                    <td>
                                                        <button type="submit" class="btn btn-sm btn-danger" name="hapus_detail" value="{{ $data->id }}">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-info " id=""
                                                            data-bs-toggle="modal"
                                                            onclick="edit_po_detail('{{ $data->id }}')"
                                                            data-bs-target="#edit-po-detail" data-id="">
                                                            <i class="bx bx-edit-alt" title="edit data"></i>
                                                        </button></center>
                                                        @include('purchase.modal')
                                                            </td>
                                                    
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
    </form>
                    <!-- end row -->
    </div>
</div>

<script>
    // Ambil elemen input
    const qtyInput = document.getElementById('qty');
    const priceInput = document.getElementById('price');
    const discountInput = document.getElementById('discount');
    const amountInput = document.getElementById('amount');

    // Tambahkan event listener untuk menghitung jumlah saat nilai berubah
    [qtyInput, priceInput, discountInput].forEach(input => {
        input.addEventListener('input', calculateAmount);
    });

    // Fungsi untuk menghitung jumlah
    function calculateAmount() {
        const qty = parseFloat(qtyInput.value);
        const price = parseFloat(priceInput.value);
        const discount = parseFloat(discountInput.value);

        // Hitung jumlah diskon dalam persen
        const discountAmount = (price * discount) / 100;

        // Hitung jumlah
        const amount = (qty * price) - discountAmount;

         // Masukkan hasil perhitungan ke dalam input amount dengan format ribuan
         amountInput.value = isNaN(amount) ? '' : amount.toLocaleString('id-ID');
    }
</script>
@endsection