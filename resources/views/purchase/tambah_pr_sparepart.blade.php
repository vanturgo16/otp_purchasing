@extends('layouts.master')

@section('konten')

<div class="page-content">
    <div class="container-fluid">
    <form method="post" action="/simpan_pr_ta" class="form-material m-t-40" enctype="multipart/form-data">
    @csrf
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"> Add Purchase</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase</a></li>
                            <li class="breadcrumb-item active"> Add Purchase TA</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add Purchase Requisition</h4>
                        <!--  <p class="card-title-desc">Form layout options : from inline, horizontal & custom grid implementations</p> -->
                    </div>
                    <div class="card-body p-4">

                    <div class="col-sm-12">
                            <div class="mt-4 mt-lg-0">
                                
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Request Number</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" value="{{ $formattedCode }}" name="request_number">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-email-input" class="col-sm-3 col-form-label">Date</label>
                                        <div class="col-sm-9">
                                            <input type="date" class="form-control" value="{{ date('Y-m-d') }}" name="date">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Suppliers </label>
                                        <div class="col-sm-9">
                                            <select class="form-select" name="id_master_suppliers" id="">
                                                <option>Pilih Suppliers</option>
                                                @foreach ($supplier as $data)
                                                    <option value="{{ $data->id }}">{{ $data->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Requester </label>
                                        <div class="col-sm-9">
                                            <select class="form-select" name="requester" id="">
                                                <option>Pilih Requester</option>
                                                @foreach ($datas as $data)
                                                    <option value="{{ $data->id }}">{{ $data->nm_requester }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Qc Check </label>
                                        <div class="col-sm-9">
                                            <input type="radio" id="html" name="qc_check" value="Y" >
                                              <label for="html">Y</label>
                                              <input type="radio" id="css" name="qc_check" value="N">
                                              <label for="css">N</label>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Note </label>
                                        <div class="col-sm-9">
                                            <textarea name="note" rows="4" cols="50" class="form-control"></textarea>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Status </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" value="Request" name="status" readonly>
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
                        <h4 class="card-title">Purchase Requisition Detail</h4>
                        <!--  <p class="card-title-desc">Form layout options : from inline, horizontal & custom grid implementations</p> -->
                    </div>
                    <div class="card-body p-4">

                    <div class="col-sm-12">
                            <div class="mt-4 mt-lg-0">
                                
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Type Product</label>
                                        <div class="col-sm-9">
                                            <input type="radio" id="html" name="type" value="TA" checked>
                                              <label for="html">TA</label>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-email-input" class="col-sm-3 col-form-label">Sparepart & Auxiliaries</label>
                                        <div class="col-sm-9">
                                            <select class="form-select" name="master_products_id" id="">
                                                    <option>Pilih Product Sparepart & Auxiliaries</option>
                                                @foreach ($ta as $data)
                                                    <option value="{{ $data->id }}">{{ $data->description }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Qty</label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" name="qty">
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
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Required Date </label>
                                        <div class="col-sm-9">
                                            <input type="date" class="form-control" name="required_date">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">CC / CO</label>
                                        <div class="col-sm-9">
                                            <select class="form-select" name="cc_co" id="">
                                                <option>Pilih CC / CO</option>
                                                @foreach ($datas as $data)
                                                <option value="{{ $data->id }}">{{ $data->nm_requester }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Remarks</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="remarks">
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
                        <!--  <p class="card-title-desc">Form layout options : from inline, horizontal & custom grid implementations</p> -->
                    </div>
                    <div class="card-body p-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                <thead>
                                    <tr>
                                    <tr>
                                        <th>Type Product</th>
                                        <th>Sparepart & Auxiliaries</th>
                                        <th>Qty</th>
                                        <th>Units</th>
                                        <th>Required Date</th>
                                        <th>CC / CO</th>
                                        <th>Remarks</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($dt_detailSmt as $data)
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
                                                        onclick="edit_pr_smt('{{ $data->id }}')"
                                                        data-bs-target="#edit-pr-smt" data-id="">
                                                        <i class="bx bx-edit-alt" title="edit data"></i>
                                                    </button></center>
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
                                <button type="reset" class="btn btn-info w-md">Back</button>
                                <button type="submit" class="btn btn-primary w-md">Save & Add More</button>
                                <button type="submit" class="btn btn-primary w-md">Save</button>
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