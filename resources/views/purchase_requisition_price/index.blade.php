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
                            <h5 class="mb-0">List Purchase Requisition (PR) With Price</h5>
                            <div>
                                <a href="" class="btn btn-sm btn-info waves-effect btn-label waves-light mb-2" data-bs-toggle="modal" data-bs-target="#showListPR" title="Lihat List PR yang telah dibuat GRN">
                                    <i class="mdi mdi-eye label-icon"></i> Show List PR (Created GRN)
                                </a>
                                <a href="" class="btn btn-sm btn-primary waves-effect btn-label waves-light mb-2" data-bs-toggle="modal" data-bs-target="#addPRPrice" title="Tambah PR Price">
                                    <i class="mdi mdi-plus label-icon"></i> Tambah Data
                                </a>
                            </div>
                        </div>
                    </div>
                    {{-- Modal Show List PR --}}
                    <div class="modal fade" id="showListPR" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-top modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="staticBackdropLabel">List of PRs that have created GRNs but do not have prices yet</b></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4" style="max-height: 65vh; overflow-y: auto;">
                                    <div id="listPRContent" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p>Loading data...</p>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
                            const modal = document.getElementById('showListPR');
                            const content = document.getElementById('listPRContent');
                            let dataTableInstance = null;
                        
                            modal.addEventListener('show.bs.modal', function () {
                                content.innerHTML = `
                                    <div class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p>Loading data...</p>
                                    </div>
                                `;
                        
                                fetch("{{ route('pr.price.getDataPRGRN') }}")
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.length === 0) {
                                            content.innerHTML = `<p class="text-center text-muted">No PRs found.</p>`;
                                            return;
                                        }
                        
                                        let tableHtml = `
                                            <table id="tablePR" class="display table table-bordered table-striped align-middle w-100">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="text-center">#</th>
                                                        <th class="text-center">GRN Number</th>
                                                        <th class="text-center">GRN Status</th>
                                                        <th class="text-center">PR Number</th>
                                                        <th class="text-center">Created At</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                        `;
                        
                                        data.forEach((item, index) => {
                                            tableHtml += `
                                                <tr>
                                                    <td>${index + 1}</td>
                                                    <td>${item.receipt_number ?? '-'}</td>
                                                    <td>${item.status ?? '-'}</td>
                                                    <td>${item.request_number ?? '-'}</td>
                                                    <td>${item.createdGRN ?? '-'}</td>
                                                </tr>
                                            `;
                                        });
                        
                                        tableHtml += `</tbody></table>`;
                                        content.innerHTML = tableHtml;
                        
                                        // destroy old instance if exists (to prevent re-init error)
                                        if (dataTableInstance) {
                                            dataTableInstance.destroy();
                                        }
                        
                                        // initialize DataTable
                                        dataTableInstance = new DataTable("#tablePR", {
                                            pageLength: 5,       // default rows per page
                                            lengthMenu: [5, 10, 20, 50],
                                            ordering: true,
                                            searching: true
                                        });
                                    })
                                    .catch(error => {
                                        content.innerHTML = `<p class="text-danger text-center">Failed to load data.</p>`;
                                        console.error(error);
                                    });
                            });
                        });
                    </script>                        

                    {{-- Modal Add --}}
                    <div class="modal fade" id="addPRPrice" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-top modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="staticBackdropLabel">Tambah Harga Pada Purchase Requisition</b></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form class="formLoad" action="{{ route('pr.price.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-body p-4" style="max-height: 65vh; overflow-y: auto;">
                                        <div class="container">
                                            <div class="row mb-4 field-wrapper required-field">
                                                <label class="col-sm-3 col-form-label">Reference Number (PR)</label>
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
                                                <label class="col-sm-3 col-form-label">Date</label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="date" class="form-control custom-bg-gray" value="" readonly required>
                                                </div>
                                            </div>
                                            <div class="row mb-4 field-wrapper required-field">
                                                <label class="col-sm-3 col-form-label">Suppliers</label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="id_master_suppliers" class="form-control custom-bg-gray" value="" placeholder="Otomatis Terisi.." readonly required>
                                                </div>
                                            </div>
                                            <div class="row mb-4 field-wrapper required-field">
                                                <label class="col-sm-3 col-form-label">Requester</label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="requester" class="form-control custom-bg-gray" value="" placeholder="Otomatis Terisi.." value="" readonly required>
                                                </div>
                                            </div>
                                            <div class="row mb-4 field-wrapper required-field">
                                                <label class="col-sm-3 col-form-label">Qc Check </label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="qc_check" class="form-control custom-bg-gray" value="" placeholder="Otomatis Terisi.." value="" readonly required>
                                                </div>
                                            </div>
                                            <div class="row mb-4 field-wrapper">
                                                <label class="col-sm-3 col-form-label">Note</label>
                                                <div class="col-sm-9">
                                                    <textarea name="note" rows="3" cols="50" class="form-control custom-bg-gray" placeholder="Note.. (Opsional)" readonly></textarea>
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
                                    <th class="align-middle text-center">Type</th>
                                    <th class="align-middle text-center">Total Items</th>
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

{{-- Modal Export --}}
<div class="modal fade" id="exportModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-top modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Export Data PR With Price</b></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="exportForm" action="{{ route('pr.price.export') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4" style="max-height: 65vh; overflow-y: auto;">
                    <div class="container">
                        <div class="row mb-2">
                            <label class="col-sm-4 col-form-label">Type Item</label>
                            <div class="col-sm-8">
                                <select class="form-select data-select2" name="typeItem" id="" style="width: 100%" required>
                                    <option value="Semua Type">-- Semua Type --</option>
                                    <option value="RM">RM</option>
                                    <option value="WIP">WIP</option>
                                    <option value="FG">FG</option>
                                    <option value="TA">TA</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-2 ">
                            <label class="col-sm-4 col-form-label">Status</label>
                            <div class="col-sm-8">
                                <select class="form-select data-select2" name="status" id="" style="width: 100%" required>
                                    <option value="Semua Status">-- Semua Status --</option>
                                    <option value="Request">Request</option>
                                    <option value="Un Posted">Un Posted</option>
                                    <option value="Posted">Posted</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-4 col-form-label">Date From</label>
                            <div class="col-sm-8">
                                <input type="date" name="dateFrom" class="form-control" value="" required>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-4 col-form-label">Date To</label>
                            <div class="col-sm-8">
                                <input type="date" name="dateTo" class="form-control" value="" required>
                                <small class="text-danger d-none" id="dateToError"><b>Date To</b> cannot be before <b>Date From</b></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success waves-effect btn-label waves-light">
                        <i class="mdi mdi-file-excel label-icon"></i>Export To Excel
                    </button>
                </div>
            </form>
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    const exportForm = document.querySelector("form[action='{{ route('pr.price.export') }}']");
                    const exportButton = exportForm.querySelector("button[type='submit']");
            
                    exportForm.addEventListener("submit", function (event) {
                        event.preventDefault(); // Prevent normal form submission
            
                        let formData = new FormData(exportForm);
                        let url = exportForm.action;
            
                        // Disable button to prevent multiple clicks
                        exportButton.disabled = true;
                        exportButton.innerHTML = '<i class="mdi mdi-loading mdi-spin label-icon"></i>Exporting...';
            
                        fetch(url, {
                            method: "POST",
                            body: formData,
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                            }
                        })
                        .then(response => response.blob()) // Expect a file response
                        .then(blob => {
                            let now = new Date();
                            let formattedDate = now.getDate().toString().padStart(2, '0') + "_" +
                                                (now.getMonth() + 1).toString().padStart(2, '0') + "_" +
                                                now.getFullYear() + "_" +
                                                now.getHours().toString().padStart(2, '0') + "_" +
                                                now.getMinutes().toString().padStart(2, '0');
                            let filename = `Export_PR_With_Price_${formattedDate}.xlsx`;
            
                            let downloadUrl = window.URL.createObjectURL(blob);
                            let a = document.createElement("a");
                            a.href = downloadUrl;
                            a.download = filename; // Set dynamic filename
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            window.URL.revokeObjectURL(downloadUrl);
                        })
                        .catch(error => {
                            console.error("Export error:", error);
                            alert("An error occurred while exporting.");
                        })
                        .finally(() => {
                            exportButton.disabled = false;
                            exportButton.innerHTML = '<i class="mdi mdi-file-excel label-icon"></i> Export To Excel';
                        });
                    });
                });
            </script>            
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var url = '{!! route('pr.price.index') !!}';
        
        var idUpdated = '{{ $idUpdated }}';
        var pageNumber = '{{ $page_number }}';
        var pageLength = 5;
        var displayStart = (pageNumber - 1) * pageLength;
        var firstReload = true; 

        var dataTable = $('#server-side-table').DataTable({
            scrollX: true,
            responsive: false,
            fixedColumns: {
                leftColumns: 2, // Freeze first two columns
                rightColumns: 1 // Freeze last column (Aksi)
            },
            processing: true,
            serverSide: true,
            
            displayStart: displayStart,
            pageLength: pageLength,

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
                    className: 'align-top fw-bold freeze-column',
                    render: function(data, type, row) {
                        // build URL with reference_number
                        let url = `{{ route('pr.index') }}?reference_number=${row.request_number}`;
                        return `<a href="${url}">${data}</a>`;
                    }
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
                {
                    data: 'id',
                    name: 'id',
                    searchable: false,
                    visible: false
                },
            ],
            createdRow: function(row, data, dataIndex) {
                let bgColor = '';
                let darkColor = '#FAFAFA';
                if (['Posted'].includes(data.status)) {
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
            drawCallback: function(settings) {
                if (firstReload && idUpdated) {
                    // Reset URL
                    let urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.toString()) {
                        let newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                        history.pushState({}, "", newUrl);
                    }
                    var row = dataTable.row(function(idx, data, node) {
                        return data.id == idUpdated;
                    });

                    if (row.length) {
                        var rowNode = row.node();
                        $('html, body').animate({
                            scrollTop: $(rowNode).offset().top - $(window).height() / 2
                        }, 500);
                    }
                    firstReload = false;
                }
            }
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
                    <option value="Posted">Posted</option>
                    <option value="Closed">Closed</option>
                    <option value="Un Posted">Un Posted</option>
                </select>
            </label>
        `;
        $('.dataTables_length').before(filterStatus);
        $('#filterStatus').select2({width: '200px' });
        $('#filterStatus').on('change', function() { $("#server-side-table").DataTable().ajax.reload(); });

        // Export Modal Button
        var exportButton = `
            <button id="exportBtn" data-bs-toggle="modal" data-bs-target="#exportModal" class="btn btn-light waves-effect btn-label waves-light">
                <i class="mdi mdi-export label-icon"></i> Export Data
            </button>
        `;
        $('.dataTables_length').before(exportButton);
    });
</script>

@endsection