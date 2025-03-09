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
                        const badgeColor = data === 'Closed' ? 'success' : 
                                        (data === 'Open' ? 'info' : 'success');
                        return `<span class="badge bg-${badgeColor}" style="font-size: smaller; width: 100%">${data}</span>`;
                    },
                },
            ],
            createdRow: function(row, data, dataIndex) {
                let bgColor = '';
                let darkColor = '#FAFAFA';
                if (data.status === 'Closed') {
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

@endsection