<section class="card" id="rekening-pembayaran" aria-labelledby="rekening-title">
    <div class="card-header">
        <h4 id="rekening-title" class="mb-1">Rekening Pembayaran</h4>
        <p class="text-muted mb-0">Hanya rekening aktif yang tampil pada instruksi pembayaran.</p>
    </div>
    <div class="card-body">
        <details id="form-rekening" class="border rounded p-3 mb-4"
                 @if($editingAccount || $errors->bankAccount->any()) open @endif>
            <summary class="font-weight-bold">{{ $editingAccount ? 'Edit Rekening' : 'Tambah Rekening' }}</summary>
            <form action="{{ $editingAccount ? route('settings.bank-accounts.update', $editingAccount) : route('settings.bank-accounts.store') }}"
                  method="POST" class="mt-3">
                @csrf
                @if($editingAccount)
                    @method('PUT')
                @endif

                @if($errors->bankAccount->any())
                    <ul class="alert alert-danger list-unstyled" role="alert">
                        @foreach($errors->bankAccount->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                @endif

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="payment-bank-name">Nama Bank</label>
                        <input type="text" class="form-control" id="payment-bank-name" name="bank_name"
                               value="{{ old('bank_name', $editingAccount?->bank_name) }}" maxlength="100" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="payment-account-number">Nomor Rekening</label>
                        <input type="text" class="form-control" id="payment-account-number" name="account_number"
                               value="{{ old('account_number', $editingAccount?->account_number) }}" maxlength="255" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="payment-account-holder">Atas Nama</label>
                        <input type="text" class="form-control" id="payment-account-holder" name="account_holder"
                               value="{{ old('account_holder', $editingAccount?->account_holder) }}" maxlength="255" required>
                    </div>
                </div>

                <button class="btn btn-primary" type="submit">{{ $editingAccount ? 'Simpan Rekening' : 'Tambah Rekening' }}</button>
                @if($editingAccount)
                    <a class="btn btn-outline-secondary" href="{{ route('settings.index') }}#rekening-pembayaran">Batal</a>
                @endif
            </form>
        </details>

        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th scope="col">Bank</th>
                        <th scope="col">Nomor Rekening</th>
                        <th scope="col">Atas Nama</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bankAccounts as $account)
                        <tr>
                            <th scope="row">{{ $account->bank_name }}</th>
                            <td>{{ $account->account_number }}</td>
                            <td>{{ $account->account_holder ?: '—' }}</td>
                            <td>
                                <span class="badge {{ $account->is_active ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $account->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <a class="btn btn-sm btn-outline-primary"
                                   href="{{ route('settings.index', ['rekening' => $account->id]) }}#form-rekening">Edit</a>
                                <form class="d-inline" action="{{ route('settings.bank-accounts.status', $account) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $account->is_active ? 0 : 1 }}">
                                    <button type="submit" class="btn btn-sm {{ $account->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                        {{ $account->is_active ? 'Nonaktifkan' : 'Aktifkan kembali' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada rekening pembayaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
