@extends('layouts.master')
@section('konten')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div class="page-title-left">
                        <a href="{{ route('pr.index') }}" class="btn btn-light waves-effect btn-label waves-light">
                            <i class="mdi mdi-arrow-left label-icon"></i> Back To List Purchase Requisition
                        </a>
                    </div>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase</a></li>
                            <li class="breadcrumb-item active"> Tambah PR ({{ $type }})</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.alert')
        {{-- FORM PR --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Tambah Purchase Requisition ({{ $type }})</h4>
            </div>
            <form method="POST" action="{{ route('pr.store') }}" class="form-material m-t-40 formLoad" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="card-body p-4">
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Request Number</label>
                        <div class="col-sm-9">
                            <input type="text" name="request_number" class="form-control custom-bg-gray" value="{{ $formattedCode }}" readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Date</label>
                        <div class="col-sm-9">
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Suppliers</label>
                        <div class="col-sm-9">
                            <select class="form-select data-select2" name="id_master_suppliers" style="width: 100%" required>
                                <option value="">Pilih Suppliers</option>
                                @foreach ($suppliers as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Requester</label>
                        <div class="col-sm-9">
                            <select class="form-select data-select2" name="requester" style="width: 100%" required>
                                <option value="">Pilih Requester</option>
                                @foreach ($requesters as $item)
                                    <option value="{{ $item->id }}">{{ $item->nm_requester }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Qc Check </label>
                        <div class="col-sm-9">
                            <input type="radio" id="qc_check_Y" name="qc_check" value="Y"required>
                            <label for="qc_check_Y">Y</label>
                            <input type="radio" id="qc_check_N" name="qc_check" value="N">
                            <label for="qc_check_N">N</label>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label class="col-sm-3 col-form-label">Note</label>
                        <div class="col-sm-9">
                            <textarea name="note" rows="3" cols="50" class="form-control" placeholder="Note.. (Opsional)"></textarea>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Status </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control custom-bg-gray" name="status" value="Request" readonly required>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row text-end">
                        <div>
                            <button type="reset" class="btn btn-secondary waves-effect btn-label waves-light">
                                <i class="mdi mdi-reload label-icon"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-primary waves-effect btn-label waves-light">
                                <i class="mdi mdi-plus label-icon"></i>Save
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
