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
                            <li class="breadcrumb-item active"> Add Purchase RM</li>
                        </ol>
                    </div>
                </div>
                <a href="/purchase" class="btn btn-info waves-effect waves-light">Back To List Data Purchase Requisition</a>
                <div></div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add Purchase Requisition</h4>
                        <!--  <p class="card-title-desc"> layout options : from inline, horizontal & custom grid implementations</p> -->
                    </div>
                    <div class="card-body p-4">

                    <div class="col-sm-12">
                            <div class="mt-4 mt-lg-0">
                                
                    
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Request Number</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="request_number" class="form-control" value="{{ $reference_number }}" readonly>
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
                        <h4 class="card-title">Purchase Requisition Detail</h4>
                        <!--  <p class="card-title-desc"> layout options : from inline, horizontal & custom grid implementations</p> -->
                    </div>
                    <div class="card-body p-4">

                    <div class="col-sm-12">
                            <div class="mt-4 mt-lg-0">
                                
                               
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Type Product</label>
                                        <div class="col-sm-9">
                                            <input type="radio" id="html" name="type" value="RM" checked>
                                            <input type="hidden" id="html" name="type_product" value="RM" checked>
                                            <input type="hidden" name="id_pr" class="form-control" value="{{ $reference_number }}" readonly>
                                              <label for="html">RM</label>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-email-input" class="col-sm-3 col-form-label">Product RM</label>
                                        <div class="col-sm-9">
                                            <select class="form-select" name="description" id="">
                                                    <option>Pilih Product RM</option>
                                                @foreach ($rawMaterials as $data)
                                                    <option value="{{ $data->description }}">{{ $data->description }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Qty</label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" name="qty" id="qty">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Units </label>
                                        <div class="col-sm-9">
                                            <select class="form-select" name="unit" id="">
                                                <option>Pilih Unit</option>
                                                @foreach ($units as $data)
                                                <option value="{{ $data->unit_code }}" @if ($data->unit_code === "KG") selected @endif>{{ $data->unit_code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Price </label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" name="price" id="price">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Discount </label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" name="discount" id="discount">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tax</label>
                                        <div class="col-sm-9">
                                            <input type="radio" id="html" name="tax" value="Y" checked>
                                              <label for="html">Y</label>
                                              <input type="radio" id="css" name="tax" value="N">
                                              <label for="css">N</label>
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
                                @foreach ($POSmt as $data)
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
                                    
                                                    <form action="/hapus_po_detail/{{ $data->id }}/{{ $id }}" method="post"
                                                        class="d-inline">
                                                        @method('delete')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Anda yakin mau menghapus item ini ?')">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-info " id=""
                                                        data-bs-toggle="modal"
                                                        onclick="edit_pr_smt('{{ $data->id }}')"
                                                        data-bs-target="#edit-pr-smt" data-id="">
                                                        <i class="bx bx-edit-alt" title="edit data"></i>
                                                    </button></center></td>
                                                    @include('purchase.modal')
                                            
                                        </tr>
                                    <!-- Add more rows as needed -->
                                    @endforeach
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

    // Tambahkan event listener untuk menghitung jumlah saat nilai berubah
    [qtyInput, priceInput, discountInput].forEach(input => {
        input.addEventListener('input', calculateAmount);
    });

    // Fungsi untuk menghitung jumlah
    function calculateAmount() {
        const qty = parseFloat(qtyInput.value);
        const price = parseFloat(priceInput.value);
        const discount = parseFloat(discountInput.value);

        // Hitung jumlah
        const amount = (qty * price) - discount;

        // Masukkan hasil perhitungan ke dalam input amount
        amountInput.value = isNaN(amount) ? '' : amount.toFixed(2);
    }
</script>
@endsection