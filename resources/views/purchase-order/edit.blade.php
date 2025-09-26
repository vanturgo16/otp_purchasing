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
                        <form action="{{ route('po.index') }}" method="GET" id="resetForm" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="idUpdated" value="{{ $data->id }}">
                            <button type="submit" class="btn btn-light waves-effect btn-label waves-light">
                                <i class="mdi mdi-arrow-left label-icon"></i> Back To List Purchase Order    
                            </button>
                        </form>
                        {{-- <a href="{{ route('po.index') }}" class="btn btn-light waves-effect btn-label waves-light">
                            <i class="mdi mdi-arrow-left label-icon"></i> Back To List Purchase Order
                        </a> --}}
                    </div>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase</a></li>
                            <li class="breadcrumb-item active"> {{ (in_array($data->status, $statusDetail)) ? 'Detail' : 'Edit' }} Purchase Order {{ $data->type }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.alert')
        {{-- DATA PO --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ (in_array($data->status, $statusDetail)) ? 'Detail' : 'Edit' }} Purchase Order</h4>
            </div>
            <form method="POST" action="{{ route('po.update', encrypt($data->id)) }}" class="form-material m-t-40 formLoad" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="reference_number_before" value="{{ $data->reference_number }}" readonly required>
                <div class="card-body p-4">
                    <div class="row mb-4 field-wrapper required-field">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Po Number</label>
                        <div class="col-sm-9">
                            <input type="text" name="po_number" class="form-control custom-bg-gray" value="{{ $data->po_number }}" readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label for="horizontal-email-input" class="col-sm-3 col-form-label">Date</label>
                        <div class="col-sm-9">
                            @if(in_array($data->status, $statusDetail))
                                <input type="text" class="form-control custom-bg-gray" value="{{ $data->date }}" readonly required>
                            @else
                                <input type="date" name="date" class="form-control" value="{{ $data->date }}" required>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label for="horizontal-email-input" class="col-sm-3 col-form-label">Delivery Date</label>
                        <div class="col-sm-9">
                            @if(in_array($data->status, $statusDetail))
                                <input type="text" class="form-control custom-bg-gray" value="{{ $data->date }}" readonly required>
                            @else
                                <input type="date" name="delivery_date" class="form-control" value="{{ $data->delivery_date }}" required>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">
                            Reference Number (PR)
                            @if(in_array($data->status, $statusEdit))
                                <i class="mdi mdi-information-outline" data-bs-toggle="tooltip" data-bs-placement="top" title="Mengubah Nomor Referensi akan memperbarui item produk sesuai Purchase Request."></i>
                            @endif
                        </label>
                        <div class="col-sm-9">
                            <select class="form-select data-select2 @if(in_array($data->status, $statusDetail)) readonly-select2 @endif" name="reference_number" id="reference_number" style="width: 100%" required>
                                <option value="">Pilih Reference Number</option>
                                @foreach ($reference_number as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == $data->reference_number ? 'selected' : '' }}>
                                        {{ $item->request_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <script>
                        $(document).ready(function() {
                            $('[data-bs-toggle="tooltip"]').tooltip();
                            $('select[name="reference_number"]').change(function() {
                                $('.mdi-information-outline').tooltip('show');
                                setTimeout(function () {
                                    $('.mdi-information-outline').tooltip('hide');
                                }, 3000);

                                var referenceId = $(this).val();
                                if (referenceId) {
                                    $.ajax({
                                        url: "{{ route('pr.getPRDetails') }}",
                                        method: 'GET',
                                        data: { reference_id: referenceId },
                                        success: function(response) {
                                            if (response.success) {
                                                $('select[name="id_master_suppliers"]').val(response.data.id_master_suppliers).trigger('change');
                                                $('input[name="type"]').val(response.data.type);
                                                $('input[name="qc_check"]').val(response.data.qc_check);
                                            } else {
                                                alert('No data found for this reference number.');
                                            }
                                        },
                                        error: function() {
                                            alert('Error fetching data. Please try again.');
                                        }
                                    });
                                } else {
                                    $('select[name="id_master_suppliers"]').val('');
                                    $('input[name="qc_check"]').val('');
                                    $('input[name="type"]').val('');
                                }
                            });
                        });
                    </script>
                    <div class="row mb-4 field-wrapper required-field">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Supplier </label>
                        <div class="col-sm-9">
                            <select class="form-select data-select2 readonly-select2" name="id_master_suppliers" style="width: 100%" id="" required>
                                <option value="">Pilih Suppliers</option>
                                @foreach ($suppliers as $item)
                                <option value="{{ $item->id }}" {{ $item->id == $data->id_master_suppliers ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Qc Check</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control custom-bg-gray" name="qc_check" value="{{ $data->qc_check }}" placeholder="Otomatis Terisi.." readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Status </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control custom-bg-gray" name="status" value="{{ $data->status }}" readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Type </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control custom-bg-gray" name="type" value="{{ $data->type }}" readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Down Payment </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control number-format @if(in_array($data->status, $statusDetail)) custom-bg-gray @endif" name="down_payment" placeholder="Masukkan Down Payment.." 
                                value="{{ $data->down_payment ? (strpos($data->down_payment, '.') === false ? number_format($data->down_payment, 0, ',', '.') : number_format($data->down_payment, 3, ',', '.')) : '0' }}" 
                                required @if(in_array($data->status, $statusDetail)) readonly @endif>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Own Remarks </label>
                        <div class="col-sm-9">
                            @if(in_array($data->status, $statusDetail))
                                <textarea rows="3" cols="50" class="form-control custom-bg-gray" placeholder="Remarks.. (Opsional)" readonly>{{ $data->own_remarks }}</textarea>
                            @else
                                <textarea name="own_remarks" rows="3" cols="50" class="form-control" placeholder="Remarks.. (Opsional)">{{ $data->own_remarks }}</textarea>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Supplier Remarks </label>
                        <div class="col-sm-9">
                            @if(in_array($data->status, $statusDetail))
                                <textarea rows="3" cols="50" class="form-control custom-bg-gray" placeholder="Remarks.. (Opsional)" readonly>{{ $data->supplier_remarks }}</textarea>
                            @else
                                <textarea name="supplier_remarks" rows="3" cols="50" class="form-control" placeholder="Remarks.. (Opsional)">{{ $data->supplier_remarks }}</textarea>
                            @endif
                        </div>
                    </div>
                </div>
                @if(in_array($data->status, $statusEdit))
                <div class="card-footer">
                    <div class="row text-end">
                        <div>
                            <a href="{{ route('po.edit', encrypt($data->id)) }}" type="button" class="btn btn-secondary waves-effect btn-label waves-light">
                                <i class="mdi mdi-reload label-icon"></i>Reset
                            </a>
                            <button type="submit" class="btn btn-primary waves-effect btn-label waves-light">
                                <i class="mdi mdi-update label-icon"></i>Update
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </form>
        </div>

        {{-- LIST ITEM --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">List Item Product <b>"{{ $data->type }}"</b></h4>
            </div>
            <div class="card-body p-4">
                <table class="table table-bordered dt-responsive w-100" style="font-size: small" id="tableItem">
                    <thead>
                        <tr>
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">No.</th>
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2; border-right: 3px solid #e2e2e2;">Product</th>
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Qty</th>
                            @if(in_array($data->status, $statusDetail))
                                <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Cancel Qty</th>
                                <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Outstanding Qty</th>
                                <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Status</th>
                            @endif
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Currency</th>
                            <th class="align-middle text-center" colspan="6" style="background-color: #6C7AE0; color:#ffff;">Detail Price</th>
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Note</th>
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2; border-left: 3px solid #e2e2e2;">Aksi</th>
                        </tr>
                        <tr>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Price</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Sub Total</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Discount</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Amount</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Tax</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($itemDatas as $item)
                            <tr>
                                <td class="align-top text-center"><b>{{ $loop->iteration }}</b></td>
                                <td class="align-top" style="border-right: 3px solid #e2e2e2;">
                                    <b>{{ $item->type_product }}</b>
                                    <br>{!! implode('<br>', array_map(fn($chunk) => implode(' ', $chunk), array_chunk(explode(' ', $item->product_desc), 10))) !!}  {{-- max 10 word one line --}}
                                </td>
                                <td class="text-center">
                                    <b>
                                        {{ $item->qty 
                                            ? (strpos(strval($item->qty), '.') !== false 
                                                ? rtrim(rtrim(number_format($item->qty, 6, ',', '.'), '0'), ',') 
                                                : number_format($item->qty, 0, ',', '.')) 
                                            : '0' }}
                                    </b>
                                    <br>({{ $item->unit_code }})
                                </td>
                                @if(in_array($data->status, $statusDetail))
                                    <td class="text-center">
                                        <b>
                                            {{ $item->cancel_qty 
                                                ? (strpos(strval($item->cancel_qty), '.') !== false 
                                                    ? rtrim(rtrim(number_format($item->cancel_qty, 6, ',', '.'), '0'), ',') 
                                                    : number_format($item->cancel_qty, 0, ',', '.')) 
                                                : '0' }}
                                        </b>
                                    </td>
                                    <td class="text-center">
                                        <b>
                                            {{ $item->outstanding_qty 
                                                ? (strpos(strval($item->outstanding_qty), '.') !== false 
                                                    ? rtrim(rtrim(number_format($item->outstanding_qty, 6, ',', '.'), '0'), ',') 
                                                    : number_format($item->outstanding_qty, 0, ',', '.')) 
                                                : '0' }}
                                        </b>
                                    </td>
                                    <td class="align-top text-center">
                                        @if ($item->status)
                                            <span class="badge bg-{{ $item->status === 'Open' ? 'info' : 'success' }}">{{ ucfirst($item->status) }}</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="text-center">{{ $item->currency ?? '-' }}</td>
                                <td class="text-end">
                                    {{ $item->price ? (strpos($item->price, '.') === false ? number_format($item->price, 0, ',', '.') : number_format($item->price, 3, ',', '.')) : '0' }}
                                </td>
                                <td class="text-end">
                                    {{ $item->sub_total ? (strpos($item->sub_total, '.') === false ? number_format($item->sub_total, 0, ',', '.') : number_format($item->sub_total, 3, ',', '.')) : '0' }}
                                </td>
                                <td class="text-end">
                                    {{ $item->discount ? (strpos($item->discount, '.') === false ? number_format($item->discount, 0, ',', '.') : number_format($item->discount, 3, ',', '.')) : '0' }}
                                </td>
                                <td class="text-end">
                                    {{ $item->amount ? (strpos($item->amount, '.') === false ? number_format($item->amount, 0, ',', '.') : number_format($item->amount, 3, ',', '.')) : '0' }}
                                </td>
                                <td class="text-end">
                                    @if($item->tax == 'N')
                                        <b>N</b>
                                    @elseif($item->tax == null)
                                        0
                                    @else
                                        {{ $item->tax_value ? (strpos($item->tax_value, '.') === false ? number_format($item->tax_value, 0, ',', '.') : number_format($item->tax_value, 3, ',', '.')) : '0' }}
                                        <br><span class="badge bg-info" 
                                            title="{{ $item->tax_rate }}% Dari {{ $item->amount ? (strpos($item->amount, '.') === false ? number_format($item->amount, 0, ',', '.') : number_format($item->amount, 3, ',', '.')) : '0' }}">
                                            ({{ $item->tax_rate }}%)
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    {{ $item->total_amount ? (strpos($item->total_amount, '.') === false ? number_format($item->total_amount, 0, ',', '.') : number_format($item->total_amount, 3, ',', '.')) : '0' }}
                                </td>
                                <td>
                                    <span title="{{ strlen($item->note) > 70 ? $item->note : '' }}">
                                        {{ strlen($item->note) > 70 ? substr($item->note, 0, 70) . '...' : $item->note }}
                                    </span>
                                </td>
                                @if(in_array($data->status, $statusEdit))
                                    <td class="align-top text-center" style="border-left: 3px solid #e2e2e2;">
                                        @if($statusPR == 'Un Posted')
                                            <span class="badge bg-warning">PR Sedang UnPost</span>
                                        @else
                                            <a href="{{ route('po.editItem', encrypt($item->id)) }}">
                                                <button type="button" class="btn btn-sm btn-info my-half"><i class="bx bx-edit-alt" title="Edit Data"></i></button>
                                            </a>
                                        @endif
                                    </td>
                                @else 
                                    <td class="align-top text-center" style="border-left: 3px solid #e2e2e2;">
                                        @if($item->outstanding_qty > 0 || $item->cancel_qty > 0)
                                            <button type="button" class="btn btn-sm btn-danger my-half" 
                                                data-bs-toggle="modal" data-bs-target="#cancelQty{{ $item->id }}"><i class="bx bx-x" title="Cancel Qty"></i> Cancel Qty
                                            </button>
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endif
                            </tr>
                            @if($item->outstanding_qty > 0 || $item->cancel_qty > 0)
                                <div class="modal fade" id="cancelQty{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-top modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="staticBackdropLabel">Cancel Qty</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('po.cancelQtyItem', encrypt($item->id)) }}" method="POST" enctype="multipart/form-data" id="formCancel{{ $item->id }}">
                                                @csrf
                                                <div class="modal-body p-4" style="max-height: 65vh; overflow-y: auto;">
                                                    <div class="container">
                                                        <div class="row mb-2 field-wrapper required-field">
                                                            <label class="col-sm-3 col-form-label">Product</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" class="form-control custom-bg-gray" placeholder="Product.." value="{{ $item->product_desc }}" readonly>
                                                            </div>
                                                        </div>
                                                        <br>
                                                        <div class="row mb-2 field-wrapper required-field">
                                                            <label class="col-sm-3 col-form-label">Qty</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" class="form-control custom-bg-gray" name="qty" id="qty" placeholder="Qty.." 
                                                                    value="{{ $item->qty 
                                                                    ? (strpos(strval($item->qty), '.') !== false 
                                                                        ? rtrim(rtrim(number_format($item->qty, 6, ',', '.'), '0'), ',') 
                                                                        : number_format($item->qty, 0, ',', '.')) 
                                                                    : '0' }}"
                                                                    readonly required>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-2 field-wrapper required-field">
                                                            <label class="col-sm-3 col-form-label">Cancel Qty</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" class="form-control number-format" name="cancel_qty" id="cancel_qty" placeholder="Masukkan Cancel Qty.." 
                                                                    value="{{ $item->cancel_qty 
                                                                    ? (strpos(strval($item->cancel_qty), '.') !== false 
                                                                        ? rtrim(rtrim(number_format($item->cancel_qty, 6, ',', '.'), '0'), ',') 
                                                                        : number_format($item->cancel_qty, 0, ',', '.')) 
                                                                    : '0' }}"
                                                                    required>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-2 field-wrapper required-field">
                                                            <label class="col-sm-3 col-form-label">Outstanding Qty</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" class="form-control custom-bg-gray" name="outstanding_qty" id="outstanding_qty" placeholder="Outstanding.. (Terisi Otomatis)" 
                                                                    value="{{ $item->outstanding_qty 
                                                                    ? (strpos(strval($item->outstanding_qty), '.') !== false 
                                                                        ? rtrim(rtrim(number_format($item->outstanding_qty, 6, ',', '.'), '0'), ',') 
                                                                        : number_format($item->outstanding_qty, 0, ',', '.')) 
                                                                    : '0' }}"
                                                                    readonly required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-danger waves-effect btn-label waves-light" id="btnFormCancel{{ $item->id }}">
                                                        <i class="bx bx-x label-icon"></i>Cancel Qty
                                                    </button>
                                                </div>
                                            </form>
                                            <script>
                                                $(document).on('submit', 'form[id^="formCancel"]', function(event) {
                                                    event.preventDefault();
                                                    let formId = this.id;
                                                    let btnId = formId.replace("formCancel", "btnFormCancel");
                                                    let btn = $("#" + btnId);
                                                    if (typeof $.fn.valid === "function" && !$(this).valid()) {
                                                        return false;
                                                    }
                                                    if (btn.length > 0) {
                                                        btn.prop("disabled", true);
                                                        btn.html('<i class="mdi mdi-loading mdi-spin label-icon"></i> Please Wait...');
                                                    }
                                                    this.submit();
                                                });
                                            </script>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                        @if(!$itemDatas->isEmpty())
                        <tr>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2; border-left: none; border-right: none;"></td>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2; border-left: none; border-right: none;" class="text-end"><b>Total</b></td>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2; border-left: none; border-right: none;"></td>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2; border-left: none; border-right: none;"></td>
                            @if(in_array($data->status, $statusDetail))
                                <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2; border-left: none; border-right: none;"></td>
                                <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2; border-left: none; border-right: none;"></td>
                                <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2; border-left: none; border-right: none;"></td>
                            @endif
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2; border-left: none;"></td>
                            <td style="border-top: 3px solid #e2e2e2;" class="text-end">
                                {{ $data->sub_total ? (strpos($data->sub_total, '.') === false ? number_format($data->sub_total, 0, ',', '.') : number_format($data->sub_total, 3, ',', '.')) : '0' }}
                            </td>
                            <td style="border-top: 3px solid #e2e2e2;" class="text-end">
                                {{ $data->total_discount ? (strpos($data->total_discount, '.') === false ? number_format($data->total_discount, 0, ',', '.') : number_format($data->total_discount, 3, ',', '.')) : '0' }}
                            </td>
                            <td style="border-top: 3px solid #e2e2e2;" class="text-end">
                                {{ $data->total_sub_amount ? (strpos($data->total_sub_amount, '.') === false ? number_format($data->total_sub_amount, 0, ',', '.') : number_format($data->total_sub_amount, 3, ',', '.')) : '0' }}
                            </td>
                            <td style="border-top: 3px solid #e2e2e2;" class="text-end">
                                {{ $data->total_ppn ? (strpos($data->total_ppn, '.') === false ? number_format($data->total_ppn, 0, ',', '.') : number_format($data->total_ppn, 3, ',', '.')) : '0' }}
                            </td>
                            <td style="border-top: 3px solid #e2e2e2;" class="text-end fw-bold">
                                {{ $data->total_amount ? (strpos($data->total_amount, '.') === false ? number_format($data->total_amount, 0, ',', '.') : number_format($data->total_amount, 3, ',', '.')) : '0' }}
                            </td>
                            <td style="border-top: 3px solid #e2e2e2; border-left: none; border-right: none;"></td>
                            <td style="border-top: 3px solid #e2e2e2; border-left: none;"></td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="card-footer p-4"></div>
        </div>
    </div>
</div>

<script>
    function formatPrice(value) {
        let num = parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
        return num;
    }
    function formatPriceDisplay(value) {
        let formatted = value.toFixed(6).replace('.', ','); // Convert decimal separator
        let parts = formatted.split(",");

        // Apply thousands separator only to the integer part
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        // Remove unnecessary trailing zeros after the comma
        if (parts[1]) {
            parts[1] = parts[1].replace(/0+$/, ""); // Remove trailing zeros
            if (parts[1] === "") return parts[0]; // If decimal part is empty, return only integer part
        }

        return parts.join(",");
    }
    function calculateSubTotal() {
        let qty = formatPrice($('#qty').val()) || 0;
        let price = formatPrice($('#price').val()) || 0;
        let subTotal = qty * price;
        subTotal = Math.round(subTotal * 1e6) / 1e6; 
        $('#sub_total').val(formatPriceDisplay(subTotal));
        calculateAmount();
        calculateTotalAmount();
    }
    $('#qty, #price').on('input', function () {
        calculateSubTotal();
    });

    function calculateAmount() {
        let subTotal = formatPrice($('#sub_total').val()) || 0;
        let disc = formatPrice($('#discount').val()) || 0; 
        let amount = subTotal - disc;
        amount = Math.round(amount * 1e6) / 1e6; 
        $('#amount').val(formatPriceDisplay(amount));
        calculateTotalAmount();
    }
    $('#discount').on('input', function () {
        calculateAmount();
    });

    function calculateTotalAmount() {
        let amount = formatPrice($('#amount').val()) || 0; 
        let taxRate = parseFloat($('#tax_rate').val()) || 0; 
        let taxValue = (taxRate/100) * amount;
        taxValue = Math.round(taxValue * 1e6) / 1e6; 
        $('#tax_value').val(formatPriceDisplay(taxValue));

        let totalAmount = amount + taxValue;
        totalAmount = Math.round(totalAmount * 1e6) / 1e6; 
        $('#total_amount').val(formatPriceDisplay(totalAmount));
    }
    $('#tax_rate').on('input', function () {
        calculateTotalAmount();
    });

    $('#tax_N').on('click', function () {
        $('#tax_rate').val(0,000).prop('readonly', true).addClass('custom-bg-gray');
        calculateTotalAmount();
    });
    $('#tax_Y').on('click', function () {
        $('#tax_rate').prop('readonly', false).removeClass('custom-bg-gray');
    });
</script>

@endsection
