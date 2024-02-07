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
                        <h4 class="mb-sm-0 font-size-18"> Purchase Order</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase</a></li>
                                <li class="breadcrumb-item active"> Purchase Order</li>
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
                                <h5 class="mb-0">Purchase Order</h5>
                                <div>
                                    <button type="button" class="btn btn-primary waves-effect waves-light"
                                        onclick="supplier(); po_number(); request_number();" data-bs-toggle="modal"
                                        data-bs-target="#myModal">Add Data</button>
                                    <!-- Include modal content -->
                                    @include('purchase.modal')
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr>
                                        <tr>
                                            <th>No</th>
                                            <th>Po Number</th>
                                            <th>Date</th>
                                            <th>Suppliers</th>
                                            <th>Reference Number (PR)</th>
                                            <th>Down Payment</th>
                                            <th>Total Amount </th>
                                            <th>QC Check</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Un Posted</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($datas as $data)
                                            <tr>
                                                <td></td>
                                                <td>{{ $data->po_number }}</td>
                                                <td>{{ $data->date }}</td>
                                                <td>{{ $data->name }}</td>
                                                <td><a href="#" class="btn btn-sm btn-danger waves-effect waves-light" data-request-number="{{ $data->request_number }}">{{ $data->request_number }}</a></td>
                                                <td>{{ $data->down_payment }}</td>
                                                <td>{{ $data->total_amount }}</td>
                                                <td>{{ $data->qc_check }}</td>
                                                <td>{{ $data->type }}</td>
                                                <td>{{ $data->status }}</td>
                                                <td></td>
                                                <td><form action="/hapus_po/{{ $data->id }}" method="post"
                                                        class="d-inline">
                                                        @method('delete')
                                                        @csrf
                                                        <!-- <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="hapusData($(this).closest('form'))"> -->
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Anda yakin mau menghapus item ini ?')">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button>
                                                    </form>
                                                    <a href="/print-po/{{ $data->id }}" class="btn btn-sm btn-info waves-effect waves-light">
                                                            <i class="bx bx-printer" title="Edit data"></i>
                                                    </a>
                                               
                                                    <a href="/edit-po/{{ $data->id }}" class="btn btn-sm btn-info waves-effect waves-light">
                                                            <i class="bx bx-edit-alt" title="Edit data"></i>
                                                    </a>
                                                    @if($data->status=='Request' or $data->status=='Un Posted')
                                                    <form action="/posted_po/{{ $data->id }}" method="post"
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
                                                    <form action="/unposted_po/{{ $data->id }}" method="post"
                                                        class="d-inline" data-id="">
                                                        @method('PUT')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary"
                                                        onclick="return confirm('Anda yakin mau Un Posted item ini ?')">
                                                            <!-- <i class="bx bx-paper-plane" title="Posted" ></i> -->
                                                            <i class="mdi mdi-arrow-left-top-bold" title="Un Posted" >Un Posted</i>
                                                        </button></center>
                                                    </form>
                                                    @endif
                                                    </td>
                                             
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