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
            scrollX: true,
            responsive: false,
            fixedColumns: {
                leftColumns: 2, // Freeze first two columns
                rightColumns: 1 // Freeze last column (Aksi)
            },
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
                    className: 'text-center fw-bold freeze-column',
                },
                {
                    data: 'request_number',
                    name: 'request_number',
                    orderable: true,
                    searchable: true,
                    className: 'align-top fw-bold freeze-column'
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
                        if (!data) { return ''; }
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
                    className: 'align-top',
                    render: function (data, type, row) {
                        if(row.input_price === 'Y'){
                            return `<span class="badge bg-info text-white">With Price</span>`;
                        } else {
                            return data;
                        }
                    }
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
                    render: function(data) {
                        const badgeColor = data === 'Closed' ? 'success' : 
                                        (data === 'Request' ? 'secondary' : 
                                        (data === 'Un Posted' ? 'warning' : 'success'));
                        const icon = data === 'Closed' ? '<i class="bx bx-check-circle"></i>' : '';
                        return `<span class="badge bg-${badgeColor}" style="font-size: smaller; width: 100%">${icon} ${data}</span>`;
                    },
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'align-top text-center freeze-column',
                },
            ],
            createdRow: function(row, data, dataIndex) {
                let bgColor = '';
                let darkColor = '#FAFAFA';
                if (['Posted', 'Created PO', 'Created GRN'].includes(data.status)) {
                    bgColor = 'table-success';
                    darkColor = '#CFEBE0';
                }
                if (data.status === 'Closed') {
                    bgColor = 'table-success-closed';
                    darkColor = '#a6eed1';
                }
                if (data.status === 'Request') {
                    bgColor = 'table-secondary';
                    darkColor = '#DFE0E3';
                }
                if (data.status === 'Un Posted') {
                    bgColor = 'table-warning';
                    darkColor = '#FFF3CB';
                }
                if (bgColor) {
                    $(row).addClass(bgColor);
                }
                $(row).find('.freeze-column').css('background-color', darkColor);
            },
        });
        $('.dataTables_scrollHeadInner thead th').each(function(index) {
            let $this = $(this);
            let isFrozenColumn = index < 2 || index === $('.dataTables_scrollHeadInner thead th').length - 1;
            if (isFrozenColumn) {
                $this.css({
                    'background-color': '#FAFAFA',
                    'position': 'sticky',
                    'z-index': '3',
                    'left': index < 2 ? ($this.outerWidth() * index) + 'px' : 'auto',
                    'right': index === $('.dataTables_scrollHeadInner thead th').length - 1 ? '0px' : 'auto'
                });
            }
        });
        // **Fix Header and Body Misalignment on Sidebar Toggle**
        $('#vertical-menu-btn').on('click', function() {
            setTimeout(function() {
                dataTable.columns.adjust().draw();
                window.dispatchEvent(new Event('resize'));
            }, 10);
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
                    <option value="Un Posted">Un Posted</option>
                    <option value="Posted">Posted</option>
                    <option value="Created PO">Created PO</option>
                    <option value="Created GRN">Created GRN</option>
                    <option value="Closed">Closed</option>
                </select>
            </label>
        `;
        $('.dataTables_length').before(filterStatus);
        $('#filterStatus').select2({width: '200px' });
        $('#filterStatus').on('change', function() { $("#server-side-table").DataTable().ajax.reload(); });
    });
</script>

@endsection