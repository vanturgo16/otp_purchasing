@extends('layouts.master')

@section('konten')

<div class="page-content">
        <div class="container-fluid">
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
                                                <td>{{ $data->request_number }}</td>
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
                                                    <form action="/submit_bulan_pgb/" method="post"
                                                        class="d-inline" data-id="">
                                                        @method('PUT')
                                                        @csrf
                                                        <button type="button" class="btn btn-sm btn-success"
                                                            onclick="kirimData($(this).closest('form'))">
                                                            <i class="bx bx-printer" title="Kirim data"></i>
                                                        </button></center>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-info " id=""
                                                            data-bs-toggle="modal"
                                                            onclick="edit_po('{{ $data->id }}')"
                                                            data-bs-target="#edit-po" data-id="">
                                                            <i class="bx bx-edit-alt" title="edit data"></i>
                                                        </button></td>
                                             
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