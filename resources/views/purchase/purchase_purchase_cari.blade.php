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
                        <h4 class="mb-sm-0 font-size-18"> Purchase Requisition</h4>
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
                                <table id="so_ppic_table" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr>
                                        <tr>
                                            <th>Request Number</th>
                                            <th>Date</th>
                                            <th>Suppliers</th>
                                            <th>Requester</th>
                                            <th>QC Check</th>
                                            <th>Note</th>
                                            <th>PO Number</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    @foreach ($datas as $data)
                                        <tr>
                                            <td>{{ $data->request_number }}</td>
                                            <td>{{ $data->date }}</td>
                                            <td>{{ $data->name }}</td>
                                            <td>{{ $data->nm_requester }}</td>
                                            <td>{{ $data->qc_check }}</td>
                                            <td>{{ $data->note }}</td>
                                           <td></td>
                                            <td>{{ $data->type_pr }}</td>
                                            <td>{{ $data->status }}</td>
                                            <td>
                                            @if($data->status=='Request' or $data->status=='Un Posted')
                                                <form action="/hapus_pr/{{ $data->request_number }}" method="post"
                                                    class="d-inline">
                                                    @method('delete')
                                                    @csrf
                                                    
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Anda yakin mau menghapus item ini ?')">
                                                        <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                    </button>
                                                </form>
                                                <a href="/print-pr/{{ $data->id }}" class="btn btn-sm btn-info waves-effect waves-light">
                                                        <i class="bx bx-printer" title="print in English"></i>
                                                </a>
                                                <a href="/print-pr-ind/{{ $data->id }}" class="btn btn-sm btn-success waves-effect waves-light">
                                                        <i class="bx bx-printer" title="print dalam B Indo"></i>
                                                </a>
                                                <a href="/edit-pr/{{ $data->id }}" class="btn btn-sm btn-info waves-effect waves-light">
                                                        <i class="bx bx-edit-alt" title="Edit data"></i>
                                                </a>
                                                @if($data->status=='Request' or $data->status=='Un Posted')
                                                            <form action="/posted_pr/{{ $data->request_number }}" method="post"
                                                                class="d-inline" data-id="">
                                                                @method('PUT')
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-success"
                                                                onclick="return confirm('Anda yakin mau Posted item ini ?')">
                                                                    <i class="bx bx-paper-plane" title="Posted" ></i>
                                                                    <!-- <i class="mdi mdi-arrow-left-top-bold" title="Posted" >Un Posted</i> -->
                                                                </button></center>
                                                            </form>
                                                            @elseif($data->status=='Posted' or $data->status=='Created PO')
                                                            <form action="/unposted_pr/{{ $data->request_number }}" method="post"
                                                                class="d-inline" data-id="">
                                                                @method('PUT')
                                                                @csrf
                                                                @can('PPIC_unposted')
                                                                <button type="submit" class="btn btn-sm btn-primary"
                                                                onclick="return confirm('Anda yakin mau Un Posted item ini ?')">
                                                                    <!-- <i class="bx bx-paper-plane" title="Posted" ></i> -->
                                                                    <i class="mdi mdi-arrow-left-top-bold" title="Un Posted" >Un Posted</i>
                                                                </button></center>
                                                                @endcan
                                                            </form>
                                                            @endif
                                                    @elseif($data->status=='Created PO' or $data->status=='Closed')
                                                            <a href="/print-pr/{{ $data->id }}" class="btn btn-sm btn-info waves-effect waves-light">
                                                                    <i class="bx bx-printer" title="print in English"></i>
                                                            </a>
                                                            <a href="/print-pr-ind/{{ $data->id }}" class="btn btn-sm btn-success waves-effect waves-light">
                                                                    <i class="bx bx-printer" title="print dalam B Indo"></i>
                                                            </a>
                                                    @elseif($data->status=='Posted')
                                                            <a href="/print-pr/{{ $data->id }}" class="btn btn-sm btn-info waves-effect waves-light">
                                                                    <i class="bx bx-printer" title="print in English"></i>
                                                            </a>
                                                            <a href="/print-pr-ind/{{ $data->id }}" class="btn btn-sm btn-success waves-effect waves-light">
                                                                    <i class="bx bx-printer" title="print dalam B Indo"></i>
                                                            </a>
                                                            <form action="/unposted_pr/{{ $data->request_number }}" method="post"
                                                                class="d-inline" data-id="">
                                                                @method('PUT')
                                                                @csrf
                                                                @can('PPIC_unposted')
                                                                <button type="submit" class="btn btn-sm btn-primary"
                                                                onclick="return confirm('Anda yakin mau Un Posted item ini ?')">
                                                                    <!-- <i class="bx bx-paper-plane" title="Posted" ></i> -->
                                                                    <i class="mdi mdi-arrow-left-top-bold" title="Un Posted" >Un Posted</i>
                                                                </button></center>
                                                                @endcan
                                                            </form>
                                                    @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
