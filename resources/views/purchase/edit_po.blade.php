@extends('layouts.master')
@section('konten')

<div class="page-content">
    <div class="container-fluid">
        @include('layouts.alert')
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div class="page-title-left">
                        <a href="{{ route('purchase_order') }}" class="btn btn-light waves-effect btn-label waves-light">
                            <i class="mdi mdi-arrow-left label-icon"></i> Back To List Purchase Order
                        </a>
                    </div>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase</a></li>
                            <li class="breadcrumb-item active"> Edit Purchase Order {{ $data->type; }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- DATA PO --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Edit Purchase Order</h4>
            </div>
            <form method="post" action="/update_po/{{ $data->id; }}" class="form-material m-t-40" enctype="multipart/form-data">
                @csrf
                <div class="card-body p-4">
                    <div class="row mb-4 field-wrapper required-field">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Po Number</label>
                        <div class="col-sm-9">
                            <input type="text" name="po_number" class="form-control custom-bg-gray" value="{{ $data->po_number; }}" readonly>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label for="horizontal-email-input" class="col-sm-3 col-form-label">Date</label>
                        <div class="col-sm-9">
                            <input type="date" name="date" class="form-control" value="{{ $data->date; }}">
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label for="horizontal-email-input" class="col-sm-3 col-form-label">Delivery Date</label>
                        <div class="col-sm-9">
                            <input type="date" name="delivery_date" class="form-control" value="{{ $data->delivery_date; }}">
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Reference Number (PR) </label>
                        <div class="col-sm-9">
                            <select class="form-select data-select2" name="reference_number" id="">
                                <option value="">Pilih Reference Number</option>
                                @foreach ($reference_number as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == $data->reference_number ? 'selected' : '' }}>{{ $item->request_number }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Supplier </label>
                        <div class="col-sm-9">
                            <select class="form-select data-select2" name="id_master_suppliers" id="">
                                <option value="">Pilih Suppliers</option>
                                @foreach ($supplier as $item)
                                <option value="{{ $item->id }}" {{ $item->id == $data->id_master_suppliers ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Qc Check </label>
                        <div class="col-sm-9">
                            <input type="radio" id="qc_check_Y" name="qc_check" value="Y" {{ $data->qc_check == 'Y' ? 'checked' : '' }}>
                            <label for="qc_check_Y">Y</label>
                            <input type="radio" id="qc_check_N" name="qc_check" value="N" {{ $data->qc_check == 'N' ? 'checked' : '' }}>
                            <label for="qc_check_N">N</label>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Down Payment </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="down_payment" placeholder="Masukkan Down Payment.. (Opsional)" value="{{ $data->down_payment }}">
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Own Remarks </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="own_remarks" placeholder="Masukkan Remarks.. (Opsional)" value="{{ $data->own_remarks }}" >
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Supplier Remarks </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="supplier_remarks" placeholder="Masukkan Remarks.. (Opsional)" value="{{ $data->supplier_remarks }}" >
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Status </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control custom-bg-gray" name="status" value="{{ $data->status; }}" readonly>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Type </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control custom-bg-gray" name="type" value="{{ $data->type; }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row text-end">
                        <div>
                            <button type="reset" class="btn btn-info w-md">Reset</button>
                            <button type="submit" class="btn btn-primary w-md">Update</button>
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
                <a href="" class="btn btn-sm btn-primary waves-effect btn-label waves-light mb-2">
                    <i class="mdi mdi-plus label-icon"></i> Tambah Product <b>"{{ $data->type }}"</b>
                </a>
                <table id="datatableCustom" class="table table-bordered dt-responsive nowrap w-100">
                    <thead>
                        <tr>
                            <th class="align-middle text-center" rowspan="2">No.</th>
                            <th class="align-middle text-center" rowspan="2">Product</th>
                            <th class="align-middle text-center" rowspan="2">Qty</th>
                            <th class="align-middle text-center" rowspan="2">Units</th>
                            <th class="align-middle text-center" rowspan="2">Currency</th>
                            <th class="align-middle text-center" colspan="6">Detail Price</th>
                            <th class="align-middle text-center" rowspan="2">Note</th>
                            <th class="align-middle text-center" rowspan="2">Aksi</th>
                        </tr>
                        <tr>
                            <th class="align-middle text-center">Price</th>
                            <th class="align-middle text-center">Sub Total</th>
                            <th class="align-middle text-center">Discount</th>
                            <th class="align-middle text-center">Amount</th>
                            <th class="align-middle text-center">Tax</th>
                            <th class="align-middle text-center">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($itemDatas as $item)
                            <tr>
                                <td class="align-top text-center"><b>{{ $loop->iteration }}</b></td>
                                <td class="align-middle">
                                    <b>{{ $item->type_product }}</b>
                                    <br>{{ $item->product_desc }}
                                </td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td class="text-center">{{ $item->unit }}</td>
                                <td class="text-center">{{ $item->currency ?? '-' }}</td>
                                <td class="text-end">{{ $item->price ?? '0' }}</td>
                                <td class="text-end">{{ $item->price ?? '0' }}</td>
                                <td class="text-end">{{ $item->discount ?? '0' }}</td>
                                <td class="text-end">{{ $item->discount ?? '0' }}</td>
                                <td class="text-end">{{ $item->tax ?? '0' }}</td>
                                <td class="text-end">{{ $item->amount ?? '0' }}</td>
                                <td>{{ $item->note ?? '-' }}</td>
                                <td class="align-top text-center">
                                    <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#delete{{ $item->id }}">
                                        <i class="bx bx-trash-alt" title="Hapus Data"></i>
                                    </button>
                                    <a href="{{ route('edit_po_item', encrypt($item->id)) }}">
                                        <button type="button" class="btn btn-sm btn-info"><i class="bx bx-edit-alt" title="Edit Data"></i></button>
                                    </a>
                                </td>
                            </tr>
                            <div class="modal fade" id="delete{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-top" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="staticBackdropLabel">Delete</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form class="formLoad" action="" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="text-center">
                                                    Apakah Anda Yakin Untuk <b>Menghapus</b> Data?
                                                    <br><b>"{{ $item->product_desc }}"</b>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-danger waves-effect btn-label waves-light">
                                                    <i class="mdi mdi-delete-alert label-icon"></i>Delete
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <tr>
                            <td style="border-top: 3px solid #e2e2e2;"></td>
                            <td class="text-center" style="border-top: 3px solid #e2e2e2;"><b>Total</b></td>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2;"></td>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2;"></td>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2;"></td>
                            <td style="border-top: 3px solid #e2e2e2;" class="text-end">0</td>
                            <td style="border-top: 3px solid #e2e2e2;" class="text-end">0</td>
                            <td style="border-top: 3px solid #e2e2e2;" class="text-end">0</td>
                            <td style="border-top: 3px solid #e2e2e2;" class="text-end">0</td>
                            <td style="border-top: 3px solid #e2e2e2;" class="text-end">0</td>
                            <td style="border-top: 3px solid #e2e2e2;" class="text-end">0</td>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2;"></td>
                            <td style="background-color: #f0f0f0; border-top: 3px solid #e2e2e2;"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer p-4"></div>
        </div>
    </div>
</div>
@endsection
