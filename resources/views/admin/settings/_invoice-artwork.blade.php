<section class="card" id="tanda-tangan-invoice" aria-labelledby="invoice-artwork-title">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
        <h4 class="mb-0" id="invoice-artwork-title">Tanda Tangan dan Stempel Invoice</h4>
        <span class="badge {{ $artworkReady ? 'badge-success' : 'badge-secondary' }}">{{ $artworkReady ? 'Tersimpan' : 'Belum diunggah' }}</span>
    </div>
    <form method="post" action="{{ route('settings.invoice-artwork.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <p class="text-muted">Unggah kedua gambar dalam format PNG, masing-masing maksimal 2 MB.</p>
            @if($errors->invoiceArtwork->any())
                <ul class="alert alert-danger" role="alert">
                    @foreach($errors->invoiceArtwork->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="invoice-artwork-signature">Tanda tangan PT</label>
                    <input id="invoice-artwork-signature" name="signature" type="file" accept="image/png" class="form-control-file" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="invoice-artwork-stamp">Stempel PT</label>
                    <input id="invoice-artwork-stamp" name="stamp" type="file" accept="image/png" class="form-control-file" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Gambar</button>
        </div>
    </form>
</section>
