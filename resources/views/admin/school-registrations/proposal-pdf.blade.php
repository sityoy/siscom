<!doctype html><html lang="id"><head><meta charset="utf-8"><title>Proposal Layanan Digital Sekolah</title>
<style>
@page{margin:12mm 16mm}body{font:9.5pt/1.28 "DejaVu Sans",sans-serif;color:#19263b}
.letterhead,.prices{width:100%;border-collapse:collapse}.letterhead{border-bottom:2pt solid #24446f;margin-bottom:9pt}
.letterhead td{padding-bottom:6pt}.letterhead img{width:85pt}.letterhead-info{font-size:8pt}.letterhead-name{font-size:15pt;font-weight:bold;color:#24446f}
h1{font-size:17pt;margin:8pt 0}h2{font-size:11pt;color:#24446f;margin:6pt 0 3pt}p{margin:4pt 0}
.prices td,.prices th{border-bottom:1px solid #c8d3df;padding:3pt;text-align:left}.prices th{background:#eaf0f6}.prices tr{page-break-inside:avoid}
</style></head><body>
@include('admin.procurement-documents._company-letterhead')
<h1>Proposal Layanan Digital Sekolah</h1>
<p>Kepada Yth. {{ $data['title'] }}<br><strong>{{ $data['school'] }}</strong></p>
<p>Kami menawarkan layanan pengelolaan sekolah dan ujian berbasis komputer. Sekolah dapat memilih layanan sesuai kebutuhan operasional, jumlah bidang yang terlibat, dan agenda ujian.</p>
<h2>Pilihan layanan dan harga penawaran</h2>
<table class="prices"><thead><tr><th>Paket</th><th style="width:43%">Tarif dan dasar penghitungan</th></tr></thead><tbody>
@foreach($packages as $key=>[$name,$description])
@php
    $basis = $data['cbt_basis'] ?? 'kesepakatan';
    $price = $data['prices'][$key] ?? null;
    $units = $data['cbt_units'] ?? null;
    $formatRupiah = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
@endphp
<tr><td><strong>{{ $name }}</strong><br>{{ $description }}</td>
<td>
    @if($price !== null){{ $formatRupiah($price) }}<br>@endif
    @if($key === 'cbt')
        {{ $cbtBases[$basis] }}
        @if($basis !== 'kesepakatan' && $units && $price !== null)
            <br>{{ number_format($units, 0, ',', '.') }} {{ $basis }} × {{ $formatRupiah($price) }} = <strong>{{ $formatRupiah($units * $price) }}</strong>
        @endif
        @if(!empty($data['cbt_note']))<br>{{ $data['cbt_note'] }}@endif
    @else
        Per {{ $data['period'] }}
    @endif
    @if($price === null && ($key !== 'cbt' || $basis !== 'kesepakatan'))<br>Tarif akan disepakati bersama.@endif
</td></tr>
@endforeach
</tbody></table>
<h2>Cakupan paket</h2>
@foreach($packages as $key=>[$name,$description])
    <h2>{{ $name }}</h2>
    @if($key !== 'portal')
        <p><strong>Ujian CBT</strong></p>
        <ul>@foreach($cbtDetails as $item)<li>{{ $item }}</li>@endforeach</ul>
    @endif
    @if(in_array($key, ['portal','lengkap','pilihan'], true))
        @php($included = $key === 'pilihan' ? $selectedModules : $modules)
        @if($included)
            <ul>@foreach($included as $module=>$label)<li><strong>{{ $label }}.</strong> {{ $details[$module] }}</li>@endforeach</ul>
        @else
            <p>Modul pendamping akan dipilih dari daftar berikut sesuai kesepakatan.</p>
            <ul>@foreach($modules as $module=>$label)<li><strong>{{ $label }}.</strong> {{ $details[$module] }}</li>@endforeach</ul>
        @endif
    @endif
@endforeach
<h2>Pelaksanaan</h2><p>Sekolah memilih paket dan menyampaikan NPSN serta kontak penanggung jawab. Setelah harga, modul, dan masa layanan disepakati, SIS memverifikasi pendaftaran dan mengaktifkan akses. Untuk paket CBT, kepala sekolah menyiapkan akun pengelola ujian melalui portal.</p>
<h2>Konfirmasi penawaran</h2><p>Mohon sampaikan pilihan paket dan periode kepada tim SIS untuk penyusunan dokumen kerja sama dan invoice. Harga yang masih kosong diisi sebelum penawaran disetujui.</p>
<p><strong>Kontak penawaran</strong> &nbsp; WhatsApp 0813-8320-5359 &bull; Email sityoyitsolutions@gmail.com</p>
@include('pdf.signatures', [
    'placeDate'=>($company?->city ?: 'Jakarta').', '.\Carbon\Carbon::parse($data['date'])->locale('id')->translatedFormat('d F Y'),
    'issuer'=>['Dibuat oleh,',$company?->director_title ?: 'Direktur',$company?->director_name,$company?->legal_name ?: 'PT SITYOY IT SOLUTIONS'],
    'recipient'=>['Disetujui oleh,',$data['title'],$data['signer'] ?? null,$data['school']],
])
</body></html>
