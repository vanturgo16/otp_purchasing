<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PRINT PURCHASE ORDER</title>
    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
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
</head>

<body>
@php
if (!function_exists('numberToWords')) {
    function numberToWords($number) {
        $words = [
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'forty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety',
        ];

        // Check for negative numbers
        if ($number < 0) {
            return 'minus ' . numberToWords(abs($number));
        }

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
            $result = 'one hundred';
            if ($number % 100 !== 0) {
                $result .= ' ' . numberToWords($number % 100);
            }
            return $result;
        }

        if ($number < 1000) {
            $result = $words[floor($number / 100)] . ' hundred';
            if ($number % 100 !== 0) {
                $result .= ' ' . numberToWords($number % 100);
            }
            return $result;
        }

        if ($number < 1000000) {
            $result = numberToWords(floor($number / 1000)) . ' thousand';
            if ($number % 1000 !== 0) {
                $result .= ' ' . numberToWords($number % 1000);
            }
            return $result;
        }

        if ($number < 1000000000) {
            $result = numberToWords(floor($number / 1000000)) . ' million';
            if ($number % 1000000 !== 0) {
                $result .= ' ' . numberToWords($number % 1000000);
            }
            return $result;
        }

        if ($number < 1000000000000) {
            $result = numberToWords(floor($number / 1000000000)) . ' billion';
            if ($number % 1000000000 !== 0) {
                $result .= ' ' . numberToWords($number % 1000000000);
            }
            return $result;
        }

        return numberToWords(floor($number / 1000000000000)) . ' trillion ' . numberToWords($number % 1000000000000);
    }
}

    @endphp
    
    @if($results[0]->status != 'Posted')
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
            <h4 style="margin-top: 3rem;">PURCHASE ORDER</h4>
        </div>


        <div class="row d-flex justify-content-between">
            <div class="col-8">Supplier  &nbsp;&nbsp; &nbsp;&nbsp;: {{ $results[0]->name; }}</div>
            <div class="col-4">
                <p class="mb-1">PO &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $results[0]->po_number; }}</p>
                <p class="mb-1"></p>
            </div>
        </div>
        <div class="row d-flex justify-content-between">
            <div class="col-8">Phone &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $results[0]->telephone; }}</div>
            <div class="col-4">
                <p class="mb-1">PR No : {{ $results[0]->request_number; }}</p>
                <p class="mb-1"></p>
            </div>
        </div>
        <div class="row d-flex justify-content-between">
            <div class="col-8">Fax &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $results[0]->fax != 0 ? $results[0]->fax : '-' }}</div>
            <div class="col-4">
                <p class="mb-1">Date &nbsp;&nbsp;&nbsp;: {{ $results[0]->date; }}</p>
                <p class="mb-1"></p>
            </div>
        </div>
        <div class="row d-flex justify-content-between pb-3">
            <div class="col-8">PT Address : {{ $results[0]->address; }}</div>
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
                    @php
                        $total = 0; // Deklarasi dan inisialisasi variabel total di sini
                    @endphp
                    @if($purchaseOrder->type=='RM')
                        
                        @foreach ($data_detail_rm as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->description }}<br>
                                    {{ $data->remarks }}</td>
                                    <td>{{ $data->qty }}</td>
                                    <td>{{ $data->unit }}</td>
                                    @if($data->currency=='IDR')
                                        <td>IDR {{ number_format($data->price,3,',','.'); }}</td>
                                    @elseif($data->currency=='USD')
                                        <td>USD {{ number_format($data->price,3,',','.'); }}</td>
                                    @elseif($data->currency=='EUR')
                                        <td>EUR {{ number_format($data->price,3,',','.'); }}</td>
                                    @elseif($data->currency=='RMB')
                                        <td>RMB {{ number_format($data->price,3,',','.'); }}</td>
                                    @else
                                        <td>{{ number_format($data->price,3,',','.'); }}</td>
                                    @endif
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
                                    <td>{{ $data->description }}<br>
                                    {{ $data->remarks }}</td>
                                    <td>{{ $data->qty }}</td>
                                    <td>{{ $data->unit }}</td>
                                    @if($data->currency=='IDR')
                                        <td>IDR {{ number_format($data->price,3,',','.'); }}</td>
                                    @elseif($data->currency=='USD')
                                        <td>USD {{ number_format($data->price,3,',','.'); }}</td>
                                    @elseif($data->currency=='EUR')
                                        <td>EUR {{ number_format($data->price,3,',','.'); }}</td>
                                    @elseif($data->currency=='RMB')
                                        <td>RMB {{ number_format($data->price,3,',','.'); }}</td>
                                    @else
                                        <td>{{ number_format($data->price,3,',','.'); }}</td>
                                    @endif
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
                                    <td>{{ $data->description }}<br>
                                    {{ $data->remarks }}</td>
                                    <td>{{ $data->qty }}</td>
                                    <td>{{ $data->unit }}</td>
                                    @if($data->currency=='IDR')
                                        <td>IDR {{ number_format($data->price,3,',','.'); }}</td>
                                    @elseif($data->currency=='USD')
                                        <td>USD {{ number_format($data->price,3,',','.'); }}</td>
                                    @elseif($data->currency=='EUR')
                                        <td>EUR {{ number_format($data->price,3,',','.'); }}</td>
                                    @elseif($data->currency=='RMB')
                                        <td>RMB {{ number_format($data->price,3,',','.'); }}</td>
                                    @else
                                        <td>{{ number_format($data->price,3,',','.'); }}</td>
                                    @endif
                                    <td>{{ number_format($data->qty*$data->price,3,',','.'); }}</td>
                                    @php
                                        $total += $data->amount; // Menambahkan nilai $data->amount ke $total di sini
                                    @endphp
                                </tr>
                        @endforeach
                    @elseif($purchaseOrder->type=='FG')
                        
                        @foreach ($data_detail_fg as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->description }} || {{ $data->perforasi }}<br>
                                    {{ $data->remarks }}</td>
                                    <td>{{ $data->qty }}</td>
                                    <td>{{ $data->unit }}</td>
                                    @if($data->currency=='IDR')
                                        <td>IDR {{ number_format($data->price,3,',','.'); }}</td>
                                    @elseif($data->currency=='USD')
                                        <td>USD {{ number_format($data->price,3,',','.'); }}</td>
                                    @elseif($data->currency=='EUR')
                                        <td>EUR {{ number_format($data->price,3,',','.'); }}</td>
                                    @elseif($data->currency=='RMB')
                                        <td>RMB {{ number_format($data->price,3,',','.'); }}</td>
                                    @else
                                        <td>{{ number_format($data->price,3,',','.'); }}</td>
                                    @endif
                                    <td>{{ number_format($data->qty*$data->price,3,',','.'); }}</td>
                                    @php
                                        $total += $data->qty*$data->price; // Menambahkan nilai $data->amount ke $total di sini
                                    @endphp
                                </tr>
                        @endforeach
                    @elseif($purchaseOrder->type=='Other')
                        
                        @foreach ($data_detail_other as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->description }}<br>
                                    {{ $data->remarks }}</td>
                                    <td>{{ $data->qty }}</td>
                                    <td>{{ $data->unit }}</td>
                                    @if($data->currency=='IDR')
                                        <td>IDR {{ number_format($data->price,3,',','.'); }}</td>
                                    @elseif($data->currency=='USD')
                                        <td>USD {{ number_format($data->price,3,',','.'); }}</td>
                                    @elseif($data->currency=='EUR')
                                        <td>EUR {{ number_format($data->price,3,',','.'); }}</td>
                                    @elseif($data->currency=='RMB')
                                        <td>RMB {{ number_format($data->price,3,',','.'); }}</td>
                                    @else
                                        <td>{{ number_format($data->price,3,',','.'); }}</td>
                                    @endif
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
        <h6>#{{ ucfirst(numberToWords(($total-$purchaseOrder->total_discount)+$purchaseOrder->down_payment+$purchaseOrder->total_ppn)) }} 
@if(isset($purchaseOrder_currency) && $purchaseOrder_currency->currency == 'USD')
    {{ 'USD' }}
@else
    {{ 'rupiah' }}
@endif#</h6>

    <table style="width: 150%; border-collapse: collapse; border: 0;">
        <tr>
            <td style="width: 15%; border: 0; padding: 5px 0;">Term Of Payment</td>
            <td style="width: 2%; text-align: center; border: 0; padding: 5px 0;">:</td>
            <td style="border: 0; padding: 5px 0;">{{ $purchaseOrder->term_payment }}</td>
        </tr>
        <tr>
            <td style="border: 0; padding: 5px 0;">Delivery Date</td>
            <td style="text-align: center; border: 0; padding: 5px 0;">:</td>
            <td style="border: 0; padding: 5px 0;">{{ $purchaseOrder->delivery_date }}</td>
        </tr>
        <tr>
            <td style="border: 0; padding: 5px 0;">Note</td>
            <td style="text-align: center; border: 0; padding: 5px 0;">:</td>
            <td style="border: 0; padding: 5px 0;">{{ $results[0]->supplier_remarks ?? '-' }}</td>
        </tr>
    </table>
</div>

            <div class="col-4 text-right">
                <div style="display: flex; flex-direction: column;">
                    <h6 style="flex-grow: 1;">Sub Total &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;: {{ number_format($total,3,',','.'); }}</h6>
                    <h6 style="flex-grow: 1;">Disc &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;: {{ number_format($purchaseOrder->total_discount,3,',','.'); }}</h6>
                    <h6 style="flex-grow: 1;">Price After Disc &nbsp;&nbsp;: {{ number_format($total-$purchaseOrder->total_discount,3,',','.'); }}</h6>
                    <h6 style="flex-grow: 1;">DP &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp; &nbsp;&nbsp;: {{ number_format($purchaseOrder->down_payment,3,',','.'); }}</h6>
                    <h6 style="flex-grow: 1;">PPn &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;: {{ number_format($purchaseOrder->total_ppn,3,',','.'); }}</h6>
                    <?php $total_final = ($total-$purchaseOrder->total_discount)+$purchaseOrder->down_payment+$purchaseOrder->total_ppn ?>
                    <h6 style="flex-grow: 1;">Total &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;: {{ number_format($total_final,3,',','.'); }}</h6>  
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-4 text-center" style="margin-top: 150px;">
                <p class="mb-5">Purchasing,</p>
                <p>(.............)</p>
            </div>
            <div class="col-4 text-center" style="margin-top: 150px;">
                <p class="mb-5">Direktur,</p>
                <p>(.............)</p>
            </div>
            <div class="col-4 text-center" style="margin-top: 150px;">
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
