<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Nota Pembayaran {{ $receiptNumber }}</title>
    <style>
        @page { margin: 26pt; }
        body { color: #1e293b; font: 10pt/1.45 "DejaVu Sans", sans-serif; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        .letterhead { border-bottom: 2pt solid #17498f; margin-bottom: 16pt; }
        .letterhead td { padding: 0 0 9pt; vertical-align: middle; }
        .letterhead .logo { width: 62pt; padding-right: 9pt; }
        .logo img { max-width: 58pt; max-height: 40pt; }
        .company { color: #17498f; font-size: 13pt; font-weight: bold; }
        .contact { color: #475569; font-size: 8pt; overflow-wrap: break-word; }
        h1 { margin: 0 0 2pt; color: #17498f; font-size: 19pt; }
        .reference { color: #475569; margin: 0 0 12pt; }
        .details { table-layout: fixed; }
        .details th, .details td { padding: 5pt 8pt; border-bottom: 1pt solid #dbe2ea; vertical-align: top; }
        .details th { width: 34%; color: #475569; font-weight: normal; text-align: left; }
        .details td { overflow-wrap: break-word; }
        .total th, .total td { background: #eef4fc; color: #17498f; font-weight: bold; }
        .total td { font-size: 13pt; }
        .signature { margin-top: 15pt; table-layout: fixed; page-break-inside: avoid; }
        .signature td { width: 50%; text-align: center; font-size: 9pt; overflow-wrap: break-word; }
        .signature-space { height: 37pt; }
        .signer { font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
@php
    $invoice = $payment->invoice;
    $companyName = $company?->legal_name ?: ($company?->company_name ?: 'PT SITYOY IT SOLUTIONS');
    $paymentDate = \Illuminate\Support\Carbon::parse($payment->payment_date)->locale('id')->translatedFormat('d F Y');
    $amount = (float) $payment->amount;
    $formattedAmount = 'Rp '.number_format($amount, floor($amount) === $amount ? 0 : 2, ',', '.');
    $configuredLogo = $company?->logo ? storage_path('app/public/'.$company->logo) : null;
    $logoPath = $configuredLogo && is_file($configuredLogo) ? $configuredLogo : public_path('logo/siscombg2.png');
@endphp

<table class="letterhead">
    <tr>
        @if(is_file($logoPath))
            <td class="logo"><img src="{{ $logoPath }}" alt="Logo penyedia"></td>
        @endif
        <td>
            <div class="company">{{ $companyName }}</div>
            @if($company?->company_email)
                <div class="contact">{{ $company->company_email }}</div>
            @endif
        </td>
    </tr>
</table>

<h1>Nota Pembayaran</h1>
<p class="reference">{{ $receiptNumber }}</p>

<table class="details">
    <tr><th scope="row">Diterima dari</th><td>{{ $invoice->client->signatureCompanyName() }}</td></tr>
    <tr><th scope="row">Nomor invoice</th><td>{{ $invoice->invoice_number }}</td></tr>
    <tr><th scope="row">Tanggal pembayaran</th><td>{{ $paymentDate }}</td></tr>
    @if(filled($payment->payment_method))
        <tr><th scope="row">Metode pembayaran</th><td>{{ $payment->payment_method }}</td></tr>
    @endif
    <tr class="total"><th scope="row">Jumlah diterima</th><td>{{ $formattedAmount }}</td></tr>
</table>

<table class="signature">
    <tr>
        <td></td>
        <td>
            {{ $company?->city ?: 'Tangerang' }}, {{ $paymentDate }}<br>
            Diterima oleh,<br>
            <strong>{{ $company?->director_title ?: 'Direktur' }}</strong><br>
            {{ $companyName }}
            <div class="signature-space"></div>
            <span class="signer">{{ $company?->director_name ?: '(...................................)' }}</span>
        </td>
    </tr>
</table>
</body>
</html>
