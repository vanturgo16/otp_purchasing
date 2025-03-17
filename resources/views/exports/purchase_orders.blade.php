@php
    $rowCounts = [];
    $rowIndex = 1; // Start numbering from 1
    foreach ($datas as $data) {
        $rowCounts[$data->id] = isset($rowCounts[$data->id]) ? $rowCounts[$data->id] + 1 : 1;
    }
    $printedIds = [];
@endphp

<table>
    <thead>
        <!-- Export Details -->
        <tr>
            <th colspan="10"><strong>Export Details</strong></th>
        </tr>
        <tr>
            <td colspan="2">Type Item</td>
            <td colspan="8">: {{ $typeItem }}</td>
        </tr>
        <tr>
            <td colspan="2">Status PO</td>
            <td colspan="8">: {{ $status }}</td>
        </tr>
        <tr>
            <td colspan="2">PO Date</td>
            <td colspan="8">: {{ $dateFrom }} - {{ $dateTo }}</td>
        </tr>
        <tr>
            <td colspan="2">Exported By</td>
            <td colspan="8">: {{ $exportedBy }} at {{ $exportedAt }}</td>
        </tr>
        <tr><td colspan="10"></td></tr>

        <!-- Column Headers -->
        <tr>
            <th>No</th>
            <th>PO Number</th>
            <th>PO Date</th>
            <th>Delivery Date</th>
            <th>Request Number</th>
            <th>Supplier Name</th>
            <th>QC Check</th>
            <th>Own Remarks</th>
            <th>Supplier Remarks</th>
            <th>Type</th>
            <th>Down Payment</th>
            <th>Total Amount PO</th>
            <th>Status PO</th>
            <th>Created PO</th>
            <th>Updated PO</th>
            <th>Product Description</th>
            <th>Qty</th>
            <th>Cancel Qty</th>
            <th>Outstanding Qty</th>
            <th>Unit</th>
            <th>Currency</th>
            <th>Price</th>
            <th>Sub Total</th>
            <th>Discount</th>
            <th>Amount</th>
            <th>Tax Rate</th>
            <th>Tax Value</th>
            <th>Total Amount Item</th>
            <th>Note</th>
            <th>Status Item</th>
            <th>Created Item</th>
            <th>Updated Item</th>
        </tr>
    </thead>
    <tbody>
        @foreach($datas as $data)
            <tr>
                @if (!isset($printedIds[$data->id]))
                    @php
                        $rowspan = $rowCounts[$data->id] ?? 1;
                        $printedIds[$data->id] = true;
                    @endphp
                    <td rowspan="{{ $rowspan }}">{{ $rowIndex++ }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->po_number ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->po_date ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->delivery_date ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->request_number ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->supplier_name ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->qc_check ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->own_remarks ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->supplier_remarks ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->type ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">
                        {{ $data->down_payment 
                            ? (strpos(strval($data->down_payment), '.') !== false 
                                ? rtrim(rtrim(number_format($data->down_payment, 6, ',', '.'), '0'), ',') 
                                : number_format($data->down_payment, 0, ',', '.')) 
                            : '0' }}
                    </td>
                    <td rowspan="{{ $rowspan }}">
                        {{ $data->total_amountPO 
                            ? (strpos(strval($data->total_amountPO), '.') !== false 
                                ? rtrim(rtrim(number_format($data->total_amountPO, 6, ',', '.'), '0'), ',') 
                                : number_format($data->total_amountPO, 0, ',', '.')) 
                            : '0' }}
                    </td>
                    <td rowspan="{{ $rowspan }}">{{ $data->statusPO ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->createdPO ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->updatedPO ?? '-' }}</td>
                @endif

                <!-- Columns without merging -->
                <td>{{ $data->product_desc ?? '-' }}</td>
                <td>
                    {{ $data->qty 
                        ? (strpos(strval($data->qty), '.') !== false 
                            ? rtrim(rtrim(number_format($data->qty, 6, ',', '.'), '0'), ',') 
                            : number_format($data->qty, 0, ',', '.')) 
                        : '0' }}
                </td>
                <td>
                    {{ $data->cancel_qty 
                        ? (strpos(strval($data->cancel_qty), '.') !== false 
                            ? rtrim(rtrim(number_format($data->cancel_qty, 6, ',', '.'), '0'), ',') 
                            : number_format($data->cancel_qty, 0, ',', '.')) 
                        : '0' }}
                </td>
                <td>
                    {{ $data->outstanding_qty 
                        ? (strpos(strval($data->outstanding_qty), '.') !== false 
                            ? rtrim(rtrim(number_format($data->outstanding_qty, 6, ',', '.'), '0'), ',') 
                            : number_format($data->outstanding_qty, 0, ',', '.')) 
                        : '0' }}
                </td>
                <td>{{ $data->unit ?? '-' }}</td>
                <td>{{ $data->currency ?? '-' }}</td>
                <td>
                    {{ $data->price 
                        ? (strpos(strval($data->price), '.') !== false 
                            ? rtrim(rtrim(number_format($data->price, 6, ',', '.'), '0'), ',') 
                            : number_format($data->price, 0, ',', '.')) 
                        : '0' }}
                </td>
                <td>
                    {{ $data->sub_total 
                        ? (strpos(strval($data->sub_total), '.') !== false 
                            ? rtrim(rtrim(number_format($data->sub_total, 6, ',', '.'), '0'), ',') 
                            : number_format($data->sub_total, 0, ',', '.')) 
                        : '0' }}
                </td>
                <td>
                    {{ $data->discount 
                        ? (strpos(strval($data->discount), '.') !== false 
                            ? rtrim(rtrim(number_format($data->discount, 6, ',', '.'), '0'), ',') 
                            : number_format($data->discount, 0, ',', '.')) 
                        : '0' }}
                </td>
                <td>
                    {{ $data->amount 
                        ? (strpos(strval($data->amount), '.') !== false 
                            ? rtrim(rtrim(number_format($data->amount, 6, ',', '.'), '0'), ',') 
                            : number_format($data->amount, 0, ',', '.')) 
                        : '0' }}
                </td>
                <td>{{ isset($data->tax_rate) ? $data->tax_rate . '%' : '-' }}</td>
                <td>
                    {{ $data->tax_value 
                        ? (strpos(strval($data->tax_value), '.') !== false 
                            ? rtrim(rtrim(number_format($data->tax_value, 6, ',', '.'), '0'), ',') 
                            : number_format($data->tax_value, 0, ',', '.')) 
                        : '0' }}
                </td>
                <td>
                    {{ $data->total_amount 
                        ? (strpos(strval($data->total_amount), '.') !== false 
                            ? rtrim(rtrim(number_format($data->total_amount, 6, ',', '.'), '0'), ',') 
                            : number_format($data->total_amount, 0, ',', '.')) 
                        : '0' }}
                </td>
                <td>{{ $data->note ?? '-' }}</td>
                <td>{{ $data->status ?? '-' }}</td>
                <td>{{ $data->createdItem ?? '-' }}</td>
                <td>{{ $data->updatedItem ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
