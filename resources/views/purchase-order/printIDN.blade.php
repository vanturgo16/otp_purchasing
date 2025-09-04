<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>CETAK PESANAN PEMBELIAN</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/customPrint.css') }}" rel="stylesheet" type="text/css" />
</head>

<body>
    @php
        if (!function_exists('numberToWords')) {
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

                // Cek angka negatif
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
        }
    @endphp
    @if(($data->status != 'Posted') && ($data->status != 'Closed'))
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
            <h4 style="margin-top: 3rem;">PESANAN PEMBELIAN</h4>
        </div>

        <div class="row">
            <div class="col-8">
                <table class="mb-3">
                    <tbody>
                        <tr>
                            <td class="align-top">Pemasok</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">{{ $data->name }}</td>
                        </tr>
                        <tr>
                            <td class="align-top">Telepon</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">{{ $data->telephone }}</td>
                        </tr>
                        <tr>
                            <td class="align-top">Fax</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">{{ $data->fax }}</td>
                        </tr>
                        <tr>
                            <td class="align-top">Alamat PT</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">{{ $data->address }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-4">
                <table class="mb-3">
                    <tbody>
                        <tr>
                            <td class="align-top">PO.</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">{{ $data->po_number }}</td>
                        </tr>
                        <tr>
                            <td class="align-top">Nomor PR</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">{{ $data->request_number }}</td>
                        </tr>
                        <tr>
                            <td class="align-top">Tanggal</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">{{ $data->date }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="row">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-10">
                    <thead class="table-light">
                        <tr>
                            <td class="align-top text-center">No.</td>
                            <td class="align-top">Keterangan</td>
                            <td class="align-top">Jumlah</td>
                            <td class="align-top">Satuan</td>
                            <td class="align-top">Harga Satuan</td>
                            <td class="align-top">Sub Total</td>
                            {{-- <td>Diskon</td>
                            <td>Ppn</td>
                            <td>Total</td> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($itemDatas as $item)
                            <tr>
                                <td class="align-top text-center">{{ $loop->iteration }}</td>
                                <td>
                                    {{ $item->product_desc }} @if($item->type_product == 'FG') || {{ $item->perforasi }} @endif <br>
                                    {!! implode('<br>', array_map(fn($chunk) => implode(' ', $chunk), array_chunk(explode(' ', $item->note), 10))) !!}
                                </td>
                                <td>
                                    {{ $item->qty 
                                        ? (strpos(strval($item->qty), '.') !== false 
                                            ? rtrim(rtrim(number_format($item->qty, 6, ',', '.'), '0'), ',') 
                                            : number_format($item->qty, 0, ',', '.')) 
                                        : '0' }}
                                </td>
                                <td>{{ $item->unit_code }}</td>
                                <td>{{ $item->currency }} 
                                    {{ $item->price ? (strpos($item->price, '.') === false ? number_format($item->price, 0, ',', '.') : number_format($item->price, 3, ',', '.')) : '0' }}
                                </td>
                                <td>
                                    {{ $item->sub_total ? (strpos($item->sub_total, '.') === false ? number_format($item->sub_total, 0, ',', '.') : number_format($item->sub_total, 3, ',', '.')) : '0' }}
                                </td>
                                {{-- <td>
                                    {{ $item->discount ? (strpos($item->discount, '.') === false ? number_format($item->discount, 0, ',', '.') : number_format($item->discount, 3, ',', '.')) : '0' }}
                                </td>
                                <td>
                                    {{ $item->tax_value ? (strpos($item->tax_value, '.') === false ? number_format($item->tax_value, 0, ',', '.') : number_format($item->tax_value, 3, ',', '.')) : '0' }}
                                </td>
                                <td>
                                    {{ $item->total_amount ? (strpos($item->total_amount, '.') === false ? number_format($item->total_amount, 0, ',', '.') : number_format($item->total_amount, 3, ',', '.')) : '0' }}
                                </td> --}}
                            </tr>
                        @endforeach
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
            <div class="col-8">
                @if($data->total_amount)
                <h6>
                    # {{ ucfirst(numberToWords($data->total_amount)) }}
                    @if(isset($itemDatas) && $itemDatas[0]->currency == 'USD')
                        {{ 'USD' }}
                    @else
                        {{ 'rupiah' }}
                    @endif#
                </h6>
                @else
                    <h6># 0 #</h6>
                @endif
                <table class="mb-3">
                    <tbody>
                        <tr>
                            <td class="align-top">Ketentuan Pembayaran</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">{{ $data->term_payment }}</td>
                        </tr>
                        <tr>
                            <td class="align-top">Tanggal Pengiriman</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">{{ $data->delivery_date }}</td>
                        </tr>
                        <tr>
                            <td class="align-top">Catatan</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">{{ $data->supplier_remarks ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-4">
                <table class="mb-3">
                    <tbody>
                        <tr>
                            <td class="align-top">Sub Total</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">
                                {{ $data->sub_total ? (strpos($data->sub_total, '.') === false ? number_format($data->sub_total, 0, ',', '.') : number_format($data->sub_total, 3, ',', '.')) : '0' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="align-top">Diskon</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">
                                {{ $data->total_discount ? (strpos($data->total_discount, '.') === false ? number_format($data->total_discount, 0, ',', '.') : number_format($data->total_discount, 3, ',', '.')) : '0' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="align-top">Harga Setelah Diskon</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">
                                {{ $data->total_sub_amount ? (strpos($data->total_sub_amount, '.') === false ? number_format($data->total_sub_amount, 0, ',', '.') : number_format($data->total_sub_amount, 3, ',', '.')) : '0' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="align-top">DP</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">
                                {{ $data->down_payment ? (strpos($data->down_payment, '.') === false ? number_format($data->down_payment, 0, ',', '.') : number_format($data->down_payment, 3, ',', '.')) : '0' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="align-top">Ppn</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">
                                {{ $data->total_ppn ? (strpos($data->total_ppn, '.') === false ? number_format($data->total_ppn, 0, ',', '.') : number_format($data->total_ppn, 3, ',', '.')) : '0' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="align-top">Total</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">
                                {{ $data->total_amount ? (strpos($data->total_amount, '.') === false ? number_format($data->total_amount, 0, ',', '.') : number_format($data->total_amount, 3, ',', '.')) : '0' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
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