<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\PaymentBankAccount;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentBankAccountController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $setting = CompanySetting::firstOrCreate([], ['company_name' => 'SIS.COM']);
        $account = $setting->bankAccounts()->make(['is_active' => true]);
        $this->save($request, $account);

        return $this->backToAccounts('Rekening berhasil ditambahkan.');
    }

    public function update(Request $request, PaymentBankAccount $bankAccount): RedirectResponse
    {
        $this->authorizeAccount($bankAccount);
        $this->save($request, $bankAccount);

        return $this->backToAccounts('Rekening berhasil diperbarui.');
    }

    public function setStatus(Request $request, PaymentBankAccount $bankAccount): RedirectResponse
    {
        $this->authorizeAccount($bankAccount);
        $data = $request->validateWithBag('bankAccount', ['is_active' => ['required', 'boolean']]);
        $bankAccount->update($data);

        return $this->backToAccounts($bankAccount->is_active ? 'Rekening diaktifkan.' : 'Rekening dinonaktifkan.');
    }

    public function storeFee(Request $request, PaymentBankAccount $bankAccount): RedirectResponse
    {
        $this->authorizeAccount($bankAccount);
        $this->normalizeRupiah($request, 'amount');
        $data = $request->validateWithBag('bankFee', [
            'debited_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.today()->toDateString()],
            'amount' => ['required', 'integer', 'min:1', 'max:9999999999999'],
            'period' => ['nullable', 'date_format:Y-m'],
        ], [], [
            'debited_on' => 'tanggal potongan',
            'amount' => 'nominal potongan',
        ]);
        $period = substr($data['debited_on'], 0, 7);
        if (isset($data['period']) && $data['period'] !== $period) {
            throw ValidationException::withMessages(['debited_on' => 'Tanggal potongan harus berada pada bulan catatan ini.'])
                ->errorBag('bankFee');
        }

        $bankAccount->administrationFees()->upsert([
            'period' => $period,
            'debited_on' => $data['debited_on'],
            'amount' => $data['amount'],
            'voided_at' => null,
        ], ['payment_bank_account_id', 'period'], ['debited_on', 'amount', 'voided_at']);

        return redirect(route('settings.index').'#biaya-rekening')->with('success', 'Biaya bank bulan tersebut berhasil disimpan.');
    }

    public function voidFee(PaymentBankAccount $bankAccount, int $fee): RedirectResponse
    {
        $this->authorizeAccount($bankAccount);

        DB::transaction(function () use ($bankAccount, $fee): void {
            $entry = $bankAccount->administrationFees()->lockForUpdate()->findOrFail($fee);
            if ($entry->voided_at === null) {
                $entry->update(['voided_at' => now()]);
            }
        }, 3);

        return redirect(route('settings.index').'#biaya-rekening')->with('success', 'Catatan potongan bank dibatalkan.');
    }

    private function authorizeAccount(PaymentBankAccount $account): void
    {
        abort_unless($account->company_setting_id == CompanySetting::first()?->id, 404);
    }

    private function save(Request $request, PaymentBankAccount $account): void
    {
        $this->normalizeRupiah($request, 'monthly_admin_fee', 'minimum_balance');
        $data = $request->validateWithBag('bankAccount', [
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:255'],
            'account_holder' => ['required', 'string', 'max:255'],
            'product_label' => ['nullable', 'string', 'max:120'],
            'monthly_admin_fee' => ['nullable', 'integer', 'min:0', 'max:9999999999999'],
            'minimum_balance' => ['nullable', 'integer', 'min:0', 'max:9999999999999'],
        ], [], [
            'bank_name' => 'nama bank',
            'account_number' => 'nomor rekening',
            'account_holder' => 'nama pemilik rekening',
            'product_label' => 'produk dan kartu',
            'monthly_admin_fee' => 'biaya bank bulanan',
            'minimum_balance' => 'saldo minimum',
        ]);

        try {
            $account->fill($data)->save();
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['account_number' => 'Rekening ini sudah terdaftar pada bank tersebut.'])
                ->errorBag('bankAccount');
        }
    }

    private function normalizeRupiah(Request $request, string ...$keys): void
    {
        foreach ($request->only($keys) as $key => $input) {
            if (is_string($input) && preg_match('/\A[0-9]{1,3}(?:\.[0-9]{3})+\z/', $input)) {
                $request->merge([$key => str_replace('.', '', $input)]);
            }
        }
    }

    private function backToAccounts(string $message): RedirectResponse
    {
        return redirect(route('settings.index').'#rekening-pembayaran')->with('success', $message);
    }
}
