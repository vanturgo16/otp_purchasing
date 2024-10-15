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
                                <div class="row mb-4 field-wrapper required-field">
                                    <label for="horizontal-email-input" class="col-sm-3 col-form-label">Delivery Date</label>
                                    <div class="col-sm-9">
                                        <input type="date" name="delivery_date" class="form-control" value="{{ $results[0]->delivery_date; }}">
                                    </div>
                                </div>
                                <div class="row mb-4 field-wrapper">
                                    <label for="horizontal-password-input" class="col-sm-3 col-form-label">Reference Number (PR) </label>
                                    <div class="col-sm-9">
                                        <select class="form-select data-select2" name="reference_number" id="">
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
                                        <select class="form-select data-select2" name="id_master_suppliers" id="">
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
                                        <select class="form-select request_number data-select2" name="master_products_id" id="" onchange="get_unit()">
                                                <option>Pilih Product RM</option>
                                            @foreach ($rawMaterials as $data)
                                                <option value="{{ $data->id }}" data-id="{{ $data->id }}">{{ $data->description }}</option>
                                            @endforeach
                                        </select>
                                        @error('master_products_id')
                                            <div class="form-group has-danger mb-0">
                                                <div class="form-control-feedback">{{ $message }}</div>
                                            </div>
                                        @enderror
                                        @elseif($results[0]->type=='WIP')
                                        <select class="form-select request_number data-select2" name="master_products_id" id="" onchange="get_unit()">
                                                <option value="">Pilih Product WIP</option>
                                            @foreach ($wip as $data)
                                                <option value="{{ $data->id }}" data-id="{{ $data->id }}">{{ $data->description }}</option>
                                            @endforeach
                                        </select>
                                        @error('master_products_id')
                                            <div class="form-group has-danger mb-0">
                                                <div class="form-control-feedback">{{ $message }}</div>
                                            </div>
                                        @enderror
                                        @elseif($results[0]->type=='FG')
                                        <select class="form-select request_number data-select2" name="master_products_id" id="" onchange="get_unit()">
                                                <option value="">Pilih Product FG</option>
                                            @foreach ($fg as $data)
                                                <option value="{{ $data->id }}" data-id="{{ $data->id }}">{{ $data->description }} || {{ $data->perforasi }}</option>
                                            @endforeach
                                        </select>
                                        @error('master_products_id')
                                            <div class="form-group has-danger mb-0">
                                                <div class="form-control-feedback">{{ $message }}</div>
                                            </div>
                                        @enderror
                                        @elseif($results[0]->type=='TA')
                                        <select class="form-select request_number data-select2" name="master_products_id" id="" onchange="get_unit()">
                                                <option value="">Pilih Product Sparepart & Auxiliaries</option>
                                            @foreach ($ta as $data)
                                                <option value="{{ $data->id }}" data-id="{{ $data->id }}">{{ $data->description }}</option>
                                            @endforeach
                                        </select>
                                        @error('master_products_id')
                                            <div class="form-group has-danger mb-0">
                                                <div class="form-control-feedback">{{ $message }}</div>
                                            </div>
                                        @enderror
                                        @elseif($results[0]->type=='Other')
                                        <select class="form-select request_number data-select2" name="master_products_id" id="" onchange="get_unit()">
                                                <option value="">Pilih Product Other</option>
                                            @foreach ($other as $data)
                                                <option value="{{ $data->id }}" data-id="{{ $data->id }}">{{ $data->description }}</option>
                                            @endforeach
                                        </select>
                                        @error('master_products_id')
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
                                        <input type="number" class="form-control" name="qty" id="qty" value="{{ old('qty') }}">
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
                                        <select class="form-select data-select2" name="master_units_id" id="unit_code">
                                            <option>Pilih Units</option>
                                            @foreach ($units as $data)
                                                <option value="{{ $data->id }}">{{ $data->unit_code }}</option>
                                            @endforeach
                                        </select>
                                        @error('master_units_id')
                                            <div class="form-group has-danger mb-0">
                                                <div class="form-control-feedback">{{ $message }}</div>
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-4 field-wrapper">
                                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Currency</label>
                                    <div class="col-sm-9">
                                            <select class="form-select" name="currency">
                                                <option value="-">Select Currency</option>
                                                @foreach ($currency as $data)
                                                <option value="{{ $data->currency_code }}">{{ $data->currency_code }}</option>
                                                @endforeach
                                            </select>
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
                                        <input type="text" class="form-control" name="price" id="price" value="{{ old('price') }}">
                                        <input type="hidden" name="currency_value" id="currency_value"> <!-- Hidden field untuk mata uang -->
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
                                        <input type="number" class="form-control" name="discount" id="discount" value="{{ old('discount') }}">
                                    </div>
                                    @error('discount')
                                            <div class="form-group has-danger mb-0">
                                                <div class="form-control-feedback">{{ $message }}</div>
                                            </div>
                                    @enderror
                                </div>
                                <div class="row mb-4 field-wrapper required-field">
                                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tax Rate (%)</label>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control" name="tax_rate" id="tax_rate" value="11">
                                    </div>
                                    @error('tax_rate')
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
                                        <input type="number" class="form-control custom-bg-gray" name="amount" id="amount" readonly>
                                    </div>
                                </div>
                                <div class="row mb-4 field-wrapper required-field">
                                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Total Amount </label>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control custom-bg-gray" name="total_amount" id="total_amount" readonly>
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
                    </div>
                    <div class="card-body p-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Type Product</th>
                                        <th>Product</th>
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
                                                <!-- <button type="button" class="btn btn-sm btn-info " id=""
                                                    data-bs-toggle="modal"
                                                    onclick="edit_po_detail('{{ $data->id }}')"
                                                    data-bs-target="#edit-po-detail" data-id="">
                                                    <i class="bx bx-edit-alt" title="edit data"></i>
                                                </button> -->
                                                <a href="/edit-po-item/{{ $data->id; }}">
                                                    <button type="button" class="btn btn-sm btn-info"><i class="bx bx-edit-alt" title="edit data"></i></button>
                                                </a>

                                                @include('purchase.modal')
                                            </td>
                                        </tr>
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
                                                <!-- <button type="button" class="btn btn-sm btn-info " id=""
                                                    data-bs-toggle="modal"
                                                    onclick="edit_po_detail('{{ $data->id }}')"
                                                    data-bs-target="#edit-po-detail" data-id="">
                                                    <i class="bx bx-edit-alt" title="edit data"></i>
                                                </button> -->
                                                <a href="/edit-po-item/{{ $data->id; }}">
                                                    <button type="button" class="btn btn-sm btn-info"><i class="bx bx-edit-alt" title="edit data"></i></button>
                                                </a>
                                                @include('purchase.modal')
                                            </td>
                                        </tr>
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
                                                <!-- <button type="button" class="btn btn-sm btn-info " id=""
                                                    data-bs-toggle="modal"
                                                    onclick="edit_po_detail('{{ $data->id }}')"
                                                    data-bs-target="#edit-po-detail" data-id="">
                                                    <i class="bx bx-edit-alt" title="edit data"></i>
                                                </button> -->
                                                <a href="/edit-po-item/{{ $data->id; }}">
                                                    <button type="button" class="btn btn-sm btn-info"><i class="bx bx-edit-alt" title="edit data"></i></button>
                                                </a>
                                                @include('purchase.modal')
                                            </td>
                                        </tr>
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
                                                <!-- <button type="button" class="btn btn-sm btn-info " id=""
                                                    data-bs-toggle="modal"
                                                    onclick="edit_po_detail('{{ $data->id }}')"
                                                    data-bs-target="#edit-po-detail" data-id="">
                                                    <i class="bx bx-edit-alt" title="edit data"></i>
                                                </button> -->
                                                <a href="/edit-po-item/{{ $data->id; }}">
                                                    <button type="button" class="btn btn-sm btn-info"><i class="bx bx-edit-alt" title="edit data"></i></button>
                                                </a>
                                                @include('purchase.modal')
                                            </td>
                                        </tr>
                                    @endforeach
                                    @elseif($results[0]->type=='Other')
                                    @foreach ($data_detail_other as $data)
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
                                                <!-- <button type="button" class="btn btn-sm btn-info " id=""
                                                    data-bs-toggle="modal"
                                                    onclick="edit_po_detail('{{ $data->id }}')"
                                                    data-bs-target="#edit-po-detail" data-id="">
                                                    <i class="bx bx-edit-alt" title="edit data"></i>
                                                </button> -->
                                                <a href="/edit-po-item/{{ $data->id; }}">
                                                    <button type="button" class="btn btn-sm btn-info"><i class="bx bx-edit-alt" title="edit data"></i></button>
                                                </a>
                                                @include('purchase.modal')
                                            </td>
                                        </tr>
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
    const total_amountInput = document.getElementById('total_amount');
    const taxRadios = document.getElementsByName('tax');
    const taxRateInput = document.getElementById('tax_rate'); // Tambahkan ini untuk nilai pajak
    const currencyRadios = document.querySelectorAll('input[name="currency"]');
    const currencyValueInput = document.getElementById('currency_value'); // Hidden input untuk mata uang

    // Tambahkan event listener untuk menghitung jumlah saat nilai berubah
    [qtyInput, priceInput, discountInput, taxRateInput].forEach(input => {
        input.addEventListener('input', calculateAmount);
    });

    // Tambahkan event listener untuk menghitung jumlah saat radio button berubah
    taxRadios.forEach(radio => {
        radio.addEventListener('change', calculateAmount);
    });

    // Tambahkan event listener ke setiap radio button
    currencyRadios.forEach(radio => {
        radio.addEventListener('change', formatPrice);
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

    // Fungsi untuk memformat angka sesuai dengan mata uang yang dipilih
    function formatPrice() {
        const selectedCurrency = document.querySelector('input[name="currency"]:checked').value;
        let priceValue = parseFloat(priceInput.value);

        if (isNaN(priceValue)) {
            priceInput.value = '';
            return;
        }

        // Format angka berdasarkan mata uang
        if (selectedCurrency === 'USD') {
            priceInput.value = priceValue.toFixed(3);  // Tampilkan tanpa format teks "USD"
        } else if (selectedCurrency === 'IDR') {
            priceInput.value = priceValue.toFixed(0);  // Tampilkan angka tanpa teks IDR
        }

        // Simpan mata uang yang dipilih ke dalam hidden field
        currencyValueInput.value = selectedCurrency;
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
