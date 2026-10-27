<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $document->type_label }} - {{ $document->document_number }}</title>
    <style>
        @page { margin: 18mm 18mm 16mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #172033; line-height: 1.55; }
        .header { width: 100%; border-bottom: 3px solid #0a3b96; padding-bottom: 10px; margin-bottom: 22px; }
        .header td { vertical-align: middle; }
        .letterhead { width: 100%; border-collapse: collapse; border-bottom: 3px solid #17498f; margin-bottom: 22px; }
        .letterhead td { vertical-align: middle; padding: 0 0 9px; }
        .letterhead-logo { text-align: left; border-right: 1px solid #9ca3af; padding-right: 10px !important; }
        .letterhead-logo img { width: 145px; max-width: 100%; max-height: 82px; object-fit: contain; }
        .letterhead-info { padding-left: 12px !important; text-align: left; color: #25324a; font-size: 9px; line-height: 1.35; }
        .letterhead-name { color: #17498f; font-size: 22px; font-weight: bold; line-height: 1.05; margin-bottom: 5px; }
        .letterhead-tagline { color: #0783df; font-size: 13px; font-weight: bold; letter-spacing: .2px; margin-bottom: 4px; }
        .letterhead-services { font-size: 9px; margin-bottom: 2px; }
        .letterhead-contact { margin-top: 2px; }
        .letterhead-separator { color: #9ca3af; padding: 0 6px; }
        .company { text-align: right; }
        .company h1 { margin: 0; color: #0a3b96; font-size: 24px; }
        .company p { margin: 2px 0; font-size: 9px; }
        .document-title { text-align: center; font-size: 17px; font-weight: bold; text-decoration: underline; margin-top: 5px; }
        .document-number { text-align: center; margin-bottom: 20px; }
        .meta-table, .detail-table, .tax-table, .signature-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 2px 3px; vertical-align: top; }
        .detail-table { margin: 14px 0; }
        .detail-table th { background: #0a3b96; color: white; padding: 8px; border: 1px solid #cbd5e1; }
        .detail-table td { padding: 9px; border: 1px solid #cbd5e1; vertical-align: top; }
        .tax-table { width: 58%; margin: 8px 0 14px auto; }
        .tax-table td { padding: 5px; border-bottom: 1px solid #dbe2ea; }
        .section-title { font-weight: bold; color: #0a3b96; margin-top: 14px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .muted { color: #64748b; margin-top: 4px; }
        .signature-table { margin-top: 30px; page-break-inside: avoid; }
        .signature-table td { vertical-align: top; padding: 0 12px; }
        .signature-space { height: 70px; }
        .draft-watermark { position: absolute; top: 42%; left: 20%; transform: rotate(-35deg); font-size: 95px; color: rgba(220,38,38,.12); font-weight: bold; z-index: -1; }
        .footer { position: fixed; bottom: -8mm; left: 0; right: 0; text-align: center; color: #64748b; font-size: 8px; border-top: 1px solid #dbe2ea; padding-top: 5px; }
    </style>
</head>
<body>
@php
    $configuredLogo = $company?->logo ? storage_path('app/public/'.$company->logo) : null;
    $defaultLogo = public_path('logo/siscombg2.png');
    $logoPath = $configuredLogo && file_exists($configuredLogo) ? $configuredLogo : $defaultLogo;
    $schoolIssued = in_array($document->type, ['purchase_order', 'work_order'], true);
@endphp

@if($schoolIssued)
    <table class="header">
        <tr>
            <td class="company" style="text-align:center;">
                <h1>{{ $document->project->client->name }}</h1>
                @if($document->project->client->address)<p>{{ $document->project->client->address }}</p>@endif
                <p>Dokumen sekolah — wajib diperiksa dan disahkan oleh pihak sekolah</p>
            </td>
        </tr>
    </table>
@else
    @include('admin.procurement-documents._company-letterhead')
@endif

@include('admin.procurement-documents._pdf-body')

<div class="footer">
    @if($schoolIssued)
        Dokumen administratif yang dibuat melalui SIS.COM; berlaku setelah diperiksa dan disahkan oleh pihak sekolah.
    @else
        {{ $company?->website ?: 'SIS.COM' }} — Dokumen dibuat melalui Sistem Administrasi Rekanan SIS.COM
    @endif
</div>
</body>
</html>
