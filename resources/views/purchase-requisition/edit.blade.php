@extends('layouts.master')
@section('konten')

@php
    $statusDetail = ['Created GRN', 'Closed'];
    $statusEdit = ['Request', 'Un Posted'];
@endphp
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
                            <li class="breadcrumb-item active"> {{ (in_array($data->status, $statusDetail)) ? 'Detail' : 'Edit' }} PR ({{ $data->type }})</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.alert')
        {{-- DATA PR --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ (in_array($data->status, $statusDetail)) ? 'Detail' : 'Edit' }} Purchase Requisition ({{ $data->type }})</h4>
            </div>
            <form method="POST" action="{{ route('pr.update', encrypt($data->id)) }}" class="form-material m-t-40 formLoad" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="{{ $data->type }}">
                <div class="card-body p-4">
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Request Number</label>
                        <div class="col-sm-9">
                            <input type="text" name="request_number" class="form-control custom-bg-gray" value="{{ $data->request_number }}" readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Date</label>
                        <div class="col-sm-9">
                            @if(in_array($data->status, $statusDetail))
                                <input type="text" class="form-control custom-bg-gray" value="{{ $data->date }}" readonly required>
                            @else
                                <input type="date" name="date" class="form-control" value="{{ $data->date }}" required>>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Suppliers</label>
                        <div class="col-sm-9">
                            <select class="form-select data-select2 @if(in_array($data->status, $statusDetail)) readonly-select2 @endif" name="id_master_suppliers" style="width: 100%" required>
                                <option value="">Pilih Suppliers</option>
                                @foreach ($suppliers as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == $data->id_master_suppliers ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Requester</label>
                        <div class="col-sm-9">
                            <select class="form-select data-select2 @if(in_array($data->status, $statusDetail)) readonly-select2 @endif" name="requester" style="width: 100%" required>
                                <option value="">Pilih Requester</option>
                                @foreach ($requesters as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == $data->requester ? 'selected' : '' }}>{{ $item->nm_requester }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Qc Check </label>
                        <div class="col-sm-9">
                            @if(in_array($data->status, $statusDetail))
                                <input type="text" class="form-control custom-bg-gray" value="{{ $data->qc_check }}" readonly required>
                            @else
                                <input type="radio" id="qc_check_Y" name="qc_check" value="Y" {{ $data->qc_check == 'Y' ? 'checked' : '' }} required>
                                <label for="qc_check_Y">Y</label>
                                <input type="radio" id="qc_check_N" name="qc_check" value="N" {{ $data->qc_check == 'N' ? 'checked' : '' }} >
                                <label for="qc_check_N">N</label>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label class="col-sm-3 col-form-label">Note</label>
                        <div class="col-sm-9">
                            @if(in_array($data->status, $statusDetail))
                                <textarea name="note" rows="3" cols="50" class="form-control custom-bg-gray" placeholder="Note.. (Opsional)" readonly>{{ $data->note }}</textarea>
                            @else
                                <textarea name="note" rows="3" cols="50" class="form-control" placeholder="Note.. (Opsional)">{{ $data->note }}</textarea>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Status </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control custom-bg-gray" name="status" value="{{ $data->status }}" readonly required>
                        </div>
                    </div>
                </div>
                @if(in_array($data->status, $statusEdit))
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
                @endif
            </form>
        </div>
        {{-- ITEM PR --}}
        @if(in_array($data->status, $statusEdit))
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Tambah Purchase Requisition Detail ({{ $data->type }})</h4>
            </div>
            <form method="POST" action="{{ route('pr.storeItem', encrypt($data->id)) }}" class="form-material m-t-40 formLoad" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="request_number" value="{{ $data->request_number }}" required>
                <div class="card-body p-4">
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Type Product</label>
                        <div class="col-sm-9">
                            <input type="radio" name="type_product" value="{{ $data->type }}" checked>
                            <label for="html">{{ $data->type }}</label>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Product {{ $data->type }}</label>
                        <div class="col-sm-9">
                            <select class="form-select request_number data-select2" name="master_products_id" required>
                                <option value="">Pilih Product {{ $data->type }}</option>
                                @foreach ($products as $item)
                                    <option value="{{ $item->id }}">{{ $item->description }}
                                        @if($data->type == 'FG')
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
                            <input type="text" class="form-control number-format" name="qty" id="qty" placeholder="Masukkan Qty.." required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Units</label>
                        <div class="col-sm-9">
                            <select class="form-select data-select2" name="master_units_id" required>
                                <option value="">Pilih Units</option>
                                @foreach ($units as $item)
                                    <option value="{{ $item->id }}" >{{ $item->unit_code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Required Date</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" name="required_date" required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">CC / CO</label>
                        <div class="col-sm-9">
                            <select class="form-select data-select2" name="cc_co" required>
                                <option value="">Pilih CC / CO</option>
                                @foreach ($requesters as $item)
                                    <option value="{{ $item->id }}">{{ $item->nm_requester }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label class="col-sm-3 col-form-label">Remarks</label>
                        <div class="col-sm-9">
                            <textarea name="remarks" rows="3" cols="50" class="form-control" placeholder="Remarks.. (Opsional)"></textarea>
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
                                <i class="mdi mdi-plus label-icon"></i>Tambah Ke Tabel
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        @endif

        {{-- LIST ITEM PR --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">List Item Product <b>"{{ $data->type }}"</b></h4>
            </div>
            <div class="card-body p-4">
                <table class="table table-bordered dt-responsive w-100" style="font-size: small" id="tableItem">
                    <thead>
                        <tr>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">No.</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2; border-right: 3px solid #e2e2e2;">Product</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Required Date</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">CC / CO</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Qty</th>
                            @if(in_array($data->status, $statusDetail))
                                <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Outstanding Qty</th>
                            @endif
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Units</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Remarks</th>
                            @if(in_array($data->status, $statusDetail))
                                <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2; border-left: 3px solid #e2e2e2;">Status</th>
                            @endif
                            @if(in_array($data->status, $statusEdit))
                                <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2; border-left: 3px solid #e2e2e2;">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($itemDatas as $item)
                            <tr>
                                <td class="align-top text-center"><b>{{ $loop->iteration }}</b></td>
                                <td class="align-top" style="border-right: 3px solid #e2e2e2;">
                                    <b>{{ $item->type_product }}</b>
                                    <br>{!! implode('<br>', array_map(fn($chunk) => implode(' ', $chunk), array_chunk(explode(' ', $item->product_desc), 10))) !!}
                                </td>
                                <td class="text-center">{{ $item->required_date}}</td>
                                <td class="align-top">{{ $item->cc_co_name}}</td>
                                <td class="text-center">
                                    <b>
                                        {{ $item->qty 
                                            ? (strpos(strval($item->qty), '.') !== false 
                                                ? rtrim(rtrim(number_format($item->qty, 3, ',', '.'), '0'), ',') 
                                                : number_format($item->qty, 0, ',', '.')) 
                                            : '0' }}
                                    </b>
                                </td>
                                @if(in_array($data->status, $statusDetail))
                                    <td class="text-center">
                                        <b>
                                            {{ $item->outstanding_qty 
                                                ? (strpos(strval($item->outstanding_qty), '.') !== false 
                                                    ? rtrim(rtrim(number_format($item->outstanding_qty, 3, ',', '.'), '0'), ',') 
                                                    : number_format($item->outstanding_qty, 0, ',', '.')) 
                                                : '0' }}
                                        </b>
                                    </td>
                                @endif
                                <td class="text-center">
                                    {{ $item->unit_code }}
                                </td>
                                <td>
                                    {!! implode('<br>', array_map(fn($chunk) => implode(' ', $chunk), array_chunk(explode(' ', $item->remarks), 10))) !!}
                                </td>
                                @if(in_array($data->status, $statusDetail))
                                    <td class="align-top text-center" style="border-left: 3px solid #e2e2e2;">
                                        @if ($item->status)
                                            <span class="badge bg-{{ $item->status === 'Open' ? 'info' : 'success' }}">{{ ucfirst($item->status) }}</span>
                                        @endif
                                    </td>
                                @endif
                                
                                @if(in_array($data->status, $statusEdit))
                                    <td class="align-top text-center" style="border-left: 3px solid #e2e2e2;">
                                        <a href="{{ route('pr.editItem', encrypt($item->id)) }}">
                                            <button type="button" class="btn btn-sm btn-info my-half"><i class="bx bx-edit-alt" title="Edit Data"></i></button>
                                        </a>
                                        <button type="submit" class="btn btn-sm btn-danger my-half" data-bs-toggle="modal" data-bs-target="#delete{{ $item->id }}">
                                            <i class="bx bx-trash-alt" title="Hapus Data"></i>
                                        </button>
                                    </td>
                                @endif
                            </tr>
                            <div class="modal fade" id="delete{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-top" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="staticBackdropLabel">Delete</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('pr.deleteItem', encrypt($item->id)) }}" method="POST" enctype="multipart/form-data" id="formDelete{{ $item->id }}">
                                            @csrf
                                            <div class="modal-body p-4">
                                                <div class="text-center">
                                                    Apakah Anda Yakin Untuk <b>Menghapus</b> Data?
                                                    <br><b>"{{ $item->product_desc }}"</b>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-danger waves-effect btn-label waves-light" id="btnFormDelete{{ $item->id }}">
                                                    <i class="mdi mdi-delete-alert label-icon"></i>Delete
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <script>
                            $(document).on('submit', 'form[id^="formDelete"]', function(event) {
                                event.preventDefault();
                                let formId = this.id;
                                let btnId = formId.replace("formDelete", "btnFormDelete");
                                let btn = $("#" + btnId);
                                if (typeof $.fn.valid === "function" && !$(this).valid()) {
                                    console.log("Form validation failed for:", formId);
                                    return false;
                                }
                                if (btn.length > 0) {
                                    btn.prop("disabled", true);
                                    btn.html('<i class="mdi mdi-loading mdi-spin label-icon"></i> Please Wait...');
                                } else {
                                    console.log("Button NOT found!");
                                }
                                this.submit();
                            });
                        </script>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


@endsection
