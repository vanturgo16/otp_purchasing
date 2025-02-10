@extends('layouts.master')
@section('konten')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div class="page-title-left">
                        <a href="{{ route('pr.edit', encrypt($data->id_purchase_requisitions)) }}" class="btn btn-light waves-effect btn-label waves-light">
                            <i class="mdi mdi-arrow-left label-icon"></i> Back To Data Purchase Requisition
                        </a>
                    </div>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase</a></li>
                            <li class="breadcrumb-item active"> Edit Item PR ({{ $data->type_product }})</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.alert')
        {{-- ITEM PR --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Edit Purchase Requisition Detail ({{ $data->type_product }})</h4>
            </div>
            <form method="POST" action="{{ route('pr.updateItem', encrypt($data->id)) }}" class="form-material m-t-40 formLoad" enctype="multipart/form-data">
                @csrf
                <div class="card-body p-4">
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Type Product</label>
                        <div class="col-sm-9">
                            <input type="radio" name="type_product" value="{{ $data->type_product }}" checked>
                            <label for="html">{{ $data->type_product }}</label>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Product {{ $data->type_product }}</label>
                        <div class="col-sm-9">
                            <select class="form-select request_number data-select2" name="master_products_id" style="width: 100%" required>
                                <option value="">Pilih Product {{ $data->type_product }}</option>
                                @foreach ($products as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == $data->master_products_id ? 'selected' : '' }}>{{ $item->description }}
                                        @if($data->type_product == 'FG')
                                            @if(!empty($item->perforasi)) || {{ $item->perforasi }} @endif
                                            @if(!empty($item->group_sub_code)) || Group Sub: {{ $item->group_sub_code }} @endif
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Qty</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control number-format" name="qty" id="qty" 
                                value="{{ $data->qty 
                                            ? (strpos(strval($data->qty), '.') !== false 
                                                ? rtrim(rtrim(number_format($data->qty, 3, ',', '.'), '0'), ',') 
                                                : number_format($data->qty, 0, ',', '.')) 
                                            : '0' }}"
                                placeholder="Masukkan Qty.." required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Units</label>
                        <div class="col-sm-9">
                            <select class="form-select data-select2" name="master_units_id" style="width: 100%" required>
                                <option value="">Pilih Units</option>
                                @foreach ($units as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == $data->master_units_id ? 'selected' : '' }}>{{ $item->unit_code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Required Date</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" name="required_date" value="{{ $data->required_date }}" required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">CC / CO</label>
                        <div class="col-sm-9">
                            <select class="form-select data-select2" name="cc_co" style="width: 100%" required>
                                <option value="">Pilih CC / CO</option>
                                @foreach ($requesters as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == $data->cc_co ? 'selected' : '' }}>{{ $item->nm_requester }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label class="col-sm-3 col-form-label">Remarks</label>
                        <div class="col-sm-9">
                            <textarea name="remarks" rows="3" cols="50" class="form-control" placeholder="Remarks.. (Opsional)">{{ $data->remarks }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row text-end">
                        <div>
                            <button type="reset" class="btn btn-secondary waves-effect btn-label waves-light">
                                <i class="mdi mdi-reload label-icon"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-info waves-effect btn-label waves-light">
                                <i class="mdi mdi-update label-icon"></i>Update
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
