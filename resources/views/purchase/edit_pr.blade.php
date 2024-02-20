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
        <form method="post" action="/update_pr/{{ $datas[0]->request_number; }}" class="form-material m-t-40" enctype="multipart/form-data" id="form_ekpor">
            @method('PUT')
            @csrf
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
                                            <input type="text" name="request_number" class="form-control" value="{{ $datas[0]->request_number; }}" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-email-input" class="col-sm-3 col-form-label">Date</label>
                                        <div class="col-sm-9">
                                            <input type="date" name="date" class="form-control" value="{{ $datas[0]->date; }}">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Suppliers </label>
                                        <div class="col-sm-9">
                                        <select class="form-select" name="id_master_suppliers" id="">
                                            <option value="">Pilih Suppliers</option>
                                            @foreach ($supplier as $data)
                                                <option value="{{ $data->id }}" {{ $data->id == $selectedId ? 'selected' : '' }}>{{ $data->name }}</option>
                                            @endforeach
                                        </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Requester </label>
                                        <div class="col-sm-9">
                                        <select class="form-select" name="requester" id="">
                                        <option>Pilih Requester</option>
                                        @foreach ($data_requester as $data)
                                            <option value="{{ $data->id }}" {{ $data->id == $selectedIdreques ? 'selected' : '' }}>{{ $data->nm_requester }}</option>
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
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Note </label>
                                        <div class="col-sm-9">
                                            <textarea name="note" rows="4" cols="50" class="form-control">{{ $datas[0]->note; }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Status </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="status" value="Request" readonly>
                                            <input type="hidden" class="form-control" name="type" value="{{ $datas[0]->type; }}">
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
        <form method="post" action="/update_detail_rm/{{ $datas[0]->request_number; }}" class="form-material m-t-40" enctype="multipart/form-data">
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
                                            <input type="radio" id="html" name="type" value="{{ $datas[0]->type; }}" checked>
                                            <input type="hidden" id="hidden" name="type_product" value="{{ $datas[0]->type; }}" checked>
                                            <!-- <input type="text" name="request_number" class="form-control" value="{{ $datas[0]->request_number; }}"> -->
                                              <label for="html">{{ $datas[0]->type; }}</label>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-email-input" class="col-sm-3 col-form-label">Product {{ $datas[0]->type; }}</label>
                                        <div class="col-sm-9">
                                            @if($datas[0]->type=='RM')
                                            <select class="form-select request_number" name="master_products_id" id="" onchange="get_unit()">
                                                    <option>Pilih Product RM</option>
                                                @foreach ($rawMaterials as $data)
                                                    <option value="{{ $data->id }}" data-id="{{ $data->id }}">{{ $data->description }}</option>
                                                @endforeach
                                            </select>
                                            @elseif($datas[0]->type=='WIP')
                                            <select class="form-select" name="master_products_id" id="">
                                                    <option>Pilih Product WIP</option>
                                                @foreach ($wip as $data)
                                                    <option value="{{ $data->id }}">{{ $data->description }}</option>
                                                @endforeach
                                            </select>
                                            @elseif($datas[0]->type=='FG')
                                            <select class="form-select" name="master_products_id" id="">
                                                    <option value="{{ $data->id }}">Pilih Product FG</option>
                                                @foreach ($fg as $data)
                                                    <option>{{ $data->description }}</option>
                                                @endforeach
                                            </select>
                                            @elseif($datas[0]->type=='TA')
                                            <select class="form-select" name="product" id="">
                                                    <option value="{{ $data->id }}">Pilih Product Sparepart & Auxiliaries</option>
                                                @foreach ($ta as $data)
                                                    <option>{{ $data->description }}</option>
                                                @endforeach
                                            </select>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Qty</label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" name="qty" value="old('qty') }}">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Units </label>
                                        <div class="col-sm-9">
                                            <select class="form-select" name="master_units_id" id="unit_code">
                                                <option>Pilih Units</option>
                                                @foreach ($units as $data)
                                                    <option value="{{ $data->id }}">{{ $data->unit_code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Required Date </label>
                                        <div class="col-sm-9">
                                            <input type="date" class="form-control" name="required_date" value="{{ old('required_date') }}">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">CC / CO</label>
                                        <div class="col-sm-9">
                                            <select class="form-select" name="cc_co" value="{{ old('cc_co') }}">
                                                <option>Pilih CC / CO</option>
                                                @foreach ($datas as $data)
                                                    <option>{{ $data->nm_requester }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Remarks</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="remarks" value="{{ old('remarks') }}">
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
                                        <th>Required Date</th>
                                        <th>CC / CO</th>
                                        <th>Remarks</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if($datas[0]->type=='RM')
                                    @foreach ($data_detail_rm as $data)
                                            <tr>
                                                <td>{{ $data->type_product }}</td>
                                                <td>{{ $data->description }}</td>
                                                <td>{{ $data->qty }}</td>
                                                <td>{{ $data->unit_code }}</td>
                                                <td>{{ $data->required_date }}</td>
                                                <td>{{ $data->cc_co }}</td>
                                                <td>{{ $data->remarks }}</td>
                                                <td>
                                                    <button type="submit" class="btn btn-sm btn-danger" name="hapus_detail" value="{{ $data->id }}">
                                                        <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-info " id=""
                                                        data-bs-toggle="modal"
                                                        onclick="edit_pr('{{ $data->id }}')"
                                                        data-bs-target="#edit-pr" data-id="">
                                                        <i class="bx bx-edit-alt" title="edit data"></i>
                                                    </button></center>
                                                    @include('purchase.modal')
                                                        </td>
                                                
                                            </tr>
                                        <!-- Add more rows as needed -->
                                    @endforeach
                                @elseif($datas[0]->type=='WIP')
                                    @foreach ($data_detail_rm as $data)
                                            <tr>
                                                <td>{{ $data->type_product }}</td>
                                                <td>{{ $data->description }}</td>
                                                <td>{{ $data->qty }}</td>
                                                <td>{{ $data->unit_code }}</td>
                                                <td>{{ $data->required_date }}</td>
                                                <td>{{ $data->cc_co }}</td>
                                                <td>{{ $data->remarks }}</td>
                                                <td>
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="hapusData($(this).closest('form'))">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-info " id=""
                                                            data-bs-toggle="modal"
                                                            onclick="edit_pr_smt('{{ $data->id }}')"
                                                            data-bs-target="#edit-pr-smt" data-id="">
                                                            <i class="bx bx-edit-alt" title="edit data"></i>
                                                        </button></center>
                                                        @include('purchase.modal')
                                                        </td>
                                                
                                            </tr>
                                        <!-- Add more rows as needed -->
                                    @endforeach
                                @elseif($datas[0]->type=='FG')
                                    @foreach ($data_detail_fg as $data)
                                            <tr>
                                                <td>{{ $data->type_product }}</td>
                                                <td>{{ $data->description }}</td>
                                                <td>{{ $data->qty }}</td>
                                                <td>{{ $data->unit_code }}</td>
                                                <td>{{ $data->required_date }}</td>
                                                <td>{{ $data->cc_co }}</td>
                                                <td>{{ $data->remarks }}</td>
                                                <td>
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="hapusData($(this).closest('form'))">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-info " id=""
                                                            data-bs-toggle="modal"
                                                            onclick="edit_pr_smt('{{ $data->id }}')"
                                                            data-bs-target="#edit-pr-smt" data-id="">
                                                            <i class="bx bx-edit-alt" title="edit data"></i>
                                                        </button></center>
                                                        @include('purchase.modal')
                                                        </td>
                                                
                                            </tr>
                                        <!-- Add more rows as needed -->
                                    @endforeach
                                @elseif($datas[0]->type=='TA')
                                 @foreach ($data_detail_ta as $data)
                                            <tr>
                                                <td>{{ $data->type_product }}</td>
                                                <td>{{ $data->description }}</td>
                                                <td>{{ $data->qty }}</td>
                                                <td>{{ $data->unit_code }}</td>
                                                <td>{{ $data->required_date }}</td>
                                                <td>{{ $data->cc_co }}</td>
                                                <td>{{ $data->remarks }}</td>
                                                <td>
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="hapusData($(this).closest('form'))">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-info " id=""
                                                            data-bs-toggle="modal"
                                                            onclick="edit_pr('{{ $data->id }}')"
                                                            data-bs-target="#edit-pr" data-id="">
                                                            <i class="bx bx-edit-alt" title="edit data"></i>
                                                        </button>
                                                            </center>
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
                                <a href="/purchase" class="btn btn-info w-md">Back</a>  
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
@endsection