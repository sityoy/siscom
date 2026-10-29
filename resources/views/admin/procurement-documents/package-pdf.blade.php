<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Paket SPJ - {{ $project->title }}</title>
    <style>
        @page { margin: 18mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #172033; line-height: 1.5; }
        .cover { text-align: center; padding-top: 32px; }
        .cover h1 { color: #0a3b96; font-size: 27px; margin-bottom: 6px; }
        .cover h2 { font-size: 17px; margin: 6px 0 30px; }
        .cover-box { border: 2px solid #0a3b96; padding: 22px; margin: 35px auto; width: 75%; }
        .page-break { page-break-before: always; }
        .header { width: 100%; border-bottom: 3px solid #0a3b96; padding-bottom: 8px; margin-bottom: 18px; }
        .header td { vertical-align: middle; }
        .letterhead { width: 100%; border-collapse: collapse; border-bottom: 3px solid #17498f; margin-bottom: 18px; }
        .letterhead td { vertical-align: middle; padding: 0 0 8px; }
        .letterhead-logo { text-align: left; border-right: 1px solid #9ca3af; padding-right: 9px !important; }
        .letterhead-logo img { width: 135px; max-width: 100%; max-height: 76px; object-fit: contain; }
        .letterhead-info { padding-left: 11px !important; text-align: left; color: #25324a; font-size: 8px; line-height: 1.3; }
        .letterhead-name { color: #17498f; font-size: 20px; font-weight: bold; line-height: 1.05; margin-bottom: 4px; }
        .letterhead-tagline { color: #0783df; font-size: 12px; font-weight: bold; letter-spacing: .2px; margin-bottom: 3px; }
        .letterhead-services { font-size: 8px; margin-bottom: 2px; }
        .letterhead-contact { margin-top: 2px; }
        .letterhead-separator { color: #9ca3af; padding: 0 5px; }
        .company { text-align: right; }
        .company h1 { margin: 0; color: #0a3b96; font-size: 21px; }
        .company p { margin: 1px 0; font-size: 8px; }
        .document-title { text-align: center; font-size: 16px; font-weight: bold; text-decoration: underline; }
        .document-number { text-align: center; margin-bottom: 18px; }
        .meta-table, .detail-table, .tax-table, .signature-table, .checklist, .invoice-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 2px 3px; vertical-align: top; }
        .detail-table, .checklist, .invoice-table { margin: 12px 0; }
        .detail-table th, .checklist th, .invoice-table th { background: #0a3b96; color: white; padding: 7px; border: 1px solid #cbd5e1; }
        .detail-table td, .checklist td, .invoice-table td { padding: 7px; border: 1px solid #cbd5e1; vertical-align: top; }
        .tax-table { width: 58%; margin: 8px 0 12px auto; }
        .tax-table td { padding: 4px; border-bottom: 1px solid #dbe2ea; }
        .section-title { font-weight: bold; color: #0a3b96; margin-top: 12px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .muted { color: #64748b; margin-top: 3px; }
        .signature-table { margin-top: 25px; page-break-inside: avoid; }
        .signature-table td { padding: 0 10px; vertical-align: top; }
        .signature-space { height: 60px; }
        .draft-watermark { position: absolute; top: 42%; left: 20%; transform: rotate(-35deg); font-size: 90px; color: rgba(220,38,38,.10); font-weight: bold; z-index: -1; }
    </style>
</head>
<body>
@php
    $configuredLogo = $company?->logo ? storage_path('app/public/'.$company->logo) : null;
    $defaultLogo = public_path('logo/siscombg2.png');
    $logoPath = $configuredLogo && file_exists($configuredLogo) ? $configuredLogo : $defaultLogo;
    $invoiceStatusLabels = [
        'paid' => 'LUNAS',
        'unpaid' => 'BELUM LUNAS',
        'partial' => 'DIBAYAR SEBAGIAN',
        'cancelled' => 'DIBATALKAN',
    ];
@endphp

@include('admin.procurement-documents._company-letterhead')

<div class="cover">
    <h1>PAKET DOKUMEN PENDUKUNG SPJ</h1>
    <h2>{{ $project->title }}</h2>
    <div class="cover-box">
        <strong>{{ $project->client->name }}</strong><br><br>
        Nilai Project: Rp {{ number_format($project->budget, 0, ',', '.') }}<br>
        Tanggal Invoice: {{ $packageDate->locale('id')->translatedFormat('d F Y') }}<br>
        Tahun: {{ $packageDate->format('Y') }}
    </div>
    <strong>{{ $company?->legal_name ?: ($company?->company_name ?: 'SIS.COM') }}</strong><br>
    Software House & IT Solutions
</div>

<div class="page-break"></div>
<h2 class="text-center">DAFTAR DOKUMEN</h2>
<table class="checklist">
    <thead><tr><th width="8%">No.</th><th>Dokumen</th><th>Nomor</th><th width="18%">Status</th></tr></thead>
    <tbody>
        @forelse($project->procurementDocuments as $document)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $document->type_label }}</td>
                <td>{{ $document->document_number }}</td>
                <td>{{ $document->status_label }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center">Belum ada dokumen rekanan.</td></tr>
        @endforelse
        @foreach($project->invoices as $invoice)
            <tr>
                <td class="text-center">{{ $project->procurementDocuments->count() + $loop->iteration }}</td>
                <td>Invoice</td>
                <td>{{ $invoice->invoice_number }}</td>
                <td>{{ $invoiceStatusLabels[$invoice->status] ?? strtoupper($invoice->status) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@foreach($project->procurementDocuments as $document)
    @php($schoolIssued = in_array($document->type, ['purchase_order', 'work_order'], true))
    <div class="page-break"></div>
    @if($schoolIssued)
        <table class="header">
            <tr>
                <td class="company" style="text-align:center;">
                    <h1>{{ $project->client->name }}</h1>
                    <p>{{ $project->client->address }}</p>
                    <p>Dokumen sekolah — wajib diperiksa dan disahkan oleh pihak sekolah</p>
                </td>
            </tr>
        </table>
    @else
        @include('admin.procurement-documents._company-letterhead')
    @endif
    @include('admin.procurement-documents._pdf-body', ['document' => $document])
@endforeach

@foreach($project->invoices as $invoice)
    <div class="page-break"></div>
    <h2 class="text-center">RINGKASAN INVOICE</h2>
    <table class="meta-table">
        <tr><td width="28%">Nomor Invoice</td><td width="3%">:</td><td>{{ $invoice->invoice_number }}</td></tr>
        <tr><td>Tanggal</td><td>:</td><td>{{ ($invoice->invoice_date ?? $invoice->created_at)->locale('id')->translatedFormat('d F Y') }}</td></tr>
        <tr><td>Jatuh Tempo</td><td>:</td><td>{{ $invoice->due_date?->locale('id')->translatedFormat('d F Y') ?? '-' }}</td></tr>
        <tr><td>Status</td><td>:</td><td>{{ $invoiceStatusLabels[$invoice->status] ?? strtoupper($invoice->status) }}</td></tr>
    </table>
    <table class="invoice-table">
        <thead><tr><th>Uraian</th><th width="10%">Qty</th><th width="23%">Harga</th><th width="23%">Total</th></tr></thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr><td colspan="3" class="text-right">Subtotal</td><td class="text-right">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td></tr>
            <tr><td colspan="3" class="text-right">PPN {{ number_format($invoice->vat_percent, 0) }}%</td><td class="text-right">Rp {{ number_format($invoice->vat, 0, ',', '.') }}</td></tr>
            <tr><td colspan="3" class="text-right">Biaya Layanan</td><td class="text-right">Rp {{ number_format($invoice->service_fee, 0, ',', '.') }}</td></tr>
            @if($invoice->late_fee_amount > 0)
                <tr>
                    <td colspan="3" class="text-right"><strong>Denda ({{ $invoice->late_months }} bulan)</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($invoice->late_fee_amount, 0, ',', '.') }}</strong></td>
                </tr>
            @endif
            <tr><td colspan="3" class="text-right"><strong>Total Tagihan</strong></td><td class="text-right"><strong>Rp {{ number_format($invoice->total_due, 0, ',', '.') }}</strong></td></tr>
        </tbody>
    </table>
    <p class="muted">Perhitungan ringkasan ini menggunakan sumber data yang sama dengan PDF Invoice resmi.</p>
@endforeach
</body>
</html>
