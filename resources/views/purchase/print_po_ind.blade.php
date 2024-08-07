<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PRINT PESANAN PEMBELIAN</title>
    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
</head>

<body>
            @php
            function numberToWords($number) {
            $words = [
                0 => 'nol',
                1 => 'satu',
                2 => 'dua',
                3 => 'tiga',
                4 => 'empat',
                5 => 'lima',
                6 => 'enam',
                7 => 'tujuh',
                8 => 'delapan',
                9 => 'sembilan',
                10 => 'sepuluh',
                11 => 'sebelas',
                12 => 'dua belas',
                13 => 'tiga belas',
                14 => 'empat belas',
                15 => 'lima belas',
                16 => 'enam belas',
                17 => 'tujuh belas',
                18 => 'delapan belas',
                19 => 'sembilan belas',
                20 => 'dua puluh',
                30 => 'tiga puluh',
                40 => 'empat puluh',
                50 => 'lima puluh',
                60 => 'enam puluh',
                70 => 'tujuh puluh',
                80 => 'delapan puluh',
                90 => 'sembilan puluh',
            ];

            if ($number < 20) {
                return $words[$number];
            }

            if ($number < 100) {
                $result = $words[10 * floor($number / 10)];
                if ($number % 10 !== 0) {
                    $result .= ' ' . $words[$number % 10];
                }
                return $result;
            }

            if ($number < 200) {
                $result = 'seratus';
                if ($number % 100 !== 0) {
                    $result .= ' ' . numberToWords($number % 100);
                }
                return $result;
            }

            if ($number < 1000) {
                $result = $words[floor($number / 100)] . ' ratus';
                if ($number % 100 !== 0) {
                    $result .= ' ' . numberToWords($number % 100);
                }
                return $result;
            }

            if ($number < 1000000) {
                $result = numberToWords(floor($number / 1000)) . ' ribu';
                if ($number % 1000 !== 0) {
                    $result .= ' ' . numberToWords($number % 1000);
                }
                return $result;
            }

            if ($number < 1000000000) {
                $result = numberToWords(floor($number / 1000000)) . ' juta';
                if ($number % 1000000 !== 0) {
                    $result .= ' ' . numberToWords($number % 1000000);
                }
                return $result;
            }

            if ($number < 1000000000000) {
                $result = numberToWords(floor($number / 1000000000)) . ' milyar';
                if ($number % 1000000000 !== 0) {
                    $result .= ' ' . numberToWords($number % 1000000000);
                }
                return $result;
            }

            return numberToWords(floor($number / 1000000000000)) . ' trilyun ' . numberToWords($number % 1000000000000);
        }

            @endphp
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
            <h1>PESANAN PEMBELIAN</h1>
        </div>

        <div class="row d-flex justify-content-between">
            <div class="col-8">Pemasok &nbsp;: {{ $results[0]->name; }}</div>
            <div class="col-4">
                <p class="mb-1">PO &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $results[0]->po_number; }}</p>
                <p class="mb-1"></p>
            </div>
        </div>
        <div class="row d-flex justify-content-between">
            <div class="col-8">Telepon &nbsp;&nbsp;&nbsp;&nbsp;:</div>
            <div class="col-4">
                <p class="mb-1">PR No : {{ $results[0]->request_number; }}</p>
                <p class="mb-1"></p>
            </div>
        </div>
        <div class="row d-flex justify-content-between">
            <div class="col-8">Fax &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</div>
            <div class="col-4">
                <p class="mb-1">Tanggal &nbsp;&nbsp;&nbsp;: {{ $results[0]->date; }}</p>
                <p class="mb-1"></p>
            </div>
        </div>
        <div class="row d-flex justify-content-between pb-3">
            <div class="col-8">Alamat PT : Jl. Raya Serang KM 16.8 Desa Telaga, Kec. Cikupa Tangerang-Banten 15710</div>
        </div>

        <div class="row">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-10">
                    <thead class="table-light">
                        <tr>
                            <td>No</td>
                            <td>Keterangan</td>
                            <td>Jumlah</td>
                            <td>Satuan</td>
                            <td>Harga Satuan 	</td>
                            <td>Total</td>
                        </tr>
                    </thead>
                    <tbody>
                    @php
                        $total = 0; // Deklarasi dan inisialisasi variabel total di sini
                    @endphp
                    @if($purchaseOrder->type=='RM')
                        
                        @foreach ($data_detail_rm as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>{{ $data->qty }}</td>
                                    <td>{{ $data->unit }}</td>
                                    <td>{{ number_format($data->price,3,',','.'); }}</td>
                                    <td>{{ number_format($data->qty*$data->price,3,',','.'); }}</td>
                                    @php
                                        $total += $data->qty*$data->price; // Menambahkan nilai $data->amount ke $total di sini
                                    @endphp
                                </tr>
                        @endforeach
                    
                    @elseif($purchaseOrder->type=='TA')
                        
                        @foreach ($data_detail_ta as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>{{ $data->qty }}</td>
                                    <td>{{ $data->unit }}</td>
                                    <td>{{ number_format($data->price,3,',','.'); }}</td>
                                    <td>{{ number_format($data->qty*$data->price,3,',','.'); }}</td>
                                    @php
                                        $total += $data->qty*$data->price; // Menambahkan nilai $data->amount ke $total di sini
                                    @endphp
                                </tr>
                        @endforeach
                    @elseif($purchaseOrder->type=='WIP')
                        
                        @foreach ($data_detail_wip as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>{{ $data->qty }}</td>
                                    <td>{{ $data->unit }}</td>
                                    <td>{{ number_format($data->price,3,',','.'); }}</td>
                                    <td>{{ number_format($data->qty*$data->price,3,',','.'); }}</td>
                                    @php
                                        $total += $data->qty*$data->price; // Menambahkan nilai $data->amount ke $total di sini
                                    @endphp
                                </tr>
                        @endforeach
                    @elseif($purchaseOrder->type=='FG')
                        
                        @foreach ($data_detail_fg as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>{{ $data->qty }}</td>
                                    <td>{{ $data->unit }}</td>
                                    <td>{{ number_format($data->price,3,',','.'); }}</td>
                                    <td>{{ number_format($data->qty*$data->price,3,',','.'); }}</td>
                                    @php
                                        $total += $data->qty*$data->price; // Menambahkan nilai $data->amount ke $total di sini
                                    @endphp
                                </tr>
                        @endforeach
                    @endif
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
        <div class="row align-items-start">
            <div class="col-8">
                <h6>#{{ ucfirst(numberToWords($total))." rupiah" }}#</h6>
                <h6>Term Of Payment : {{ $purchaseOrder->term_payment }}</h6>
                <h6>Delivery Date &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</h6>
                <h6>Catatan &nbsp;&nbsp;:</h6>
            </div>
            <div class="col-4 text-right">
                <div style="display: flex; flex-direction: column;">
                <h6 style="flex-grow: 1;">Sub Total &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;: {{ number_format($total,3,',','.'); }}</h6>
                    <h6 style="flex-grow: 1;">Disc &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;: {{ number_format($purchaseOrder->total_discount,3,',','.'); }}</h6>
                    <h6 style="flex-grow: 1;">Price After Disc &nbsp;&nbsp;: {{ number_format($total-$purchaseOrder->total_discount,3,',','.'); }}</h6>
                    <h6 style="flex-grow: 1;">DP &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp; &nbsp;&nbsp;: {{ number_format($purchaseOrder->down_payment,3,',','.'); }}</h6>
                    <h6 style="flex-grow: 1;">PPn &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;: {{ number_format($purchaseOrder->total_ppn,3,',','.'); }}</h6>
                    <h6 style="flex-grow: 1;">Total &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;: {{ number_format(($total-$purchaseOrder->total_discount)-$purchaseOrder->down_payment-$purchaseOrder->total_ppn,3,',','.'); }}</h6>   
                </div>
            </div>
        </div>
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
