<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 12mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; line-height: 1.4; }
        table { width: 100%; border-collapse: collapse; }
        .letterhead { width: 100%; border-collapse: collapse; border-bottom: 3px solid #17498f; margin-bottom: 17px; }
        .letterhead td { border: none; vertical-align: middle; padding: 0 0 8px; }
        .letterhead-logo { text-align: left; border-right: 1px solid #9ca3af !important; padding-right: 9px !important; }
        .letterhead-logo img { width: 135px; max-width: 100%; max-height: 76px; object-fit: contain; }
        .letterhead-info { padding-left: 11px !important; text-align: left; color: #25324a; font-size: 8px; line-height: 1.3; }
        .letterhead-name { color: #17498f; font-size: 20px; font-weight: bold; line-height: 1.05; margin-bottom: 4px; }
        .letterhead-tagline { color: #0783df; font-size: 12px; font-weight: bold; letter-spacing: .2px; margin-bottom: 3px; }
        .letterhead-services { font-size: 8px; margin-bottom: 2px; }
        .letterhead-contact { margin-top: 2px; }
        .letterhead-separator { color: #9ca3af; padding: 0 5px; }
        .invoice-heading td { vertical-align: top; border: none; }
        .invoice-title { color: #17498f; font-size: 29px; font-weight: bold; letter-spacing: 1px; }
        .invoice-meta td { padding: 2px 0; border: none; font-size: 9px; }
        .invoice-meta td:first-child { color: #64748b; width: 42%; }
        .badge { display: inline-block; padding: 3px 8px; color: #fff; border-radius: 10px; font-size: 8px; font-weight: bold; }
        .paid { background: #15803d; }
        .unpaid, .overdue { background: #dc2626; }
        .partial { background: #d97706; }
        .cancelled { background: #64748b; }
        .info-grid { margin: 14px 0; }
        .info-grid td { width: 50%; border: none; vertical-align: top; }
        .info-grid td:first-child { padding-right: 6px; }
        .info-grid td:last-child { padding-left: 6px; }
        .box { border: 1px solid #dbe2ea; padding: 9px; min-height: 62px; }
        .section-title { color: #17498f; font-size: 10px; font-weight: bold; margin-bottom: 5px; }
        .items th { background: #17498f; color: #fff; padding: 6px 5px; border: 1px solid #dbe2ea; font-size: 8px; }
        .items td { padding: 6px 5px; border: 1px solid #dbe2ea; vertical-align: top; font-size: 8px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .summary-payment { margin-top: 13px; }
        .summary-payment > tbody > tr > td { border: none; vertical-align: top; }
        .payment-column { width: 58%; padding-right: 8px; }
        .summary-column { width: 42%; padding-left: 8px; }
        .notes { border: 1px solid #dbe2ea; padding: 8px; margin-bottom: 8px; min-height: 35px; }
        .banks { table-layout: fixed; }
        .banks td { border: none; vertical-align: top; padding-right: 5px; }
        .banks td:last-child { padding-right: 0; }
        .bank-card { border: 1px solid #dbe2ea; padding: 7px; min-height: 62px; font-size: 7px; }
        .bank-name { color: #17498f; font-size: 8px; font-weight: bold; margin-bottom: 7px; }
        .summary td { border: 1px solid #dbe2ea; padding: 6px; font-size: 8px; }
        .summary .late-fee { color: #dc2626; font-weight: bold; }
        .summary .total td { background: #17498f; color: #fff; font-size: 11px; font-weight: bold; }
        .signatures { margin-top: 22px; page-break-inside: avoid; }
        .signatures td { width: 50%; border: none; vertical-align: top; text-align: center; padding: 0 18px; }
        .signature-space { height: 52px; }
        .signer-name { display: block; font-weight: bold; text-decoration: underline; }
        .footer { margin-top: 18px; padding-top: 6px; border-top: 2px solid #17498f; text-align: center; color: #64748b; font-size: 7px; }
    </style>
</head>
<body>
@php
    $configuredLogo = $company?->logo ? storage_path('app/public/'.$company->logo) : null;
    $defaultLogo = public_path('logo/siscombg2.png');
    $logoPath = $configuredLogo && file_exists($configuredLogo) ? $configuredLogo : $defaultLogo;
    $banks = collect([
        ['name' => 'BANK JAKARTA', 'number' => $company?->bank_jakarta, 'holder' => $company?->bank_jakarta_name],
        ['name' => 'BANK MANDIRI', 'number' => $company?->bank_mandiri, 'holder' => $company?->bank_mandiri_name],
        ['name' => 'BANK BCA', 'number' => $company?->bank_bca, 'holder' => $company?->bank_bca_name],
    ])->filter(fn (array $bank) => filled($bank['number']))->values();
    $titleTranslations = [
        'kepala sekolah' => 'Principal',
        'wakil kepala sekolah' => 'Vice Principal',
        'bendahara' => 'Treasurer',
        'ketua yayasan' => 'Foundation Chairperson',
        'direktur' => 'Director',
        'direktur utama' => 'President Director',
        'pimpinan' => 'Director',
        'direktur/pimpinan' => 'Director',
    ];
    $schoolSignerName = $invoice->client->authorized_official_name;
    $schoolSignerTitleSource = $invoice->client->authorized_official_title ?: 'Principal';
    $schoolSignerTitle = $titleTranslations[strtolower(trim($schoolSignerTitleSource))]
        ?? $schoolSignerTitleSource;
    $directorName = $company?->director_name;
    $directorTitleSource = $company?->director_title ?: 'Director';
    $directorTitle = $titleTranslations[strtolower(trim($directorTitleSource))]
        ?? $directorTitleSource;
    $companyName = $company?->legal_name ?: ($company?->company_name ?: 'SIS.COM');
    $city = $company?->city ?: 'Tangerang';
    $isOverdue = $invoice->late_months > 0 && $invoice->status !== 'paid';
    $displayStatus = $isOverdue ? 'OVERDUE' : strtoupper($invoice->status);
@endphp

@include('admin.procurement-documents._company-letterhead')

<table class="invoice-heading">
    <tr>
        <td width="58%"><div class="invoice-title">INVOICE</div></td>
        <td width="42%">
            <table class="invoice-meta">
                <tr><td>Invoice Number</td><td>: {{ $invoice->invoice_number }}</td></tr>
                <tr><td>Invoice Date</td><td>: {{ ($invoice->invoice_date ?? $invoice->created_at)->format('d M Y') }}</td></tr>
                <tr><td>Invoice Type</td><td>: {{ $invoice->invoice_type === 'renewal' ? 'Package Renewal' : 'Project' }}</td></tr>
                <tr><td>Due Date</td><td>: {{ $invoice->due_date?->format('d M Y') ?? '-' }}</td></tr>
                @if($invoice->late_fee_active)
                    <tr><td>Late Fee</td><td>: Rp {{ number_format($invoice->late_fee_per_month, 0, ',', '.') }} / 30 days</td></tr>
                @endif
                <tr><td>Status</td><td>: <span class="badge {{ $isOverdue ? 'overdue' : $invoice->status }}">{{ $displayStatus }}</span></td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="info-grid">
    <tr>
        <td>
            <div class="box">
                <div class="section-title">BILL TO</div>
                <strong>{{ $invoice->client->name }}</strong><br>
                @if($invoice->client->address){{ $invoice->client->address }}<br>@endif
                @if($invoice->client->email){{ $invoice->client->email }}@endif
            </div>
        </td>
        <td>
            <div class="box">
                <div class="section-title">PROJECT</div>
                <strong>{{ $invoice->project?->title ?? 'Package Renewal' }}</strong>
                @if($invoice->project?->description)<br>{{ $invoice->project->description }}@endif
            </div>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th width="5%">No.</th><th width="29%">Description</th><th width="7%">Qty</th>
            <th width="16%">Price</th><th width="12%">Duration</th><th width="15%">Period</th><th width="16%">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td><strong>{{ $item->description }}</strong></td>
                <td class="text-center">{{ $item->qty }}</td>
                <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                <td class="text-center">
                    @if($item->duration)
                        {{ $item->duration }}
                        {{ match ($item->duration_type) {
                            'Hari' => 'Day(s)', 'Bulan' => 'Month(s)', 'Tahun' => 'Year(s)', default => $item->duration_type,
                        } }}
                    @else - @endif
                </td>
                <td class="text-center">
                    @if($item->start_date)
                        {{ \Carbon\Carbon::parse($item->start_date)->format('d M Y') }}<br>to<br>
                        {{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('d M Y') : '-' }}
                    @else - @endif
                </td>
                <td class="text-right"><strong>Rp {{ number_format($item->total, 0, ',', '.') }}</strong></td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="summary-payment">
    <tr>
        <td class="payment-column">
            <div class="notes">
                <div class="section-title">NOTES</div>
                {{ $invoice->notes ?: 'Thank you for trusting '.$companyName.' with your technology needs.' }}
            </div>
            <div class="section-title">PAYMENT INFORMATION</div>
            @if($banks->isNotEmpty())
                <table class="banks"><tr>
                    @foreach($banks as $bank)
                        <td width="{{ 100 / $banks->count() }}%">
                            <div class="bank-card">
                                <div class="bank-name">{{ $bank['name'] }}</div>
                                <strong>{{ $bank['number'] }}</strong><br>
                                ACCOUNT HOLDER:<br>{{ $bank['holder'] ?: '-' }}
                            </div>
                        </td>
                    @endforeach
                </tr></table>
            @else
                <div class="notes">Payment account has not been configured in Company Settings.</div>
            @endif
        </td>
        <td class="summary-column">
            <table class="summary">
                <tr><td>Subtotal</td><td class="text-right">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td></tr>
                <tr><td>VAT {{ number_format($invoice->vat_percent, 0) }}%</td><td class="text-right">Rp {{ number_format($invoice->vat, 0, ',', '.') }}</td></tr>
                <tr><td>Service Fee</td><td class="text-right">Rp {{ number_format($invoice->service_fee, 0, ',', '.') }}</td></tr>
                @if($invoice->late_fee_amount > 0)
                    <tr class="late-fee"><td>Late Fee ({{ $invoice->late_months }} {{ (int) $invoice->late_months === 1 ? 'Month' : 'Months' }})</td><td class="text-right">Rp {{ number_format($invoice->late_fee_amount, 0, ',', '.') }}</td></tr>
                @endif
                <tr class="total"><td>TOTAL</td><td class="text-right">Rp {{ number_format($invoice->total_due, 0, ',', '.') }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="signatures">
    <tr>
        <td>
            Approved by,<br><strong>{{ $schoolSignerTitle }}</strong>
            <div class="signature-space"></div>
            <span class="signer-name">{{ $schoolSignerName ?: '(...................................)' }}</span>
            {{ $invoice->client->name }}
        </td>
        <td>
            {{ $city }}, {{ ($invoice->invoice_date ?? $invoice->created_at)->format('d F Y') }}<br><strong>{{ $directorTitle }}</strong>
            <div class="signature-space"></div>
            <span class="signer-name">{{ $directorName ?: '(...................................)' }}</span>
            {{ $companyName }}
        </td>
    </tr>
</table>

<div class="footer">
    Thank you for your business!<br>
    {{ $company?->website ?: 'SIS.COM' }} — {{ $companyName }}
</div>
</body>
</html>
