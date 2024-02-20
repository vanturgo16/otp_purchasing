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
                        <h4 class="mb-sm-0 font-size-18"> Purchase Requisition Item</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase</a></li>
                                <li class="breadcrumb-item active"> Purchase Requisition</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Purchase Requisition</h5>
                                <div>
                                    <a href="/hapus_request_number" class="btn btn-primary waves-effect waves-light">Add PR RM</a>
                                    <a href="/hapus_request_number_wip" class="btn btn-primary waves-effect waves-light">Add PR WIP</a>
                                    <a href="/hapus_request_number_fg" class="btn btn-primary waves-effect waves-light">Add PR FG</a>
                                    <a href="/hapus_request_number_ta" class="btn btn-primary waves-effect waves-light">Add PR Sparepart</a>
                                    <a href="/hapus_request_number_other" class="btn btn-primary waves-effect waves-light">Add other items</a>
                                    <!-- Include modal content -->
                                   
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Type Product</th>
                                            <th>Product WIP</th>
                                            <th>Qty</th>
                                            <th>Units</th>
                                            <th>Required Date</th>
                                            <th>CC / CO</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($data_detail_rm as $data)
                                            <tr>
                                                <td>{{ $data->type_product }}</td>
                                                <td>{{ $data->description }}</td>
                                                <td>{{ $data->qty }}</td>
                                                <td>{{ $data->unit_code }}</td>
                                                <td>{{ $data->required_date }}</td>
                                                <td>{{ $data->cc_co }}</td>
                                                <td>{{ $data->remarks }}</td>
                                               
                                                
                                            </tr>
                                        <!-- Add more rows as needed -->
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection