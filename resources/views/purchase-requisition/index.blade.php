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
                            <h5 class="mb-0">List Purchase Requisition (PR)</h5>
                            <div>
                                <a href="{{ route('pr.add', 'RM') }}" class="btn btn-sm btn-primary waves-effect btn-label waves-light" title="Tambah PR Raw Material">
                                    <i class="mdi mdi-plus label-icon"></i> PR <b>(RM)</b>
                                </a>
                                <a href="{{ route('pr.add', 'WIP') }}" class="btn btn-sm btn-primary waves-effect btn-label waves-light" title="Tambah PR WIP">
                                    <i class="mdi mdi-plus label-icon"></i> PR <b>(WIP)</b>
                                </a>
                                <a href="{{ route('pr.add', 'FG') }}" class="btn btn-sm btn-primary waves-effect btn-label waves-light" title="Tambah PR Finished Goods">
                                    <i class="mdi mdi-plus label-icon"></i> PR <b>(FG)</b>
                                </a>
                                <a href="{{ route('pr.add', 'TA') }}" class="btn btn-sm btn-primary waves-effect btn-label waves-light" title="Tambah PR Auxalary & Sparepart">
                                    <i class="mdi mdi-plus label-icon"></i> PR <b>(Aux & Sparepart)</b>
                                </a>
                                <a href="{{ route('pr.add', 'Other') }}" class="btn btn-sm btn-primary waves-effect btn-label waves-light" title="Tambah PR Lainnya">
                                    <i class="mdi mdi-plus label-icon"></i> PR <b>(Other)</b>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered dt-responsive w-100" id="server-side-table" style="font-size: small">
                            <thead>
                                <tr>
                                    <th class="align-middle text-center">No.</th>
                                    <th class="align-middle text-center">Request Number</th>
                                    <th class="align-middle text-center">Date</th>
                                    <th class="align-middle text-center">Suppliers</th>
                                    <th class="align-middle text-center">Requester</th>
                                    <th class="align-middle text-center">QC Check</th>
                                    <th class="align-middle text-center">Note</th>
                                    <th class="align-middle text-center">PO Number</th>
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
        var url = '{!! route('pr.index') !!}';

        var dataTable = $('#server-side-table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 5,
            lengthMenu: [
                [5, 10, 20, 25, 50, 100, 200, -1],
                [5, 10, 20, 25, 50, 100, 200, "All"]
            ],
            aaSorting: [],
            ajax: {
                url: url,
                type: 'GET'
            },
            columns: [
                {
                data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                },
                {
                    data: 'request_number',
                    name: 'request_number',
                    orderable: true,
                    searchable: true,
                    className: 'align-top fw-bold'
                },
                {
                    data: 'requisition_date',
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
                    data: 'nm_requester',
                    name: 'nm_requester',
                    orderable: true,
                    searchable: true,
                    className: 'align-top'
                },
                {
                    data: 'qc_check',
                    name: 'qc_check',
                    orderable: true,
                    searchable: true,
                    className: 'align-top text-center'
                },
                {
                    data: 'note',
                    name: 'note',
                    orderable: true,
                    searchable: true,
                    className: 'align-top',
                    render: function (data, type, row) {
                        if (data.length > 100) {
                            return `<span class="note-tooltip" title="${data}">${data.substring(0, 70)}...</span>`;
                        }
                        return data;
                    }
                },
                {
                    data: 'po_number',
                    name: 'po_number',
                    orderable: true,
                    searchable: true,
                    className: 'align-top'
                },
                {
                    data: 'type',
                    name: 'type',
                    orderable: true,
                    searchable: true,
                    className: 'align-top text-center'
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

@endsection