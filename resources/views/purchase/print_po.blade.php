<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PRINT PURCHASE ORDER</title>
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
            <h1>PURCHASE ORDER</h1>
        </div>

        <div class="row d-flex justify-content-between">
            <div class="col-8">Supplier : {{ $results[0]->name; }}</div>
            <div class="col-4">
                <p class="mb-1">PO &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $results[0]->po_number; }}</p>
                <p class="mb-1"></p>
            </div>
        </div>
        <div class="row d-flex justify-content-between">
            <div class="col-8">Phone &nbsp;&nbsp;&nbsp;:</div>
            <div class="col-4">
                <p class="mb-1">PR No : {{ $results[0]->request_number; }}</p>
                <p class="mb-1"></p>
            </div>
        </div>
        <div class="row d-flex justify-content-between pb-3">
            <div class="col-8">Fax &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</div>
            <div class="col-4">
                <p class="mb-1">Date &nbsp;&nbsp;&nbsp;: {{ $results[0]->date; }}</p>
                <p class="mb-1"></p>
            </div>
        </div>

        <div class="row">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-10">
                    <thead class="table-light">
                        <tr>
                            <td>No</td>
                            <td>Description</td>
                            <td>Qty</td>
                            <td>Unit</td>
                            <td>Unit Price 	</td>
                            <td>Amount</td>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($data_detail_rm as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ $data->qty }}</td>
                                <td>{{ $data->unit }}</td>
                                <td>{{ $data->price }}</td>
                                <td>{{ $data->amount }}</td>
                            </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <ul style="list-style-type: '- ';">
                <!-- <li>Penyerahan (Toleransi +/- 10%)</li>
                <li>Syarat Pembayaran :</li>
                Batas waktu pengaduan tentang kondisi barang yang disebabkan cacat dari pabrik kami (masalah kualitas),
                harap diinformasikan kepada
                kami selambat-lambatnya 30 hari setelah tanggal penerimaan barang dengan ketentuan cantumkan nomor
                label, box dan jumlahnya. -->
            </ul>
        </div>
        <hr>
        <div class="row">
            <div class="col-4 text-center">
                <p class="mb-5">Purchasing,</p>
                <p>(.............)</p>
            </div>
            <div class="col-4 text-center">
                <p class="mb-5">Direktur,</p>
                <p>(.............)</p>
            </div>
            <div class="col-4 text-center">
                <p class="mb-5">Supplier</p>
                <p>(.............)</p>
            </div>
        </div>

        <div class="row">
            <h6>*NB: Mohon setelah PO diterima, ditandatangan, distempel kemudian difax atau diemail kembali</h6>
        </div>



    </div>
</body>

</html>