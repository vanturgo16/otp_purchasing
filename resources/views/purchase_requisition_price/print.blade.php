<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PRINT PURCHASE REQUISITION WITH PRICE</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/customPrint.css') }}" rel="stylesheet" type="text/css" />
</head>

<body>
    @if(($data->status != 'Posted') && ($data->status != 'Created PO') && ($data->status != 'Closed'))
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
            <h4 style="margin-top: 3rem;">PURCHASE REQUISITION WITH PRICE</h4>
        </div>

        <table class="mb-3">
            <tbody>
                <tr>
                    <td class="align-top">Request No.</td>
                    <td class="align-top" style="padding-left: 15px;">:</td>
                    <td class="align-top">{{ $data->request_number }}</td>
                </tr>
                <tr>
                    <td class="align-top">Request Date</td>
                    <td class="align-top" style="padding-left: 15px;">:</td>
                    <td class="align-top">{{ $data->date }}</td>
                </tr>
                <tr>
                    <td class="align-top">Supplier</td>
                    <td class="align-top" style="padding-left: 15px;">:</td>
                    <td class="align-top">{{ $data->name }}</td>
                </tr>
            </tbody>
        </table>
        
        <div class="row">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-10">
                    <thead class="table-light">
                        <tr>
                            <td class="align-top text-center">No.</td>
                            <td class="align-top">Item Code</td>
                            <td class="align-top">Description</td>
                            <td class="align-top">Qty</td>
                            <td class="align-top">Unit</td>
                            <td class="align-top">Required</td>
                            <td class="align-top">CC/CO</td>
                            <td class="align-top">Unit Price</td>
                            <td class="align-top">Sub Total</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($itemDatas as $item)
                            <tr>
                                <td class="align-top text-center">{{ $loop->iteration }}</td>
                                <td>{{ $item->code }}</td>
                                <td>
                                    {{ $item->product_desc }} @if($item->type_product == 'FG') || {{ $item->perforasi }} @endif <br>
                                    {{ $item->remarks }}
                                </td>
                                <td>
                                    {{ $item->qty 
                                        ? (strpos(strval($item->qty), '.') !== false 
                                            ? rtrim(rtrim(number_format($item->qty, 6, ',', '.'), '0'), ',') 
                                            : number_format($item->qty, 0, ',', '.')) 
                                        : '0' }}
                                </td>
                                <td>{{ $item->unit_code }}</td>
                                <td>{{ $item->required_date }}</td>
                                <td>{{ $item->cc_co_name }}</td>
                                <td>{{ $item->currency }} 
                                    {{ $item->price ? (strpos($item->price, '.') === false ? number_format($item->price, 0, ',', '.') : number_format($item->price, 3, ',', '.')) : '0' }}
                                </td>
                                <td>
                                    {{ $item->sub_total ? (strpos($item->sub_total, '.') === false ? number_format($item->sub_total, 0, ',', '.') : number_format($item->sub_total, 3, ',', '.')) : '0' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <ul style="list-style-type: '- ';">
                Note : {{ $data->note; }}
            </ul>
        </div>
        <hr>
        <div class="row">
            <div class="col-4 text-center">
                <p class="mb-5">Requested By,</p>
                <p>(.............)</p>
            </div>
            <div class="col-4 text-center">
                <p class="mb-5">Approved by,</p>
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