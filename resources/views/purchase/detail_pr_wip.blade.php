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
        <form method="post" action="/simpan_detail_wip/{{ $request_number }}" class="form-material m-t-40" enctype="multipart/form-data">
        @csrf
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18"> Add Purchase</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase</a></li>
                                <li class="breadcrumb-item active"> Add Purchase WIP</li>
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
                            <!--  <p class="card-title-desc"> layout options : from inline, horizontal & custom grid implementations</p> -->
                        </div>
                        <div class="card-body p-4">

                        <div class="col-sm-12">
                                <div class="mt-4 mt-lg-0">
                                    
                                   
                                        <div class="row mb-4 field-wrapper required-field">
                                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Request Number</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" name="request_number" value="{{ $request_number }}" readonly>
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
                            <!--  <p class="card-title-desc"> layout options : from inline, horizontal & custom grid implementations</p> -->
                        </div>
                        <div class="card-body p-4">

                        <div class="col-sm-12">
                                <div class="mt-4 mt-lg-0">
                                    
                                        <div class="row mb-4 field-wrapper required-field">
                                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Type Product</label>
                                            <div class="col-sm-9">
                                                <input type="radio" id="html" name="type" value="WIP" checked >
                                                <input type="hidden" id="html" name="type_product" value="WIP" checked>
                                                  <label for="html">WIP</label>
                                            </div>
                                        </div>
                                        <div class="row mb-4 field-wrapper required-field">
                                            <label for="horizontal-email-input" class="col-sm-3 col-form-label">Product WIP</label>
                                            <div class="col-sm-9">
                                                <select class="form-select" name="master_products_id" id="">
                                                        <option>Pilih Product WIP</option>
                                                    @foreach ($wip as $data)
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
                                                    <option value="{{ $data->id }}" @if ($data->unit_code === "KG") selected @endif>{{ $data->unit_code }}</option>
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