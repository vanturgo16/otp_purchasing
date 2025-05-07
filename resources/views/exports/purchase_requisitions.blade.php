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
            <td colspan="2">Status PR</td>
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
            <th>No</th>
            <th>Request Number</th>
            <th>Requisition Date</th>
            <th>Supplier Name</th>
            <th>Requester Name</th>
            <th>QC Check</th>
            <th>Note</th>
            <th>PO Number</th>
            <th>Type</th>
            <th>Status PR</th>
            <th>Created PR</th>
            <th>Updated PR</th>
            <th>Product Description</th>
            <th>Required Date</th>
            <th>CC/CO Name</th>
            <th>Qty</th>
            <th>Cancel Qty</th>
            <th>Outstanding Qty</th>
            <th>Unit</th>
            <th>Remarks</th>
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
                    <td rowspan="{{ $rowspan }}">{{ $data->request_number ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->requisition_date ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->supplier_name ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->requester_name ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->qc_check ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->note ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->po_number ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->type ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->statusPR ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->createdPR ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->updatedPR ?? '-' }}</td>
                @endif

                <!-- Columns without merging -->
                <td>{{ $data->product_desc ?? '-' }}</td>
                <td>{{ $data->required_date ?? '-' }}</td>
                <td>{{ $data->cc_co_name ?? '-' }}</td>
                
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
                <td>{{ $data->remarks ?? '-' }}</td>
                <td>{{ $data->status ?? '-' }}</td>
                <td>{{ $data->createdItem ?? '-' }}</td>
                <td>{{ $data->updatedItem ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
