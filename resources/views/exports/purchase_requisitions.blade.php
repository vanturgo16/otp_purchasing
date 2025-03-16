<table>
    <thead>
        <tr>
            <th>Request Number</th>
            <th>Requisition Date</th>
            <th>Supplier Name</th>
            <th>Requester Name</th>
            <th>QC Check</th>
            <th>Note</th>
            <th>PO Number</th>
            <th>Type</th>
            <th>Status PR</th>
            <th>Product Description</th>
            <th>Required Date</th>
            <th>CC/CO Name</th>
            <th>Qty</th>
            <th>Cancel Qty</th>
            <th>Outstanding Qty</th>
            <th>Unit</th>
            <th>Remarks</th>
            <th>Status Item</th>
        </tr>
    </thead>
    <tbody>
        @foreach($datas->groupBy('id') as $groupedData)
            @foreach($groupedData as $index => $data)
                <tr>
                    @if($index == 0)
                        <td rowspan="{{ count($groupedData) }}">{{ $data->request_number ?? '-' }}</td>
                        <td rowspan="{{ count($groupedData) }}">{{ $data->requisition_date ?? '-' }}</td>
                        <td rowspan="{{ count($groupedData) }}">{{ $data->supplier_name ?? '-' }}</td>
                        <td rowspan="{{ count($groupedData) }}">{{ $data->requester_name ?? '-' }}</td>
                        <td rowspan="{{ count($groupedData) }}">{{ $data->qc_check ?? '-' }}</td>
                        <td rowspan="{{ count($groupedData) }}">{{ $data->note ?? '-' }}</td>
                        <td rowspan="{{ count($groupedData) }}">{{ $data->po_number ?? '-' }}</td>
                        <td rowspan="{{ count($groupedData) }}">{{ $data->type  ?? '-'}}</td>
                        <td rowspan="{{ count($groupedData) }}">{{ $data->statusPR ?? '-' }}</td>
                    @endif
                    <td>{{ $data->product_desc ?? '-' }}</td>
                    <td>{{ $data->required_date ?? '-' }}</td>
                    <td>{{ $data->cc_co_name ?? '-' }}</td>
                    <td>{{ $data->qty ?? '-' }}</td>
                    <td>{{ $data->cancel_qty ?? '-' }}</td>
                    <td>{{ $data->outstanding_qty ?? '-' }}</td>
                    <td>{{ $data->unit ?? '-' }}</td>
                    <td>{{ $data->remarks ?? '-' }}</td>
                    <td>{{ $data->status ?? '-' }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
