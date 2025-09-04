@extends('layouts.master')
@section('konten')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div class="page-title-left">
                        <form action="{{ route('pr.price.index') }}" method="GET" id="resetForm" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="idUpdated" value="{{ $prPrice->id }}">
                            <button type="submit" class="btn btn-light waves-effect btn-label waves-light">
                                <i class="mdi mdi-arrow-left label-icon"></i> Back To List Purchase Requisition Price 
                            </button>
                        </form>
                        {{-- <a href="{{ route('pr.price.index') }}" class="btn btn-light waves-effect btn-label waves-light">
                            <i class="mdi mdi-arrow-left label-icon"></i> Back To List Purchase Requisition Price
                        </a> --}}
                    </div>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase</a></li>
                            <li class="breadcrumb-item active"> {{ (in_array($prPrice->status, ['Posted', 'Closed'])) ? 'Detail' : 'Edit' }} PR Price ({{ $data->type }})</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.alert')
        {{-- DATA PR --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ (in_array($prPrice->status, ['Posted', 'Closed'])) ? 'Detail' : 'Edit' }} Purchase Requisition Price ({{ $data->type }})</h4>
            </div>
            <form method="POST" action="{{ route('pr.price.update', encrypt($prPrice->id)) }}" class="form-material m-t-40 formLoad" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="reference_number_before" value="{{ $data->id }}">
                <div class="card-body p-4">
                    <div class="row mb-4 field-wrapper {{ (in_array($prPrice->status, ['Posted', 'Closed'])) ? '' : 'required-field' }}">
                        @if(!in_array($prPrice->status, ['Posted', 'Closed']))
                            <label for="horizontal-password-input" class="col-sm-3 col-form-label">
                                Reference Number (PR)
                                <i class="mdi mdi-information-outline" data-bs-toggle="tooltip" data-bs-placement="top" title="Mengubah Nomor Referensi akan memperbarui item produk sesuai Purchase Request."></i>
                            </label>
                            <div class="col-sm-9">
                                <select class="form-select data-select2" name="reference_number" id="reference_number" style="width: 100%" required>
                                    <option value="">Pilih Reference Number</option>
                                    @foreach ($reference_number as $item)
                                        <option value="{{ $item->id }}" {{ $item->id == $data->id ? 'selected' : '' }}>
                                            {{ $item->request_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else 
                            <label class="col-sm-3 col-form-label">Reference Number (PR)</label>
                            <div class="col-sm-9">
                                <input type="text" name="reference_number" class="form-control custom-bg-gray" value="{{ $data->request_number }}" readonly required>
                            </div>
                        @endif
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
                                        url: "{{ route('pr.price.getPRDetails') }}",
                                        method: 'GET',
                                        data: { reference_id: referenceId },
                                        success: function(response) {
                                            if (response.success) {
                                                $('input[name="date"]').val(response.data.date);
                                                $('input[name="id_master_suppliers"]').val(response.data.supplier_name);
                                                $('input[name="requester"]').val(response.data.nm_requester);
                                                $('input[name="qc_check"]').val(response.data.qc_check);
                                                $('textarea[name="note"]').val(response.data.note);
                                                $('textarea[name="note"]').html(response.data.note);
                                            } else {
                                                alert('No data found for this reference number.');
                                            }
                                        },
                                        error: function() {
                                            alert('Error fetching data. Please try again.');
                                        }
                                    });
                                } else {
                                    $('input[name="date"]').val('');
                                    $('input[name="id_master_suppliers"]').val('');
                                    $('input[name="requester"]').val('');
                                    $('input[name="qc_check"]').val('');
                                    $('textarea[name="note"]').val('');
                                    $('textarea[name="note"]').html('');
                                }
                            });
                        });
                    </script>
                    <div class="row mb-4 field-wrapper {{ (in_array($prPrice->status, ['Posted', 'Closed'])) ? '' : 'required-field' }}">
                        <label class="col-sm-3 col-form-label">Date</label>
                        <div class="col-sm-9">
                            <input type="text" name="date" class="form-control custom-bg-gray" value="{{ $data->date }}" readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper {{ (in_array($prPrice->status, ['Posted', 'Closed'])) ? '' : 'required-field' }}">
                        <label class="col-sm-3 col-form-label">Suppliers</label>
                        <div class="col-sm-9">
                            <input type="text" name="id_master_suppliers" class="form-control custom-bg-gray" value="{{ $data->supplier_name }}" readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper {{ (in_array($prPrice->status, ['Posted', 'Closed'])) ? '' : 'required-field' }}">
                        <label class="col-sm-3 col-form-label">Requester</label>
                        <div class="col-sm-9">
                            <input type="text" name="requester" class="form-control custom-bg-gray" value="{{ $data->nm_requester }}" readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper {{ (in_array($prPrice->status, ['Posted', 'Closed'])) ? '' : 'required-field' }}">
                        <label class="col-sm-3 col-form-label">Qc Check </label>
                        <div class="col-sm-9">
                            <input type="text" name="qc_check" class="form-control custom-bg-gray" value="{{ $data->qc_check }}" readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label class="col-sm-3 col-form-label">Note</label>
                        <div class="col-sm-9">
                            <textarea name="note" rows="3" cols="50" class="form-control custom-bg-gray" placeholder="Note.. (Opsional)" readonly>{{ $data->note }}</textarea>
                        </div>
                    </div>
                </div>
                @if(!in_array($prPrice->status, ['Posted', 'Closed']))
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
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Required Date</th>
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">CC / CO</th>
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Qty</th>
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Cancel Qty</th>
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Outstanding Qty</th>
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Currency</th>
                            <th class="align-middle text-center" colspan="6" style="background-color: #6C7AE0; color:#ffff;">Detail Price</th>
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Status</th>
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Remarks</th>
                            @if(!in_array($prPrice->status, ['Posted', 'Closed']))
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2; border-left: 3px solid #e2e2e2;">Aksi</th>
                            @endif
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
                                    <br>{!! implode('<br>', array_map(fn($chunk) => implode(' ', $chunk), array_chunk(explode(' ', $item->product_desc), 10))) !!}
                                </td>
                                <td class="text-center">{{ $item->required_date}}</td>
                                <td class="align-top">{{ $item->cc_co_name}}</td>
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
                                <td class="text-center">
                                    {{ $item->cancel_qty 
                                        ? (strpos(strval($item->cancel_qty), '.') !== false 
                                            ? rtrim(rtrim(number_format($item->cancel_qty, 6, ',', '.'), '0'), ',') 
                                            : number_format($item->cancel_qty, 0, ',', '.')) 
                                        : '0' }}
                                </td>
                                <td class="text-center">
                                    {{ $item->outstanding_qty 
                                        ? (strpos(strval($item->outstanding_qty), '.') !== false 
                                            ? rtrim(rtrim(number_format($item->outstanding_qty, 6, ',', '.'), '0'), ',') 
                                            : number_format($item->outstanding_qty, 0, ',', '.')) 
                                        : '0' }}
                                </td>
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
                                <td class="align-top text-center">
                                    <span class="badge bg-{{ $item->status == 'Open' ? 'info' : 'success' }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td>
                                    {!! implode('<br>', array_map(fn($chunk) => implode(' ', $chunk), array_chunk(explode(' ', $item->remarks), 10))) !!}
                                </td>
                                @if(!in_array($prPrice->status, ['Posted', 'Closed']))
                                <td class="align-top text-center" style="border-left: 3px solid #e2e2e2;">
                                    @if($data->status == 'Un Posted')
                                        <span class="badge bg-warning">PR Sedang UnPost</span>
                                    @else
                                        <a href="{{ route('pr.price.editItem', encrypt($item->id)) }}">
                                            <button type="button" class="btn btn-sm btn-info my-half"><i class="bx bx-edit-alt" title="Edit Data"></i></button>
                                        </a>
                                    @endif
                                </td>
                                @endif
                            </tr>
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
                        @if(!$itemDatas->isEmpty())
                        <tr>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2; border-left: none; border-right: none;"></td>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2; border-left: none; border-right: none;" class="text-end"><b>Total</b></td>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2; border-left: none; border-right: none;"></td>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2; border-left: none; border-right: none;"></td>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2; border-left: none; border-right: none;"></td>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2; border-left: none; border-right: none;"></td>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2; border-left: none; border-right: none;"></td>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2; border-left: none; border-right: none;"></td>
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
                            <td style="border-top: 3px solid #e2e2e2; border-left: none; border-right: none;"></td>
                            @if(!in_array($prPrice->status, ['Posted', 'Closed']))
                            <td style="border-top: 3px solid #e2e2e2; border-left: none;"></td>
                            @endif
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


@endsection
