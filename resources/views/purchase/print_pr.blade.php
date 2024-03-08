<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PRINT PURCHASE REQUISITION</title>
    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-8 d-flex align-items-center gap-10">
                <img src="http://eks.olefinatifaplas.my.id/img/otp-icon.jpg" width="100" height="100">
                <small style="padding-left: 10px">
                    <b>PT OLEFINA TIFAPLAS POLIKEMINDO</b><br />
                    Jl. Raya Serang KM 16.8 Desa Telaga, Kec. Cikupa<br />
                    Tangerang-Banten 15710<br />
                    Tlp. +62 21 5960801/05, Fax. +62 21 5960776<br />
                </small>
            </div>
            <div class="col-4 d-flex justify-content-end">
                FM-SM-MKT-02, Rev. 0, 01 September 2021
            </div>
        </div>

        <div class="row text-center">
            <h1>PURCHASE REQUISITION</h1>
        </div>

        <div class="row d-flex justify-content-between">
            <div class="col-8">Request No. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $datas[0]->request_number; }}</div>
        </div>
        <div class="row d-flex justify-content-between">
            <div class="col-8">Request Date &nbsp;&nbsp;&nbsp;: {{ $datas[0]->date; }}</div>
        </div>
        <div class="row d-flex justify-content-between pb-3">
            <div class="col-8">Supplier &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $datas[0]->name }}</div>
        </div>

        <div class="row">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-10">
                    <thead class="table-light">
                        <tr>
                            <td>No</td>
                            <td>Item Code</td>
                            <td>Description</td>
                            <td>Qty</td>
                            <td>Unit</td>
                            <td>Required</td>
                            <td>CC/CO</td>
                        </tr>
                    </thead>
                    <tbody>
                    @if($PurchaseRequisitions->type=='RM')
                        @foreach ($data_detail_rm as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->rm_code }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>{{ $data->qty }}</td>
                                    <td>{{ $data->unit_code }}</td>
                                    <td>{{ $data->required_date }}</td>
                                    <td>{{ $data->cc_co }}</td>
                                </tr>
                        @endforeach
                    @elseif($PurchaseRequisitions->type=='TA')
                        @foreach ($data_detail_ta as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->code }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>{{ $data->qty }}</td>
                                    <td>{{ $data->unit_code }}</td>
                                    <td>{{ $data->required_date }}</td>
                                    <td>{{ $data->cc_co }}</td>
                                </tr>
                        @endforeach
                    @elseif($PurchaseRequisitions->type=='WIP')
                        @foreach ($data_detail_wip as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->wip_code }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>{{ $data->qty }}</td>
                                    <td>{{ $data->unit_code }}</td>
                                    <td>{{ $data->required_date }}</td>
                                    <td>{{ $data->cc_co }}</td>
                                </tr>
                        @endforeach
                    @elseif($PurchaseRequisitions->type=='FG')
                        @foreach ($data_detail_fg as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->product_code }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>{{ $data->qty }}</td>
                                    <td>{{ $data->unit_code }}</td>
                                    <td>{{ $data->required_date }}</td>
                                    <td>{{ $data->cc_co }}</td>
                                </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <ul style="list-style-type: '- ';">
          
            </ul>
        </div>
        <hr>
        <div class="row">
            <div class="col-4 text-center">
                <p class="mb-5">Diminta Oleh,</p>
                <p>(.............)</p>
            </div>
            <div class="col-4 text-center">
                <p class="mb-5">Disetujui Oleh,</p>
                <p>(.............)</p>
            </div>
            <div class="col-4 text-center">
                <p class="mb-5">Purchasing</p>
                <p>(.............)</p>
            </div>
        </div>



    </div>
</body>

</html>
