@extends('layouts.master')
@section('konten')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div class="page-title-left">
                        <a href="{{ route('po.index') }}" class="btn btn-light waves-effect btn-label waves-light">
                            <i class="mdi mdi-arrow-left label-icon"></i> Back To List Purchase Order
                        </a>
                    </div>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase</a></li>
                            <li class="breadcrumb-item active"> Edit Purchase Order {{ $data->type }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.alert')
        {{-- DATA PO --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Edit Purchase Order</h4>
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
                            <input type="date" name="date" class="form-control" value="{{ $data->date }}" required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label for="horizontal-email-input" class="col-sm-3 col-form-label">Delivery Date</label>
                        <div class="col-sm-9">
                            <input type="date" name="delivery_date" class="form-control" value="{{ $data->delivery_date }}">
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">
                            Reference Number (PR)
                            <i class="mdi mdi-information-outline" data-bs-toggle="tooltip" data-bs-placement="top" title="Mengubah Nomor Referensi akan memperbarui item produk sesuai Purchase Request."></i>
                        </label>
                        <div class="col-sm-9">
                            <select class="form-select data-select2" name="reference_number" id="reference_number" style="width: 100%" required>
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
                                                // if (response.data.qc_check === 'Y') {
                                                //     $('#qc_check_Y').prop('checked', true);
                                                // } else if (response.data.qc_check === 'N') {
                                                //     $('#qc_check_N').prop('checked', true);
                                                // }
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
                    {{-- <div class="row mb-4 field-wrapper required-field">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Qc Check </label>
                        <div class="col-sm-9">
                            <input type="radio" id="qc_check_Y" name="qc_check" value="Y" {{ $data->qc_check == 'Y' ? 'checked' : '' }} required>
                            <label for="qc_check_Y">Y</label>
                            <input type="radio" id="qc_check_N" name="qc_check" value="N" {{ $data->qc_check == 'N' ? 'checked' : '' }}>
                            <label for="qc_check_N">N</label>
                        </div>
                    </div> --}}
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
                            <input type="text" class="form-control number-format" name="down_payment" placeholder="Masukkan Down Payment.. (Opsional)" 
                                value="{{ $data->down_payment ? (strpos($data->down_payment, '.') === false ? number_format($data->down_payment, 0, ',', '.') : number_format($data->down_payment, 3, ',', '.')) : '' }}" 
                                required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Own Remarks </label>
                        <div class="col-sm-9">
                            <textarea name="own_remarks" rows="3" cols="50" class="form-control" placeholder="Remarks.. (Opsional)">{{ $data->own_remarks }}</textarea>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Supplier Remarks </label>
                        <div class="col-sm-9">
                            <textarea name="supplier_remarks" rows="3" cols="50" class="form-control" placeholder="Remarks.. (Opsional)">{{ $data->supplier_remarks }}</textarea>
                        </div>
                    </div>
                </div>
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
            </form>
        </div>

        {{-- LIST ITEM --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">List Item Product <b>"{{ $data->type }}"</b></h4>
            </div>
            <div class="card-body p-4">
                {{-- <a href="" class="btn btn-info waves-effect btn-label waves-light mb-2" data-bs-toggle="modal" data-bs-target="#addProduct">
                    <i class="mdi mdi-plus label-icon"></i> Tambah Product <b>"{{ $data->type }}"</b>
                </a> --}}
                <table class="table table-bordered dt-responsive w-100" style="font-size: small" id="tableItem">
                    <thead>
                        <tr>
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">No.</th>
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Product</th>
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Qty</th>
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Currency</th>
                            <th class="align-middle text-center" colspan="6" style="background-color: #6C7AE0; color:#ffff;">Detail Price</th>
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Note</th>
                            <th class="align-middle text-center" rowspan="2" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Aksi</th>
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
                                <td class="align-top">
                                    <b>{{ $item->type_product }}</b>
                                    <br>{!! implode('<br>', array_map(fn($chunk) => implode(' ', $chunk), array_chunk(explode(' ', $item->product_desc), 10))) !!}  {{-- max 10 word one line --}}
                                </td>
                                <td class="text-center">
                                    <b>{{ $item->qty }}</b>
                                    <br>({{ $item->unit_code }})
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
                                <td>
                                    <span title="{{ strlen($item->note) > 70 ? $item->note : '' }}">
                                      {{ strlen($item->note) > 70 ? substr($item->note, 0, 70) . '...' : $item->note }}
                                    </span>
                                </td>
                                <td class="align-top text-center">
                                    <a href="{{ route('po.editItem', encrypt($item->id)) }}">
                                        <button type="button" class="btn btn-sm btn-info my-half"><i class="bx bx-edit-alt" title="Edit Data"></i></button>
                                    </a>
                                    {{-- <button type="submit" class="btn btn-sm btn-danger my-half" data-bs-toggle="modal" data-bs-target="#delete{{ $item->id }}">
                                        <i class="bx bx-trash-alt" title="Hapus Data"></i>
                                    </button> --}}
                                </td>
                            </tr>
                            {{-- Modal Delete --}}
                            {{-- <div class="modal fade" id="delete{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-top" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="staticBackdropLabel">Delete</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('po.deleteItem', encrypt($item->id)) }}" method="POST" enctype="multipart/form-data" id="formDelete{{ $item->id }}">
                                            @csrf
                                            <input type="hidden" name="id_purchase_orders" value="{{ $item->id_purchase_orders }}">
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
                                        <script>
                                            var idList = "{{ $item->id }}";
                                            $('#formDelete' + idList).submit(function() {
                                                if (!$('#formDelete' + idList).valid()) return false;
                                                $('#btnFormDelete' + idList).attr("disabled", "disabled");
                                                $('#btnFormDelete' + idList).html('<i class="mdi mdi-loading mdi-spin label-icon"></i>Please Wait...');
                                                return true;
                                            });
                                        </script>
                                    </div>
                                </div>
                            </div> --}}
                        @endforeach
                        @if(!$itemDatas->isEmpty())
                        <tr>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2; border-left: none; border-right: none;"></td>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2; border-left: none; border-right: none;" class="text-end"><b>Total</b></td>
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
                            <td style="border-top: 3px solid #e2e2e2; border-left: none;"></td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            
            {{-- Modal Add --}}
            {{-- <div class="modal fade" id="addProduct" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-top modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">Tambah Product <b>"{{ $data->type }}"</b></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="formLoad" action="{{ route('po.storeItem', encrypt($data->id)) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id_purchase_orders" value="{{ $data->id }}">
                            <div class="modal-body p-4" style="max-height: 65vh; overflow-y: auto;">
                                <div class="container">
                                    <div class="row mb-2 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Type Product</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control custom-bg-gray" placeholder="Masukkan Type Product.." name="type_product" value="{{ $data->type }}" readonly required>
                                        </div>
                                    </div>
                                    <div class="row mb-2 field-wrapper required-field">
                                        <label class="col-sm-3 col-form-label">Product {{ $data->type }}</label>
                                        <div class="col-sm-9">
                                            <select class="form-select request_number data-select2" name="master_products_id" style="width: 100%" required>
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
                                    <br><br>
                                    
                                    <div class="row mb-2 field-wrapper required-field">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Qty</label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" placeholder="Masukkan Qty.." name="qty" id="qty" value="" required>
                                        </div>
                                    </div>
                                    <div class="row mb-2 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Units </label>
                                        <div class="col-sm-9">
                                            <select class="form-select data-select2" name="master_units_id" id="unit_code" style="width: 100%" required>
                                                <option>Pilih Units</option>
                                                @foreach ($units as $item)
                                                    <option value="{{ $item->id }}">
                                                        {{ $item->unit_code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-2 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Currency</label>
                                        <div class="col-sm-9">
                                            <select class="form-select data-select2" name="currency" id="" style="width: 100%" required>
                                                <option>Pilih Currency</option>
                                                @foreach ($currency as $item)
                                                    <option value="{{ $item->currency_code }}">
                                                        {{ $item->currency_code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-2 field-wrapper required-field">
                                        <label class="col-sm-3 col-form-label">Price </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control number-format" placeholder="Masukkan Price.." name="price" id="price" value="" required>
                                        </div>
                                    </div>
                                    <div class="row mb-2 field-wrapper required-field">
                                        <label class="col-sm-3 col-form-label">Sub Total</label>
                                        <div class="col-sm-9">
                                            <div class="input-group mb-3">
                                                <input type="text" class="form-control custom-bg-gray" placeholder="Sub Total.. (Terisi Otomatis)" value="" name="sub_total" id="sub_total" readonly>
                                                <span class="input-group-text">(Qty * Price)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <br><br>
        
                                    <div class="row mb-2 field-wrapper required-field">
                                        <label class="col-sm-3 col-form-label">Discount </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control number-format" placeholder="Masukkan Discount.." name="discount" id="discount" value="" required>
                                        </div>
                                    </div>
                                    <div class="row mb-2 field-wrapper required-field">
                                        <label class="col-sm-3 col-form-label">Amount</label>
                                        <div class="col-sm-9">
                                            <div class="input-group mb-3">
                                                <input type="text" class="form-control custom-bg-gray" placeholder="Amount.. (Terisi Otomatis)" name="amount" id="amount" value="" readonly>
                                                <span class="input-group-text">(Sub Total - Discount)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <br><br>
        
                                    <div class="row mb-2 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tax</label>
                                        <div class="col-sm-9">
                                            <input type="radio" id="tax_Y" name="tax" value="Y" required>
                                            <label for="tax_Y">Y</label>
                                            <input type="radio" id="tax_N" name="tax" value="N">
                                            <label for="tax_N">N</label>
                                        </div>
                                    </div>
                                    <div class="row mb-2 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tax Rate (%)</label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" placeholder="Masukkan Tax.." name="tax_rate" id="tax_rate" value="" required>
                                        </div>
                                    </div>
                                    <div class="row mb-2 field-wrapper required-field">
                                        <label class="col-sm-3 col-form-label">Tax Value </label>
                                        <div class="col-sm-9">
                                            <div class="input-group mb-3">
                                                <input type="text" class="form-control custom-bg-gray" placeholder="Tax Value.. (Terisi Otomatis)" name="tax_value" id="tax_value" value="" readonly>
                                                <span class="input-group-text">(Tax Rate/100 * Amount)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <br><br>

                                    <div class="row mb-2 field-wrapper required-field">
                                        <label class="col-sm-3 col-form-label">Total Amount </label>
                                        <div class="col-sm-9">
                                            <div class="input-group mb-3">
                                                <input type="text" class="form-control custom-bg-gray" placeholder="Total Amount.. (Terisi Otomatis)" name="total_amount" id="total_amount" value="" readonly>
                                                <span class="input-group-text">(Amount + Tax)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Note</label>
                                        <div class="col-sm-9">
                                            <textarea name="note" rows="4" cols="50" class="form-control" placeholder="Note.. (Opsional)"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary waves-effect btn-label waves-light">
                                    <i class="mdi mdi-plus label-icon"></i>Tambah Ke Tabel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div> --}}
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
        let formatted = value.toFixed(3).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        if (formatted.endsWith(',000')) {
            formatted = formatted.slice(0, -4);
        }
        return formatted;
    }
    function calculateSubTotal() {
        let qty = parseFloat($('#qty').val()) || 0;
        let price = formatPrice($('#price').val()) || 0;
        let subTotal = qty * price;
        subTotal = Math.round(subTotal * 1000) / 1000; // Round to 3 decimal places
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
        amount = Math.round(amount * 1000) / 1000;
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
        taxValue = Math.round(taxValue * 1000) / 1000;
        $('#tax_value').val(formatPriceDisplay(taxValue));

        let totalAmount = amount + taxValue;
        totalAmount = Math.round(totalAmount * 1000) / 1000;
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
