@php
    $client = $document->project->client;
    $providerName = $company?->legal_name ?: ($company?->company_name ?: 'SIS.COM');
    $subject = $document->subject ?: $document->project->title;
    $indonesianTitles = [
        'principal' => 'Kepala Sekolah',
        'vice principal' => 'Wakil Kepala Sekolah',
        'treasurer' => 'Bendahara',
        'foundation chairperson' => 'Ketua Yayasan',
        'director' => 'Direktur',
        'president director' => 'Direktur Utama',
        'leader' => 'Pimpinan',
        'kepala sekolah' => 'Kepala Sekolah',
        'wakil kepala sekolah' => 'Wakil Kepala Sekolah',
        'bendahara' => 'Bendahara',
        'ketua yayasan' => 'Ketua Yayasan',
        'direktur' => 'Direktur',
        'direktur utama' => 'Direktur Utama',
        'pimpinan' => 'Pimpinan',
        'direktur/pimpinan' => 'Direktur/Pimpinan',
    ];
    $schoolSignerTitleSource = $document->school_signer_title ?: 'Pejabat yang Berwenang';
    $schoolSignerTitle = $indonesianTitles[strtolower(trim($schoolSignerTitleSource))]
        ?? $schoolSignerTitleSource;
    $providerSignerTitleSource = $document->provider_signer_title ?: 'Pimpinan SIS.COM';
    $providerSignerTitle = $indonesianTitles[strtolower(trim($providerSignerTitleSource))]
        ?? $providerSignerTitleSource;
@endphp

@if($document->status === 'draft')
    <div class="draft-watermark">DRAFT</div>
@endif

<div class="document-title">{{ strtoupper($document->type_label) }}</div>
<div class="document-number">Nomor: {{ $document->document_number }}</div>

<table class="meta-table">
    <tr><td width="28%">Tanggal</td><td width="3%">:</td><td>{{ $document->document_date->locale('id')->translatedFormat('d F Y') }}</td></tr>
    <tr><td>Perihal</td><td>:</td><td>{{ $subject }}</td></tr>
    <tr><td>Nama Sekolah/Client</td><td>:</td><td>{{ $client->name }}</td></tr>
    @if($client->address)<tr><td>Alamat</td><td>:</td><td>{{ $client->address }}</td></tr>@endif
    <tr><td>Project/Pekerjaan</td><td>:</td><td>{{ $document->project->title }}</td></tr>
    @if($document->funding_source)<tr><td>Sumber Dana</td><td>:</td><td>{{ $document->funding_source }}</td></tr>@endif
</table>

@switch($document->type)
    @case('quotation')
        <p>Dengan hormat,</p>
        <p>Sehubungan dengan kebutuhan {{ $client->name }}, bersama ini kami dari {{ $providerName }} menyampaikan penawaran untuk pekerjaan berikut:</p>
        @break

    @case('negotiation_minutes')
        <p>Pada tanggal {{ $document->document_date->locale('id')->translatedFormat('d F Y') }}, telah dilakukan pembahasan dan negosiasi antara {{ $client->name }} dan {{ $providerName }} mengenai pekerjaan berikut:</p>
        @break

    @case('purchase_order')
        <p>Dokumen ini merupakan Surat Pesanan/Surat Perintah Kerja yang menjadi dasar pelaksanaan pekerjaan antara {{ $client->name }} sebagai Pihak Pertama dan {{ $providerName }} sebagai Pihak Kedua.</p>
        @break

    @case('work_order')
        <p>Melalui Surat Perintah Tugas ini, {{ $client->name }} memberikan penugasan kepada {{ $providerName }} untuk melaksanakan pekerjaan berikut sesuai ruang lingkup dan waktu yang telah disepakati.</p>
        @break

    @case('work_report')
        <p>Dengan ini {{ $providerName }} menyampaikan bahwa pekerjaan berikut telah dilaksanakan sesuai ruang lingkup dan ketentuan yang disepakati:</p>
        @break

    @case('handover')
        <p>Pada tanggal tersebut di atas, {{ $providerName }} telah menyerahkan hasil pekerjaan kepada {{ $client->name }}. Pihak sekolah/client menyatakan telah melakukan pemeriksaan atas hasil pekerjaan sesuai dokumen kesepakatan.</p>
        @break

    @case('payment_request')
        <p>Sehubungan dengan pelaksanaan pekerjaan berikut, kami mengajukan permohonan pembayaran kepada {{ $client->name }} sesuai nilai tagihan dan dokumen pendukung yang telah disampaikan.</p>
        @break

    @case('receipt')
        <p>Sudah terima dari <strong>{{ $client->name }}</strong> pembayaran untuk pekerjaan berikut:</p>
        @break
@endswitch

<table class="detail-table">
    <thead>
        <tr><th>Uraian Pekerjaan</th><th width="28%">Nilai</th></tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <strong>{{ $document->project->title }}</strong>
                @if($document->project->description)
                    <div class="muted">{{ $document->project->description }}</div>
                @endif
            </td>
            <td class="text-right"><strong>Rp {{ number_format($document->amount, 0, ',', '.') }}</strong></td>
        </tr>
    </tbody>
</table>

@if($document->work_start_date || $document->work_end_date)
    <p><strong>Waktu pelaksanaan:</strong>
        {{ $document->work_start_date?->locale('id')->translatedFormat('d F Y') ?? '-' }}
        sampai dengan
        {{ $document->work_end_date?->locale('id')->translatedFormat('d F Y') ?? '-' }}.
    </p>
@endif

@if($document->invoice)
    <p><strong>Invoice terkait:</strong> {{ $document->invoice->invoice_number }}</p>
@endif

@if($document->terms)
    <div class="section-title">Ketentuan</div>
    <ol>
        @foreach(preg_split('/\r\n|\r|\n/', $document->terms) as $term)
            @if(trim($term) !== '')<li>{{ trim($term) }}</li>@endif
        @endforeach
    </ol>
@endif

@if((float) $document->tax_base > 0 || (float) $document->vat_amount > 0 || (float) $document->income_tax_amount > 0)
    <div class="section-title">Informasi Perpajakan</div>
    <table class="tax-table">
        <tr><td>DPP</td><td class="text-right">Rp {{ number_format($document->tax_base, 0, ',', '.') }}</td></tr>
        <tr><td>PPN</td><td class="text-right">Rp {{ number_format($document->vat_amount, 0, ',', '.') }}</td></tr>
        <tr><td>PPh/Potongan Pajak</td><td class="text-right">Rp {{ number_format($document->income_tax_amount, 0, ',', '.') }}</td></tr>
    </table>
@endif

@if($document->notes)
    <div class="section-title">Catatan</div>
    <p>{!! nl2br(e($document->notes)) !!}</p>
@endif

@if($document->type === 'handover')
    <p>Dengan ditandatanganinya berita acara ini, kedua pihak menyatakan serah terima telah dilakukan. Apabila masih terdapat catatan hasil pemeriksaan, catatan tersebut wajib dicantumkan pada bagian catatan dokumen ini.</p>
@elseif($document->type === 'negotiation_minutes')
    <p>Nilai dan ketentuan di atas merupakan hasil pembahasan para pihak dan menjadi dasar penyusunan Surat Pesanan/SPK.</p>
@elseif($document->type === 'receipt')
    <p><strong>Terbilang:</strong> ....................................................................................................................</p>
@else
    <p>Demikian dokumen ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
@endif

<table class="signature-table">
    <tr>
        <td width="50%" class="text-center">
            Pihak Sekolah/Client,<br>
            <strong>{{ $schoolSignerTitle }}</strong>
            <div class="signature-space"></div>
            <strong>{{ $document->school_signer_name ?: '(...................................)' }}</strong>
        </td>
        <td width="50%" class="text-center">
            {{ $company?->city ?: 'Tangerang' }}, {{ $document->document_date->locale('id')->translatedFormat('d F Y') }}<br>
            <strong>{{ $providerSignerTitle }}</strong>
            <div class="signature-space"></div>
            <strong>{{ $document->provider_signer_name ?: ($company?->director_name ?: '(...................................)') }}</strong>
        </td>
    </tr>
</table>
