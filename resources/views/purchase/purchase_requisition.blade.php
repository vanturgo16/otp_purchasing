@extends('layouts.master')

@section('konten')
<div class="page-content">
    <div class="container-fluid">
        @if (session('pesan'))
            <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="mdi mdi-check-all label-icon"></i><strong>Success</strong> - {{ session('pesan') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Purchase Requisition Item</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase</a></li>
                            <li class="breadcrumb-item active">Purchase Requisition</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                       
                                <div>
                                    <a href="/hapus_request_number" class="btn btn-primary waves-effect waves-light">Add PR RM</a>
                                    <a href="/hapus_request_number_wip" class="btn btn-primary waves-effect waves-light">Add PR WIP</a>
                                    <a href="/hapus_request_number_fg" class="btn btn-primary waves-effect waves-light">Add PR FG</a>
                                    <a href="/hapus_request_number_ta" class="btn btn-primary waves-effect waves-light">Add PR Sparepart</a>
                                    <a href="/hapus_request_number_other" class="btn btn-primary waves-effect waves-light">Add other items</a>
                                    <!-- Include modal content -->
                                   
                                </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>NO</th>
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
