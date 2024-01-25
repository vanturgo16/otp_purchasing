@extends('layouts.master')

@section('konten')

<div class="page-content">
        <div class="container-fluid">
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
                                    <!-- Include modal content -->
                                   
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
                                            <th>Request Number</th>
                                            <th>Date</th>
                                            <th>Suppliers</th>
                                            <th>Requester</th>
                                            <th>QC Check</th>
                                            <th>Note</th>
                                            <th>PO Number</th>
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
                                                <td>{{ $data->request_number }}</td>
                                                <td>{{ $data->date }}</td>
                                                <td>{{ $data->name }}</td>
                                                <td>{{ $data->nm_requester }}</td>
                                                <td>{{ $data->qc_check }}</td>
                                                <td>{{ $data->note }}</td>
                                                <td></td>
                                                <td>{{ $data->type }}</td>
                                                <td>{{ $data->status }}</td>
                                                <td></td>
                                                <td><form action="/hapus_pr/{{ $data->id }}" method="post"
                                                        class="d-inline">
                                                        @method('delete')
                                                        @csrf
                                                        <!-- <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="hapusData($(this).closest('form'))">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button> -->
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
                                                    <a href="/edit-pr/{{ $data->request_number }}" class="btn btn-sm btn-info waves-effect waves-light">
                                                            <i class="bx bx-edit-alt" title="Edit data"></i>
                                                    </a>
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