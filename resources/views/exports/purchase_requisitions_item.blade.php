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
            <td colspan="2">Status PR Item</td>
            <td colspan="8">: {{ $status }}</td>
        </tr>
        <tr>
            <td colspan="2">Requisition Date</td>
            <td colspan="8">: {{ $dateFrom }} - {{ $dateTo }}</td>
        </tr>
        <tr>
            <td colspan="2">Exported By</td>
            <td colspan="8">: {{ $exportedBy }} at {{ $exportedAt }}</td>
        </tr>
        <tr><td colspan="10"></td></tr>

        <!-- Column Headers -->
        <tr>
            <th>No.</th>
            <th>Request Number</th>
            <th>PO Number</th>
            <th>Date</th>
            <th>Suppliers</th>
            <th>Type Product</th>
            <th>Product</th>
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
            <th>Delivery Date</th>
            <th>Status PR Item</th>
            <th>Created Item</th>
            <th>Updated Item</th>
        </tr>
    </thead>
    <tbody>
        @foreach($datas as $data)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $data->request_number ?? '-' }}</td>
                <td>{{ $data->po_number ?? '-' }}</td>
                <td>{{ $data->date ?? '-' }}</td>
                <td>{{ $data->supplier_name ?? '-' }}</td>
                <td>{{ $data->type_product ?? '-' }}</td>
                <td>{{ $data->product_desc ?? '-' }}</td>

                <td>{{ $data->qty ??  '0' }}</td>
                <td>{{ $data->cancel_qty ??  '0' }}</td>
                <td>{{ $data->outstanding_qty ??  '0' }}</td>
                {{-- <td>
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
                </td> --}}
                <td>{{ $data->unit ?? '-' }}</td>
                <td>{{ $data->currency ?? $data->currencyPO ?? '-' }}</td>
                
                <td>{{ $data->pricePO ??  '0' }}</td>
                <td>{{ $data->sub_totalPO ??  '0' }}</td>
                <td>{{ $data->discountPO ??  '0' }}</td>
                <td>{{ $data->amountPO ??  '0' }}</td>
                <td>{{ isset($data->tax_rate) ? $data->tax_rate . '%' : (isset($data->tax_ratePO) ? $data->tax_ratePO . '%' : '-') }}</td>
                <td>{{ $data->tax_valuePO ??  '0' }}</td>
                <td>{{ $data->total_amountPO ??  '0' }}</td>
                {{-- <td>
                    {{ $data->price ?? $data->pricePO 
                        ? (strpos(strval($data->price ?? $data->pricePO), '.') !== false 
                            ? rtrim(rtrim(number_format($data->price ?? $data->pricePO, 3, ',', '.'), '0'), ',') 
                            : number_format($data->price ?? $data->pricePO, 0, ',', '.')) 
                        : '0' }}
                </td>
                
                <td>
                    {{ $data->sub_total ?? $data->sub_totalPO 
                        ? (strpos(strval($data->sub_total ?? $data->sub_totalPO), '.') !== false 
                            ? rtrim(rtrim(number_format($data->sub_total ?? $data->sub_totalPO, 3, ',', '.'), '0'), ',') 
                            : number_format($data->sub_total ?? $data->sub_totalPO, 0, ',', '.')) 
                        : '0' }}
                </td>
                <td>
                    {{ $data->discount ?? $data->discountPO 
                        ? (strpos(strval($data->discount ?? $data->discountPO), '.') !== false 
                            ? rtrim(rtrim(number_format($data->discount ?? $data->discountPO, 3, ',', '.'), '0'), ',') 
                            : number_format($data->discount ?? $data->discountPO, 0, ',', '.')) 
                        : '0' }}
                </td>
                <td>
                    {{ $data->amount ?? $data->amountPO 
                        ? (strpos(strval($data->amount ?? $data->amountPO), '.') !== false 
                            ? rtrim(rtrim(number_format($data->amount ?? $data->amountPO, 3, ',', '.'), '0'), ',') 
                            : number_format($data->amount ?? $data->amountPO, 0, ',', '.')) 
                        : '0' }}
                </td>
                <td>
                    {{ isset($data->tax_rate) ? $data->tax_rate . '%' : (isset($data->tax_ratePO) ? $data->tax_ratePO . '%' : '-') }}
                </td>                
                <td>
                    {{ $data->tax_value ?? $data->tax_valuePO
                        ? (strpos(strval($data->tax_value ?? $data->tax_valuePO), '.') !== false 
                            ? rtrim(rtrim(number_format($data->tax_value ?? $data->tax_valuePO, 3, ',', '.'), '0'), ',') 
                            : number_format($data->tax_value ?? $data->tax_valuePO, 0, ',', '.')) 
                        : '0' }}
                </td>
                <td>
                    {{ $data->total_amount ?? $data->total_amountPO
                        ? (strpos(strval($data->total_amount ?? $data->total_amountPO), '.') !== false 
                            ? rtrim(rtrim(number_format($data->total_amount ?? $data->total_amountPO, 3, ',', '.'), '0'), ',') 
                            : number_format($data->total_amount ?? $data->total_amountPO, 0, ',', '.')) 
                        : '0' }}
                </td> --}}
                <td>{{ $data->delivery_date ?? '-' }}</td>
                <td>{{ $data->status ?? '-' }}</td>
                <td>{{ $data->createdItem ?? '-' }}</td>
                <td>{{ $data->updatedItem ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
