<section id="biaya-rekening" class="mt-4" aria-labelledby="biaya-rekening-title">
    <h5 id="biaya-rekening-title">Potongan Bank Bulanan</h5>
    <p class="text-muted small mb-3">Catat total biaya rutin per rekening dan bulan sesuai mutasi bank. Tanggal yang digunakan adalah tanggal potongan terakhir jika biaya rekening dan kartu terpisah.</p>

    @if($feeAccount)
        <form class="border rounded p-3 mb-3" method="POST"
              action="{{ route('settings.bank-accounts.fees.store', $feeAccount) }}">
            @csrf
            @if($editingFee)
                <input type="hidden" name="period" value="{{ $editingFee->period }}">
            @endif

            <h6 class="mb-3">
                {{ $editingFee ? 'Perbarui Potongan' : 'Catat Potongan' }} · {{ $feeAccount->bank_name }} · {{ $feeAccount->account_number }}
            </h6>

            @if($errors->bankFee->any())
                <ul class="alert alert-danger mb-3" role="alert">
                    @foreach($errors->bankFee->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="bank-debited-on">Tanggal Potongan</label>
                    <input type="date" class="form-control" id="bank-debited-on" name="debited_on"
                           max="{{ today()->toDateString() }}"
                           value="{{ old('debited_on', $editingFee?->debited_on->toDateString() ?? today()->toDateString()) }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="bank-debited-amount">Nominal Potongan (Rp)</label>
                    <input type="text" inputmode="numeric" class="form-control" id="bank-debited-amount" name="amount"
                           maxlength="17" value="{{ old('amount', $editingFee?->amount ?? $feeAccount->monthly_admin_fee) }}" required>
                </div>
            </div>

            <small class="form-text text-muted mb-3">Menyimpan rekening dan bulan yang sama akan memperbarui catatan bulan tersebut.</small>
            <button type="submit" class="btn btn-primary">Simpan Potongan</button>
            <a class="btn btn-outline-secondary" href="{{ route('settings.index') }}#biaya-rekening">Batal</a>
        </form>
    @endif

    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th scope="col">Rekening</th>
                    <th scope="col">Tanggal Potongan</th>
                    <th scope="col" class="text-right">Nominal</th>
                    <th scope="col">Status</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feeHistory as $fee)
                    <tr>
                        <td>{{ $fee->paymentBankAccount->bank_name }} · {{ $fee->paymentBankAccount->account_number }}</td>
                        <td>{{ $fee->debited_on->format('d/m/Y') }}</td>
                        <td class="text-right text-nowrap">Rp {{ number_format($fee->amount, 0, ',', '.') }}</td>
                        <td>{{ $fee->voided_at ? 'Dibatalkan' : 'Tercatat' }}</td>
                        <td class="text-nowrap">
                            @unless($fee->voided_at)
                                <a class="btn btn-sm btn-outline-primary"
                                   href="{{ route('settings.index', ['biaya' => $fee->id]) }}#biaya-rekening">Edit</a>
                                <form class="d-inline" method="POST"
                                      action="{{ route('settings.bank-accounts.fees.void', [$fee->paymentBankAccount, $fee]) }}"
                                      onsubmit="return confirm('Batalkan potongan bank ini dari rekap?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Batalkan</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">Belum ada potongan bank yang dicatat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $feeHistory->onEachSide(1)->links() }}
</section>
