@extends('layouts.master')

@section('konten')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">List Purchase Requisition Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Request Number</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Supplier</th>
                                        <th>PO Number</th>
                                        <th>Product </th>
                                        <th>Qty</th>
                                        <th>Units</th>
                                        <th>Price</th>
                                        <th>Discount</th>
                                        <th>Amount</th>
                                        <th>Outstanding Qty</th>
                                        <th>Delivery Date</th>
                                        <th>sts_pod</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->request_number }}</td>
                                        <td>{{ $item->date_prd }}</td>
                                        <td>{{ $item->type_prd }}</td>
                                        <td>{{ $item->supplier_name }}</td>
                                        <td>{{ $item->po_number }}</td>
                                        <td>{{ $item->product_desc }}</td>
                                        <td>{{ $item->qty }}</td>
                                        <td>{{ $item->unit_code }}</td>
                                        <td>{{ number_format($item->price, 2, ',', '.') }}</td>
                                        <td>{{ $item->discount }}</td>
                                        <td>{{ $item->amount }}</td>
                                        <td>{{ $item->outstanding_qty_grnd }}</td>
                                        <td>{{ $item->delivery_date }}</td>
                                        <td>{{ $item->sts_pod }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
