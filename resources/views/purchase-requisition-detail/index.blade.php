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
                            <h5 class="mb-0">List Purchase Requisition Items</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered dt-responsive w-100" id="server-side-table" style="font-size: small">
                            <thead>
                                <tr>
                                    <th class="align-middle text-center">No.</th>
                                    <th class="align-middle text-center">Request Number</th>
                                    <th class="align-middle text-center">PO Number</th>
                                    <th class="align-middle text-center">Date</th>
                                    <th class="align-middle text-center">Suppliers</th>
                                    <th class="align-middle text-center">Product</th>
                                    <th class="align-middle text-center">Qty</th>
                                    <th class="align-middle text-center">Cancel Qty</th>
                                    <th class="align-middle text-center">Outstanding Qty</th>
                                    <th class="align-middle text-center">Unit</th>
                                    <th class="align-middle text-center">Currency</th>
                                    <th class="align-middle text-center">Price</th>
                                    <th class="align-middle text-center">Discount</th>
                                    <th class="align-middle text-center">Amount</th>
                                    <th class="align-middle text-center">Delivery Date</th>
                                    <th class="align-middle text-center">Status</th>
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
                <h5 class="modal-title" id="staticBackdropLabel">Export Data PR Item</b></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="exportForm" action="{{ route('pr.item.export') }}" method="POST" enctype="multipart/form-data">
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
                                    <option value="Open">Open</option>
                                    <option value="Close">Close</option>
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
                    const exportForm = document.querySelector("form[action='{{ route('pr.item.export') }}']");
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
                            let filename = `Export_PR_Item_${formattedDate}.xlsx`;
            
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
        var url = '{!! route('pr.indexItem') !!}';

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
            lengthMenu: [ [5, 10, 25, 50, -1], [5, 10, 25, 50, "All"] ],
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
                    data: 'po_number',
                    name: 'po_number',
                    orderable: true,
                    searchable: true,
                    className: 'align-top',
                    render: function (data, type, row) {
                        if (!data) { return '-'; }
                        return data;
                    }
                },
                {
                    data: 'date',
                    name: 'date',
                    orderable: true,
                    searchable: true,
                    className: 'align-top'
                },
                {
                    data: 'supplier_name',
                    name: 'supplier_name',
                    orderable: true,
                    searchable: true,
                    className: 'align-top'
                },
                {
                    data: 'product_desc',
                    name: 'product_desc',
                    orderable: true,
                    searchable: true,
                    className: 'align-top',
                    render: function (data, type, row) {
                        if (!data) { return ''; }
                        return '<b>' + row.type_product + '</b><br>' + data;
                    }
                },
                {
                    data: 'qty',
                    name: 'qty',
                    searchable: true,
                    orderable: true,
                    className: 'align-top',
                    render: function(data, type, row) {
                        if (data) {
                            let number = parseFloat(data).toString(); // Convert to string without rounding
                            let parts = number.split('.'); // Split integer and decimal parts
                            let integerPart = parts[0];
                            let decimalPart = parts[1] || '';

                            // Add dots as thousands separator
                            integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                            return decimalPart ? `${integerPart},${decimalPart}` : integerPart;
                        }
                        return '';
                    }
                },
                {
                    data: 'cancel_qty',
                    name: 'cancel_qty',
                    searchable: true,
                    orderable: true,
                    className: 'align-top',
                    render: function(data, type, row) {
                        if (data) {
                            let number = parseFloat(data).toString(); // Convert to string without rounding
                            let parts = number.split('.'); // Split integer and decimal parts
                            let integerPart = parts[0];
                            let decimalPart = parts[1] || '';

                            // Add dots as thousands separator
                            integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                            return decimalPart ? `${integerPart},${decimalPart}` : integerPart;
                        }
                        return '';
                    }
                },
                {
                    data: 'outstanding_qty',
                    name: 'outstanding_qty',
                    searchable: true,
                    orderable: true,
                    className: 'align-top',
                    render: function(data, type, row) {
                        if (data) {
                            let number = parseFloat(data).toString(); // Convert to string without rounding
                            let parts = number.split('.'); // Split integer and decimal parts
                            let integerPart = parts[0];
                            let decimalPart = parts[1] || '';

                            // Add dots as thousands separator
                            integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                            return decimalPart ? `${integerPart},${decimalPart}` : integerPart;
                        }
                        return '';
                    }
                },
                {
                    data: 'unit_code',
                    name: 'unit_code',
                    orderable: true,
                    searchable: true,
                    className: 'align-top'
                },
                {
                    data: 'currency',
                    name: 'currency',
                    orderable: true,
                    searchable: true,
                    className: 'align-top',
                    render: function(data, type, row) {
                        var currency = data ? data : row.currencyPO;
                        if (!currency) { return '-'; }
                        return currency;
                    }
                },
                {
                    data: 'price',
                    name: 'price',
                    orderable: true,
                    className: 'align-top',
                    render: function(data, type, row) {
                        let number = data ? parseFloat(data) : parseFloat(row.pricePO);
                        if (!isNaN(number)) {
                            let numberStr = number.toString();
                            let parts = numberStr.split('.'); // Split integer and decimal parts
                            let integerPart = parts[0];
                            let decimalPart = parts[1] || '';
                            // Format integer part with dot as thousand separator
                            integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            return decimalPart ? `${integerPart},${decimalPart}` : integerPart;
                        }
                        return '-';
                    }
                },
                {
                    data: 'discount',
                    name: 'discount',
                    orderable: true,
                    className: 'align-top',
                    render: function(data, type, row) {
                        let number = data ? parseFloat(data) : parseFloat(row.discountPO);
                        if (!isNaN(number)) {
                            let numberStr = number.toString();
                            let parts = numberStr.split('.'); // Split integer and decimal parts
                            let integerPart = parts[0];
                            let decimalPart = parts[1] || '';
                            // Format integer part with dot as thousand separator
                            integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            return decimalPart ? `${integerPart},${decimalPart}` : integerPart;
                        }
                        return '-';
                    }
                },
                {
                    data: 'amount',
                    name: 'amount',
                    orderable: true,
                    className: 'align-top',
                    render: function(data, type, row) {
                        let number = data ? parseFloat(data) : parseFloat(row.amountPO);
                        if (!isNaN(number)) {
                            let numberStr = number.toString();
                            let parts = numberStr.split('.'); // Split integer and decimal parts
                            let integerPart = parts[0];
                            let decimalPart = parts[1] || '';
                            // Format integer part with dot as thousand separator
                            integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            return decimalPart ? `${integerPart},${decimalPart}` : integerPart;
                        }
                        return '-';
                    }
                },
                {
                    data: 'delivery_date',
                    name: 'delivery_date',
                    orderable: true,
                    searchable: true,
                    className: 'align-top',
                    render: function (data, type, row) {
                        if (!data) { return '-'; }
                        return data;
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: true,
                    searchable: true,
                    className: 'align-top text-center freeze-column',
                    render: function(data) {
                        const badgeColor = data === 'Close' ? 'success' : 
                                        (data === 'Open' ? 'info' : 'success');
                        return `<span class="badge bg-${badgeColor}" style="font-size: smaller; width: 100%">${data}</span>`;
                    },
                },
            ],
            createdRow: function(row, data, dataIndex) {
                let bgColor = '';
                let darkColor = '#FAFAFA';
                if (['Close'].includes(data.status)) {
                    bgColor = 'table-success';
                    darkColor = '#CFEBE0';
                }
                if (data.status === 'Open') {
                    bgColor = 'table-secondary';
                    darkColor = '#DFE0E3';
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
                    <option value="Open">Open</option>
                    <option value="Close">Close</option>
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