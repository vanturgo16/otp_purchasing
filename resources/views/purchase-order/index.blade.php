@extends('layouts.master')
@section('konten')

<div class="page-content">
    <div class="container-fluid">
        @include('layouts.alert')

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">List Purchase Order (PO)</h5>
                            <div>
                                <a href="" class="btn btn-sm btn-primary waves-effect btn-label waves-light mb-2" data-bs-toggle="modal" data-bs-target="#addPO" title="Tambah PO">
                                    <i class="mdi mdi-plus label-icon"></i> Tambah Data PO
                                </a>
                            </div>
                        </div>
                    </div>
                    {{-- Modal Add --}}
                    <div class="modal fade" id="addPO" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-top modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="staticBackdropLabel">Tambah Purchase Order (PO)</b></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form class="formLoad" action="{{ route('po.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="non_invoiceable" value="N">
                                    <input type="hidden" name="vendor_taxable" value="N">
                                    <div class="modal-body p-4" style="max-height: 65vh; overflow-y: auto;">
                                        <div class="container">
                                            <div class="row mb-4 field-wrapper required-field">
                                                <label class="col-sm-3 col-form-label">Po Number</label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="po_number" class="form-control custom-bg-gray" value="{{ $formattedCode }}" placeholder="Otomatis Terisi.." readonly required>
                                                </div>
                                            </div>
                                            <div class="row mb-4 field-wrapper required-field">
                                                <label class="col-sm-3 col-form-label">Date</label>
                                                <div class="col-sm-9">
                                                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d'), old('date') }}" required>
                                                </div>
                                            </div>
                                            <div class="row mb-4 field-wrapper">
                                                <label class="col-sm-3 col-form-label">Delivery Date</label>
                                                <div class="col-sm-9">
                                                    <input type="date" name="delivery_date" class="form-control" value="{{ date('Y-m-d'), old('delivery_date') }}">
                                                </div>
                                            </div>
                                            <div class="row mb-4 field-wrapper required-field">
                                                <label class="col-sm-3 col-form-label">Reference Number (PR) </label>
                                                <div class="col-sm-9">
                                                    <select class="form-select data-select2" name="reference_number" id="" style="width: 100%" required>
                                                        <option value="">Pilih Reference Number</option>
                                                        @foreach ($postedPRs as $item)
                                                            <option value="{{ $item->id }}">{{ $item->request_number }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mb-4 field-wrapper required-field">
                                                <label class="col-sm-3 col-form-label">Supplier</label>
                                                <div class="col-sm-9">
                                                    <select class="form-select data-select2" name="id_master_suppliers" id="" style="width: 100%" required>
                                                        <option value="">Pilih Suppliers</option>
                                                        @foreach ($suppliers as $item)
                                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mb-4 field-wrapper required-field">
                                                <label class="col-sm-3 col-form-label">Qc Check</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control custom-bg-gray" name="qc_check" value="" placeholder="Otomatis Terisi.." readonly required>
                                                </div>
                                            </div>
                                            <div class="row mb-4 field-wrapper required-field">
                                                <label class="col-sm-3 col-form-label">Down Payment </label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control number-format" name="down_payment" placeholder="Masukkan Down Payment.. (Opsional)" value="0" required>
                                                </div>
                                            </div>
                                            <div class="row mb-4 field-wrapper">
                                                <label class="col-sm-3 col-form-label">Own Remarks </label>
                                                <div class="col-sm-9">
                                                    <textarea name="own_remarks" rows="3" cols="50" class="form-control" placeholder="Remarks.. (Opsional)"></textarea>
                                                </div>
                                            </div>
                                            <div class="row mb-4 field-wrapper">
                                                <label class="col-sm-3 col-form-label">Supplier Remarks </label>
                                                <div class="col-sm-9">
                                                    <textarea name="supplier_remarks" rows="3" cols="50" class="form-control" placeholder="Remarks.. (Opsional)"></textarea>
                                                </div>
                                            </div>
                                            <div class="row mb-4 field-wrapper required-field">
                                                <label class="col-sm-3 col-form-label">Status </label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control custom-bg-gray" name="status" value="Request" readonly required>
                                                </div>
                                            </div>
                                            <div class="row mb-4 field-wrapper required-field">
                                                <label class="col-sm-3 col-form-label">Type </label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control custom-bg-gray" name="type" value="" placeholder="Otomatis Terisi.." readonly required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary waves-effect btn-label waves-light">
                                            <i class="mdi mdi-plus label-icon"></i>Tambah
                                        </button>
                                    </div>
                                </form>
                                <script>
                                    $(document).ready(function() {
                                        $('select[name="reference_number"]').change(function() {
                                            var referenceId = $(this).val();
                                            if (referenceId) {
                                                $.ajax({
                                                    url: "{{ route('pr.getPRDetails') }}",
                                                    method: 'GET',
                                                    data: { reference_id: referenceId },
                                                    success: function(response) {
                                                        if (response.success) {
                                                            $('select[name="id_master_suppliers"]').val(response.data.id_master_suppliers).trigger('change');
                                                            $('input[name="qc_check"]').val(response.data.qc_check);
                                                            $('input[name="type"]').val(response.data.type);
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
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered dt-responsive w-100" id="server-side-table" style="font-size: small">
                            <thead>
                                <tr>
                                    <th class="align-middle text-center">No.</th>
                                    <th class="align-middle text-center">PO Number</th>
                                    <th class="align-middle text-center">Date</th>
                                    <th class="align-middle text-center">Suppliers</th>
                                    <th class="align-middle text-center">Reference Number (PR)</th>
                                    <th class="align-middle text-center">Down Payment</th>
                                    <th class="align-middle text-center">Total Amount</th>
                                    <th class="align-middle text-center">QC Check</th>
                                    <th class="align-middle text-center">Type</th>
                                    <th class="align-middle text-center">Total Product</th>
                                    <th class="align-middle text-center">Status</th>
                                    <th class="align-middle text-center">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var url = '{!! route('po.index') !!}';

        var dataTable = $('#server-side-table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 5,
            aaSorting: [],
            ajax: {
                url: url,
                type: 'GET',
                data: function(d) {
                    d.filterType = $('#filterType').val();
                    d.filterStatus = $('#filterStatus').val();
                }
            },
            columns: [
                {
                data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                    orderable: false,
                    searchable: false,
                    className: 'text-center fw-bold',
                },
                {
                    data: 'po_number',
                    name: 'po_number',
                    orderable: true,
                    searchable: true,
                    className: 'align-top fw-bold'
                },
                {
                    data: 'date',
                    searchable: true,
                    orderable: true,
                    className: 'align-top text-center',
                },
                {
                    data: 'supplier_name',
                    name: 'supplier_name',
                    orderable: true,
                    searchable: true,
                    className: 'align-top'
                },
                {
                    data: 'reference_number',
                    name: 'reference_number',
                    orderable: true,
                    className: 'align-top'
                },
                {
                    data: 'down_payment',
                    name: 'down_payment',
                    orderable: true,
                    className: 'align-top text-center',
                    render: function(data, type, row) {
                        if (data) {
                            let parts = data.split('.');
                            let integerPart = parts[0];
                            let decimalPart = parts[1] || '';
                            integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            if (decimalPart) {
                                return `${integerPart},${decimalPart}`;
                            }
                            return integerPart;
                        }
                        return '';
                    }
                },
                {
                    data: 'total_amount',
                    name: 'total_amount',
                    orderable: true,
                    className: 'align-top text-center',
                    render: function(data, type, row) {
                        if (data) {
                            let parts = data.split('.');
                            let integerPart = parts[0];
                            let decimalPart = parts[1] || '';
                            integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            if (decimalPart) {
                                return `${integerPart},${decimalPart}`;
                            }
                            return integerPart;
                        }
                        return '';
                    }
                },
                {
                    data: 'qc_check',
                    name: 'qc_check',
                    orderable: true,
                    searchable: true,
                    className: 'align-top text-center'
                },
                {
                    data: 'type',
                    name: 'type',
                    orderable: true,
                    searchable: true,
                    className: 'align-top text-center fw-bold'
                },
                {
                    data: 'count',
                    name: 'count',
                    orderable: true,
                    searchable: true,
                    className: 'align-top text-center'
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: true,
                    searchable: true,
                    className: 'align-top text-center',
                    render: function(data, type, row) {
                        let badgeColor = data === 'Request' ? 'secondary' : 
                                        data === 'Un Posted' ? 'warning' : 'success';
                        return `<span class="badge bg-${badgeColor}" style="font-size: smaller; width: 100%">${data}</span>`;
                    },
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'align-top text-center',
                },
            ],
            createdRow: function(row, data, dataIndex) {
                if (data.status === 'Posted') {
                    $(row).addClass('table-success');
                }
                if (data.status === 'Request') {
                    $(row).addClass('table-secondary');
                }
            },
            columnDefs: [
                {
                    width: '10%',
                    targets: [2],
                },
                {
                    width: '10%',
                    targets: [11],
                },
            ],
        });
    });
</script>

<script>
    $(function() {
        // Hide Length Datatable
        $('.dataTables_wrapper .dataTables_length').hide();

        // Length
        var lengthDropdown = `
            <label>
                <select id="lengthDT">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </label>
        `;
        $('.dataTables_length').before(lengthDropdown);
        $('#lengthDT').select2({ minimumResultsForSearch: Infinity, width: '60px' });
        $('#lengthDT').on('change', function() {
            var newLength = $(this).val();
            var table = $("#server-side-table").DataTable();
            table.page.len(newLength).draw();
        });

        // Filter Type
        var filterType = `
            <label>
                <select id="filterType">
                    <option value="All">-- Semua Type --</option>
                    <option value="RM">RM</option>
                    <option value="WIP">WIP</option>
                    <option value="FG">FG</option>
                    <option value="TA">TA</option>
                    <option value="Other">Other</option>
                </select>
            </label>
        `;
        $('.dataTables_length').before(filterType);
        $('#filterType').select2({width: '150px' });
        $('#filterType').on('change', function() { $("#server-side-table").DataTable().ajax.reload(); });

        // Filter Status
        var filterStatus = `
            <label>
                <select id="filterStatus">
                    <option value="All">-- Semua Status --</option>
                    <option value="Request">Request</option>
                    <option value="Posted">Posted</option>
                    <option value="Closed">Closed</option>
                    <option value="Un Posted">Un Posted</option>
                </select>
            </label>
        `;
        $('.dataTables_length').before(filterStatus);
        $('#filterStatus').select2({width: '200px' });
        $('#filterStatus').on('change', function() { $("#server-side-table").DataTable().ajax.reload(); });
    });
</script>

@endsection