<!doctype html><html lang="id"><head><meta charset="utf-8"><title>Proposal Layanan Digital Sekolah</title>
<style>
@page{margin:12mm 16mm}body{font:9.5pt/1.28 "DejaVu Sans",sans-serif;color:#19263b}
.letterhead,.prices{width:100%;border-collapse:collapse}.letterhead{border-bottom:2pt solid #24446f;margin-bottom:9pt}
.letterhead td{padding-bottom:6pt}.letterhead img{width:85pt}.letterhead-info{font-size:8pt}.letterhead-name{font-size:15pt;font-weight:bold;color:#24446f}
h1{font-size:17pt;margin:8pt 0}h2{font-size:11pt;color:#24446f;margin:8pt 0 3pt}p{margin:4pt 0}
.prices td,.prices th{border-bottom:1px solid #c8d3df;padding:3pt;text-align:left}.prices th{background:#eaf0f6}.prices tr{page-break-inside:avoid}
</style></head><body>
@include('admin.procurement-documents._company-letterhead')
<h1>Proposal Layanan Digital Sekolah</h1>
<p>Kepada Yth. {{ $data['title'] }}<br><strong>{{ $data['school'] }}</strong></p>
<p>Kami menawarkan layanan pengelolaan sekolah dan ujian berbasis komputer. Sekolah dapat memilih layanan sesuai kebutuhan operasional, jumlah bidang yang terlibat, dan agenda ujian.</p>
<h2>Pilihan layanan dan harga penawaran</h2>
<table class="prices"><thead><tr><th>Paket dan cakupan</th><th style="width:25%">Harga per {{ $data['period'] }}</th></tr></thead><tbody>
@foreach($packages as $key=>[$name,$description])
<tr><td><strong>{{ $name }}</strong><br>{{ $description }}
@if($key==='pilihan' && $modules)<br>Modul pilihan: {{ implode(', ', $modules) }}.@endif</td>
<td>@if(isset($data['prices'][$key]))Rp {{ number_format((float)$data['prices'][$key],0,',','.') }}@else Rp __________ @endif</td></tr>
@endforeach</tbody></table>
<h2>Pelaksanaan</h2><p>Sekolah memilih paket dan menyampaikan NPSN serta kontak penanggung jawab. Setelah harga, modul, dan masa layanan disepakati, SIS memverifikasi pendaftaran dan mengaktifkan akses. Untuk paket CBT, kepala sekolah menyiapkan akun pengelola ujian melalui portal.</p>
<h2>Konfirmasi penawaran</h2><p>Mohon sampaikan pilihan paket dan periode kepada tim SIS untuk penyusunan dokumen kerja sama dan invoice. Harga yang masih kosong diisi sebelum penawaran disetujui.</p>
@include('pdf.signatures', [
    'placeDate'=>($company?->city ?: 'Jakarta').', '.\Carbon\Carbon::parse($data['date'])->locale('id')->translatedFormat('d F Y'),
    'issuer'=>['Dibuat oleh,',$company?->director_title ?: 'Direktur',$company?->director_name,$company?->legal_name ?: 'PT SITYOY IT SOLUTIONS'],
    'recipient'=>['Disetujui oleh,',$data['title'],$data['signer'] ?? null,$data['school']],
])
</body></html>
