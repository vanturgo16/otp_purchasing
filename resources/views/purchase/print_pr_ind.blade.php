<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PRINT PURCHASE REQUISITION</title>
    <!-- Bootstrap Css -->
    <style>
        .row.d-flex .col-8 {
            display: flex;
            justify-content: space-between;
        }
        .row.d-flex .col-8 div {
            display: flex;
        }
        .label {
            width: 200px; /* Adjust this width as needed */
        }
        .label2 {
            width: 10px; /* Adjust this width as needed */
        }
        .value {
            flex-grow: 1;
        }
    </style>
    <style>
    /* CSS untuk watermark */
    .watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 5rem;
        color: rgba(0, 0, 0, 0.1);
        z-index: 1000;
        pointer-events: none;
        user-select: none;
    }

    /* CSS khusus untuk cetak */
    @media print {
        body {
            position: relative;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 5rem;
            color: rgba(0, 0, 0, 0.1);
            z-index: 1000;
            pointer-events: none;
            user-select: none;
            page-break-inside: avoid;
        }
    }
</style>


    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
</head>

<body>

@if(($datas[0]->status != 'Posted') && ($datas[0]->status != 'Created PO'))
    <div class="watermark">DRAFT</div>
@endif

    <div class="container-fluid">
        <div class="row">
            <div class="col-8 d-flex align-items-center gap-10">
                <img src="{{ asset('assets/images/icon-otp.png') }}" width="80" height="80">
                <small style="padding-left: 10px">
                    <b>PT OLEFINA TIFAPLAS POLIKEMINDO</b><br />
                    Jl. Raya Serang KM 16.8 Desa Telaga, Kec. Cikupa<br />
                    Tangerang-Banten 15710<br />
                    Tlp. +62 21 595663567, Fax. 0<br />
                </small>
            </div>
            <div class="col-4 d-flex justify-content-end" style="font-size: 0.7rem;">
                FM-SM-MKT-02, Rev. 0, 01 September 2021
            </div>
        </div>

        <div class="row text-center">
            <h4 style="margin-top: 3rem;">PERMINTAAN PEMBELIAN</h4>
        </div>

        <div class="row d-flex justify-content-between">
        <div class="col-8">
            <div class="label">No. Permintaan:</div>
            <div class="label2">:</div>
            <div class="value">{{ $datas[0]->request_number }}</div>
        </div>
    </div>
    <div class="row d-flex justify-content-between">
        <div class="col-8">
            <div class="label">Permohonan Tanggal</div>
            <div class="label2">:</div>
            <div class="value">{{ $datas[0]->date }}</div>
        </div>
    </div>
    <div class="row d-flex justify-content-between pb-3">
        <div class="col-8">
            <div class="label">Supplier</div>
            <div class="label2">:</div>
            <div class="value">{{ $datas[0]->name }}</div>
        </div>
    </div>


        <div class="row">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-10">
                    <thead class="table-light">
                        <tr>
                            <td>No</td>
                            <td>Kode Barang</td>
                            <td>Keterangan</td>
                            <td>Jumlah</td>
                            <td>Satuan</td>
                            <td>Diperlukan</td>
                            <td>CC/CO</td>
                        </tr>
                    </thead>
                    <tbody>
                    @if($PurchaseRequisitions->type=='RM')
                        @foreach ($data_detail_rm as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->rm_code }}</td>
                                    <td>{{ $data->description }}<br>
                                    {{ $data->remarks }}</td>
                                    <td>{{ $data->qty }}</td>
                                    <td>{{ $data->unit_code }}</td>
                                    <td>{{ $data->required_date }}</td>
                                    <td>{{ $data->nm_requester }}</td>
                                </tr>
                        @endforeach
                    @elseif($PurchaseRequisitions->type=='TA')
                        @foreach ($data_detail_ta as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->code }}</td>
                                    <td>{{ $data->description }}<br>
                                    {{ $data->remarks }}</td>
                                    <td>{{ $data->qty }}</td>
                                    <td>{{ $data->unit_code }}</td>
                                    <td>{{ $data->required_date }}</td>
                                    <td>{{ $data->nm_requester }}</td>
                                </tr>
                        @endforeach
                    @elseif($PurchaseRequisitions->type=='WIP')
                        @foreach ($data_detail_wip as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->wip_code }}</td>
                                    <td>{{ $data->description }}<br>
                                    {{ $data->remarks }}</td>
                                    <td>{{ $data->qty }}</td>
                                    <td>{{ $data->unit_code }}</td>
                                    <td>{{ $data->required_date }}</td>
                                    <td>{{ $data->nm_requester }}</td>
                                </tr>
                        @endforeach
                    @elseif($PurchaseRequisitions->type=='FG')
                        @foreach ($data_detail_fg as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->product_code }}</td>
                                    <td>{{ $data->description }} || {{ $data->perforasi }}<br>
                                    {{ $data->remarks }}</td>
                                    <td>{{ $data->qty }}</td>
                                    <td>{{ $data->unit_code }}</td>
                                    <td>{{ $data->required_date }}</td>
                                    <td>{{ $data->nm_requester }}</td>
                                </tr>
                        @endforeach
                    @elseif($PurchaseRequisitions->type=='Other')
                        @foreach ($data_detail_other as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->code }}</td>
                                    <td>{{ $data->description }}<br>
                                    {{ $data->remarks }}</td>
                                    <td>{{ $data->qty }}</td>
                                    <td>{{ $data->unit_code }}</td>
                                    <td>{{ $data->required_date }}</td>
                                    <td>{{ $data->nm_requester }}</td>
                                </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <ul style="list-style-type: '- ';">
            Note : {{ $datas[0]->note; }}
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
