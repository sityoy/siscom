<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <title>Invoice PDF</title>

    <style>

        @page {
            margin: 10mm;
        }

        body {

            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1e293b;

        }

        * {

            box-sizing: border-box;

        }

        .wrapper {

            border: 1px solid #dbe2ea;
            border-radius: 14px;
            padding: 12px;

        }

        table {

            width: 100%;
            border-collapse: collapse;

        }

        .header td {

            border: none;
            vertical-align: top;

        }

        .logo {

            width: 150px;

        }

        .company-info {

            text-align: right;

        }

        .company-info h1 {

            margin: 0;
            color: #0f172a;
            font-size: 34px;

        }

        .company-info p {

            margin: 4px 0;
            font-size: 12px;

        }

        .top-line {

            height: 4px;
            background: #0a3b96;
            margin: 14px 0 20px;
            border-radius: 30px;

        }

        .invoice-title {

            font-size: 40px;
            font-weight: bold;
            color: #0a3b96;
            letter-spacing: 1px;

        }

        .invoice-info td {

            border: none;
            padding: 3px 0;
            font-size: 11px;

        }

        .section-card {

            margin-top: 14px;
            margin-bottom: 18px;

        }

        .section-card td {

            border: none;
            vertical-align: top;

        }

        .box {

            border: 1px solid #dbe2ea;
            border-radius: 10px;
            padding: 10px;
            min-height: 70px;

        }

        .section-title {

            color: #0a3b96;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;

        }

        .main-table th {

            background: #0a3b96;
            color: white;
            padding: 7px;
            border: 1px solid #dbe2ea;
            font-size: 10px;

        }

        .main-table td {

            border: 1px solid #dbe2ea;
            padding: 6px;
            vertical-align: top;
            font-size: 10px;

        }

        .text-center {

            text-align: center;

        }

        .text-right {

            text-align: right;

        }

        .summary {

            margin-top: 12px;

        }

        .summary td {

            border: 1px solid #dbe2ea;
            padding: 9px;

        }

        .grand-total {

            background: #edf4ff;
            color: #0a3b96;
            font-weight: bold;
            font-size: 14px;

        }

        .notes-box {

            border: 1px solid #dbe2ea;
            border-radius: 10px;
            padding: 10px;
            min-height: 80px;

        }

        .payment-box {

            margin-top: 10px;
            border: 1px solid #0a3b96;
            border-radius: 12px;
            padding: 10px;

        }

        .payment-title {

            text-align: center;
            font-size: 14px;
            color: #0a3b96;
            font-weight: bold;
            margin-bottom: 12px;

        }

        .bank-table td {

            border: none;
            width: 50%;
            padding: 6px;

        }

       .bank-card {

            border: 1px solid #dbe2ea;
            border-radius: 10px;
            padding: 10px;
            min-height: 60px;

        }

        .bank-name {

            color: #0a3b96;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 13px;

        }

        .footer {

            margin-top: 8px;
            text-align: center;

        }

        .footer h3 {

            color: #0a3b96;
            margin-bottom: 6px;

        }

        .footer-line {

            height: 5px;
            background: #0a3b96;
            border-radius: 30px;
            margin-top: 12px;

        }

        .badge {

            padding: 4px 10px;
            border-radius: 20px;
            color: white;
            font-size: 8px;
            font-weight: bold;

        }

        .paid {

            background: green;

        }

        .unpaid {

            background: red;

        }

        .partial {

            background: orange;

        }

        .cancelled {

            background: gray;

        }

    </style>

</head>

<body>

<div class="wrapper">

    {{-- HEADER --}}
    <table class="header">

        <tr>

            <td width="50%">

                <img src="{{ public_path('logo/siscombg2.png') }}"
                     class="logo">

            </td>

            <td width="50%" class="company-info">

                <h1><a href="https://sis.com">SIS.COM</a></h1>

                <p>Software House & IT Solutions</p>

                <p>Email: tioirfanantoni@gmail.com</p>

                <p>Indonesia</p>

            </td>

        </tr>

    </table>

    <div class="top-line"></div>

    {{-- TITLE --}}
    <table>

        <tr>

            <td width="55%">

                <div class="invoice-title">

                    INVOICE

                </div>

            </td>

            <td width="45%">

                <table class="invoice-info">

                    <tr>

                        <td>Invoice Number</td>
                        <td>:</td>
                        <td>{{ $invoice->invoice_number }}</td>

                    </tr>

                    <tr>

                        <td>Invoice Date</td>
                        <td>:</td>
                        <td>{{ $invoice->created_at->format('d M Y') }}</td>

                    </tr>

                    <tr>

                        <td>Due Date</td>
                        <td>:</td>
                        <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</td>

                    </tr>

                    <tr>

                        <td>Status</td>
                        <td>:</td>

                        <td>

                            <span class="badge {{ $invoice->status }}">

                                {{ strtoupper($invoice->status) }}

                            </span>

                        </td>

                    </tr>

                </table>

            </td>

        </tr>

    </table>

    {{-- CLIENT --}}
    <table class="section-card">

        <tr>

            <td width="50%" style="padding-right:8px;">

                <div class="box">

                    <div class="section-title">

                        BILL TO

                    </div>

                    <strong style="font-size:14px;">

                        {{ $invoice->client->name }}

                    </strong>

                    <p style="margin:6px 0;">

                        {{ $invoice->client->email }}

                    </p>

                    <p style="margin:0;">

                        {{ $invoice->client->phone ?? '-' }}

                    </p>

                </div>

            </td>

            <td width="50%" style="padding-left:8px;">

                <div class="box">

                    <div class="section-title">

                        PROJECT

                    </div>

                    <strong style="font-size:14px;">

                        {{ $invoice->project?->title ?? '-' }}

                    </strong>

                </div>

            </td>

        </tr>

    </table>

    {{-- TABLE --}}
    <table class="main-table">

        <thead>

            <tr>

                <th width="5%">No</th>
                <th width="28%">Deskripsi</th>
                <th width="8%">Qty</th>
                <th width="15%">Harga</th>
                <th width="12%">Durasi</th>
                <th width="15%">Periode</th>
                <th width="17%">Total</th>

            </tr>

        </thead>

        <tbody>

            @foreach($invoice->items as $item)

                <tr>

                    <td class="text-center">

                        {{ $loop->iteration }}

                    </td>

                    <td>

                        <strong style="color:#0a3b96;">

                            {{ $item->description }}

                        </strong>

                    </td>

                    <td class="text-center">

                        {{ $item->qty }}

                    </td>

                    <td class="text-right">

                        Rp {{ number_format($item->price,0,',','.') }}

                    </td>

                    <td class="text-center">

                        @if($item->duration)

                            {{ $item->duration }}
                            {{ $item->duration_type }}

                        @else

                            -

                        @endif

                    </td>

                    <td class="text-center">

                        @if($item->start_date)

                            {{ \Carbon\Carbon::parse($item->start_date)->format('d M Y') }}

                            <br>

                            s/d

                            <br>

                            {{ \Carbon\Carbon::parse($item->end_date)->format('d M Y') }}

                        @else

                            -

                        @endif

                    </td>

                    <td class="text-right">

                        <strong style="color:#0a3b96;">

                            Rp {{ number_format($item->total,0,',','.') }}

                        </strong>

                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

    {{-- SUMMARY + PAYMENT --}}
<table style="margin-top:14px; width:100%;">

    <tr>

        {{-- LEFT --}}
        <td width="58%"
            style="vertical-align:top; padding-right:10px; border:none;">

            <div class="notes-box">

                <div class="section-title">

                    CATATAN

                </div>

                <p style="line-height:1.6; margin:0 0 14px 0;">

                    {{ $invoice->notes ?? 'Terima kasih atas kepercayaan Anda menggunakan layanan SIS.COM.' }}

                </p>

                <div class="section-title">

                    INFORMASI PEMBAYARAN

                </div>

                <table style="width:100%; margin-top:8px;">

                    <tr>

                        <td width="33%"
                            style="padding-right:6px; border:none; vertical-align:top;">

                            <div style="
                                border:1px solid #dbe2ea;
                                border-radius:8px;
                                padding:8px;
                                min-height:70px;
                            ">

                                <strong style="color:#0a3b96; font-size:10px;">

                                    BANK JAKARTA

                                </strong>

                                <br><br>

                                300-23-31341-1

                                <br>

                                A/N TIO IRFAN ANTONI

                            </div>

                        </td>

                        <td width="33%"
                            style="padding-right:6px; border:none; vertical-align:top;">

                            <div style="
                                border:1px solid #dbe2ea;
                                border-radius:8px;
                                padding:8px;
                                min-height:70px;
                            ">

                                <strong style="color:#0a3b96; font-size:10px;">

                                    BANK MANDIRI

                                </strong>

                                <br><br>

                                11800-1378-1322

                                <br>

                                A/N TIO IRFAN ANTONI

                            </div>

                        </td>

                        <td width="33%"
                            style="border:none; vertical-align:top;">

                            <div style="
                                border:1px solid #dbe2ea;
                                border-radius:8px;
                                padding:8px;
                                min-height:70px;
                            ">

                                <strong style="color:#0a3b96; font-size:10px;">

                                    BANK BCA

                                </strong>

                                <br><br>

                                5310-7411-42

                                <br>

                                A/N TIO IRFAN ANTONI

                            </div>

                        </td>

                    </tr>

                </table>

            </div>

        </td>

        {{-- RIGHT --}}
<td width="42%"
    style="vertical-align:top; border:none;">

    @php

        $cashbackAmount =
            ($invoice->grand_total * $invoice->cashback) / 100;


        $finalTotal =
            $invoice->grand_total;

        // $finalTotal =
        //     $invoice->grand_total - $cashbackAmount;

    @endphp

    <table class="summary">

        <tr>

            <td>

                Subtotal

            </td>

            <td class="text-right">

                Rp {{ number_format($invoice->subtotal,0,',','.') }}

            </td>

        </tr>

        <tr>

            <td>

                PPN {{ number_format($invoice->vat_percent,0) }}%

            </td>

            <td class="text-right">

                Rp {{ number_format($invoice->vat,0,',','.') }}

            </td>

        </tr>

        <tr>

            <td>

                Biaya Layanan

            </td>

            <td class="text-right">

                Rp {{ number_format($invoice->service_fee,0,',','.') }}

            </td>

        </tr>

        <tr class="grand-total">

            <td>

                GRAND TOTAL

            </td>

            <td class="text-right">

                Rp {{ number_format($invoice->grand_total,0,',','.') }}

            </td>

        </tr>

        <tr>

            <td style="color:green; font-weight:bold;">

                Cashback Reward {{ $invoice->cashback }}%

            </td>

            <td class="text-right"
                style="color:green; font-weight:bold;">

                Rp {{ number_format($cashbackAmount,0,',','.') }}

            </td>

        </tr>

        <tr style="
            background:#0a3b96;
            color:white;
            font-weight:bold;
            font-size:14px;
        ">

            <td>

                TOTAL DIBAYAR

            </td>

            <td class="text-right">

                Rp {{ number_format($finalTotal,0,',','.') }}

            </td>

        </tr>

    </table>

</td>


    </div>

    {{-- FOOTER --}}
    <div class="footer">

        <h3>

            Thank you for your business!

        </h3>

        <a href="https://sis.com"> SIS.COM </a> - Software House & IT Solutions

        <div class="footer-line"></div>

    </div>

</div>

</body>
</html>
