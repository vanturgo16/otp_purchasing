<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PRINT PURCHASE ORDERS</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/customPrint.css') }}" rel="stylesheet" type="text/css" />
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
            <h4 style="margin-top: 3rem;">PURCHASE ORDER</h4>
        </div>

        <div class="row">
            <div class="col-8">
                <table class="mb-3">
                    <tbody>
                        <tr>
                            <td class="align-top">Supplier</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">{{ $data->name }}</td>
                        </tr>
                        <tr>
                            <td class="align-top">Phone</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">{{ $data->telephone }}</td>
                        </tr>
                        <tr>
                            <td class="align-top">Fax</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">{{ $data->fax }}</td>
                        </tr>
                        <tr>
                            <td class="align-top">PT Address</td>
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
                            <td class="align-top">PR No.</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">{{ $data->request_number }}</td>
                        </tr>
                        <tr>
                            <td class="align-top">Date</td>
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
                            <td class="align-top">Description</td>
                            <td class="align-top">Qty</td>
                            <td class="align-top">Unit</td>
                            <td class="align-top">Unit Price</td>
                            <td class="align-top">Sub Total</td>
                            {{-- <td>Discount</td>
                            <td>Tax Value</td>
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
                                    {{ $item->price 
                                        ? rtrim(rtrim(number_format($item->price, 3, ',', '.'), '0'), ',') 
                                        : '0' }}
                                    {{-- {{ $item->price ? (strpos($item->price, '.') === false ? number_format($item->price, 0, ',', '.') : number_format($item->price, 3, ',', '.')) : '0' }} --}}
                                </td>
                                <td>
                                    {{ $item->sub_total 
                                        ? rtrim(rtrim(number_format($item->sub_total, 3, ',', '.'), '0'), ',') 
                                        : '0' }}
                                    {{-- {{ $item->sub_total ? (strpos($item->sub_total, '.') === false ? number_format($item->sub_total, 0, ',', '.') : number_format($item->sub_total, 3, ',', '.')) : '0' }} --}}
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
            <div class="col-12">
                <table>
                    <tbody>
                        <tr>
                            <td class="align-top">Own Remark</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">{{ $data->own_remarks ?? '-' }}</td>
                        </tr>
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
                            <td class="align-top">Term Of Payment</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">{{ $data->term_payment }}</td>
                        </tr>
                        <tr>
                            <td class="align-top">Delivery Date</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">{{ $data->delivery_date }}</td>
                        </tr>
                        <tr>
                            <td class="align-top">Supplier Remark</td>
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
                            <td class="align-top">Disc</td>
                            <td class="align-top" style="padding-left: 15px;">:</td>
                            <td class="align-top">
                                {{ $data->total_discount ? (strpos($data->total_discount, '.') === false ? number_format($data->total_discount, 0, ',', '.') : number_format($data->total_discount, 3, ',', '.')) : '0' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="align-top">Price After Disc</td>
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
                            <td class="align-top">Tax</td>
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
                <p class="mb-5">Director,</p>
                <p>(.............)</p>
            </div>
            <div class="col-4 text-center" style="margin-top: 150px;">
                <p class="mb-5">Supplier</p>
                <p>(.............)</p>
            </div>
        </div>

        <div class="row">
            <h6>*Note: After the PO is received, please sign, stamp, and then fax or email it back.</h6>
        </div>
    </div>
</body>
</html>