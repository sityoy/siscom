<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Invoice | SIS.COM</title>
    <style>
        body { margin: 0; background: #eef3f9; color: #172840; font: 1rem/1.5 system-ui, sans-serif; }
        main { box-sizing: border-box; max-width: 38rem; margin: 7vh auto; padding: 1.6rem; border-radius: .8rem; background: white; border: 1px solid #ccd9e8; }
        header { color: #17498f; font-size: .85rem; font-weight: 700; }
        h1 { margin-block: .35rem 1rem; font-size: clamp(1.4rem, 4vw, 2rem); }
        dl { margin-block: 1.25rem; }
        dl div { display: flex; justify-content: space-between; gap: 1rem; padding: .7rem 0; border-bottom: 1px solid #dbe3ed; }
        dd { margin: 0; font-weight: 650; overflow-wrap: anywhere; text-align: right; }
        @media(max-width: 42rem) { main { margin: 1rem; } }
    </style>
</head>
<body>
<main>
    <header>PT SITYOY IT SOLUTIONS</header>
    @if(!$invoice)
        <h1>Kode invoice tidak ditemukan</h1>
        <p>Minta salinan invoice terbaru kepada penerbit.</p>
    @else
        <h1>{{ $invoice->status === 'cancelled' ? 'Invoice dibatalkan' : 'Invoice tercatat di SIS.COM' }}</h1>
        <dl>
            <div><dt>Nomor invoice</dt><dd>{{ $invoice->invoice_number }}</dd></div>
            <div><dt>Tanggal terbit</dt><dd>{{ ($invoice->invoice_date ?? $invoice->created_at)->copy()->locale('id')->translatedFormat('d F Y') }}</dd></div>
            <div><dt>Nilai tagihan awal</dt><dd>Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</dd></div>
        </dl>
        <p>Cocokkan nomor, tanggal, dan nilai tagihan awal dengan invoice yang Anda terima.</p>
    @endif
</main>
</body>
</html>
