<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $agreement->number ? 'MoU '.$agreement->number : 'Draf MoU' }}</title>
    <style>
        @page { margin: 15mm 18mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; line-height: 1.45; color: #1e293b; }
        .letterhead { width: 100%; border-collapse: collapse; border-bottom: 3px solid #17498f; margin-bottom: 15px; }
        .letterhead td { vertical-align: middle; padding-bottom: 8px; }
        .letterhead-logo img { width: 115px; }
        .letterhead-info { padding-left: 10px; font-size: 8pt; }
        .letterhead-name { color: #17498f; font-weight: bold; font-size: 17pt; }
        .letterhead-tagline { color: #17498f; font-weight: bold; }
        .letterhead-separator { color: #64748b; padding: 0 4px; }
        .heading { text-align: center; margin: 14px 0; }
        .heading h1 { margin: 0; color: #17498f; font-size: 18pt; }
        .heading p { margin: 3px 0; overflow-wrap: break-word; }
        .draft { background: #fef3c7; color: #78350f; text-align: center; padding: 6px; font-weight: bold; }
        .meta { width: 100%; border-collapse: collapse; margin: 14px 0; table-layout: fixed; }
        .meta td { border: 1px solid #dbe2ea; padding: 5px 8px; overflow-wrap: break-word; }
        .meta td:first-child { width: 38%; background: #eff6ff; }
        h2 { font-size: 11pt; margin: 12px 0 4px; color: #17498f; page-break-after: avoid; }
        .details { margin: 0; overflow-wrap: break-word; }
        .signatures { width: 100%; table-layout: fixed; text-align: center; border-collapse: collapse; margin-top: 22px; page-break-inside: avoid; }
        .signatures td { width: 50%; padding: 0 8px; vertical-align: top; overflow-wrap: break-word; }
        .signature-space { height: 43px; }
    </style>
</head>
<body>
    @include('admin.procurement-documents._company-letterhead')

    @unless($agreement->finalized_at)
        <div class="draft">DRAF UNTUK PENINJAUAN</div>
    @endunless

    <div class="heading">
        <h1>NOTA KESEPAHAMAN (MoU)</h1>
        <p><strong>{{ $agreement->title }}</strong></p>
        @if($agreement->number)
            <p>Nomor {{ $agreement->number }}</p>
        @endif
    </div>

    <p>Dokumen kerja sama antara <strong>{{ $agreement->provider_name }}</strong> sebagai Pihak Pertama dan
        <strong>{{ $agreement->client_name }}</strong> sebagai Pihak Kedua.</p>

    @if(filled($agreement->details['provider_address'] ?? null) || filled($agreement->details['client_address'] ?? null))
        <p class="details">
            @if(filled($agreement->details['provider_address'] ?? null))<strong>Alamat Pihak Pertama</strong> {{ $agreement->details['provider_address'] }}<br>@endif
            @if(filled($agreement->details['client_address'] ?? null))<strong>Alamat Pihak Kedua</strong> {{ $agreement->details['client_address'] }}@endif
        </p>
    @endif

    <table class="meta">
        <tr><td>Tempat dan tanggal MoU</td><td>{{ $agreement->city }}, {{ $agreement->document_date->translatedFormat('d F Y') }}</td></tr>
        <tr><td>Invoice sumber</td><td>{{ $agreement->invoice_reference }}{{ $agreement->invoice_date ? ' • '.$agreement->invoice_date->translatedFormat('d F Y') : '' }}</td></tr>
        <tr><td>Nilai bruto referensi</td><td>Rp {{ number_format((float) $agreement->invoice_total, 0, ',', '.') }}</td></tr>
        <tr><td>Masa berlaku</td><td>{{ $agreement->starts_on->translatedFormat('d F Y') }} s.d. {{ $agreement->ends_on->translatedFormat('d F Y') }}</td></tr>
    </table>

    @foreach($agreement->articles() as $heading => $body)
        <h2>{{ $agreement->details ? 'Pasal ' : '' }}{{ $loop->iteration }}. {{ $heading }}</h2>
        <p class="details">{!! nl2br(e($body)) !!}</p>
    @endforeach

    <table class="signatures">
        <tr><td><strong>Pihak Pertama</strong></td><td><strong>Pihak Kedua</strong></td></tr>
        <tr><td>{{ $agreement->provider_title }}</td><td>{{ $agreement->client_title }}</td></tr>
        <tr><td>{{ $agreement->provider_name }}</td><td>{{ $agreement->signatureCompanyName() }}</td></tr>
        <tr><td class="signature-space"></td><td></td></tr>
        <tr><td><strong>{{ $agreement->provider_signer }}</strong></td><td><strong>{{ $agreement->client_signer }}</strong></td></tr>
        
    </table>
</body>
</html>
