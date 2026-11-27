<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\PaymentBankAccount;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    private function authorizeAccount(PaymentBankAccount $account): void
    {
        abort_unless($account->company_setting_id == CompanySetting::first()?->id, 404);
    }

    private function save(Request $request, PaymentBankAccount $account): void
    {
        $data = $request->validateWithBag('bankAccount', [
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:255'],
            'account_holder' => ['required', 'string', 'max:255'],
        ], [], [
            'bank_name' => 'nama bank',
            'account_number' => 'nomor rekening',
            'account_holder' => 'nama pemilik rekening',
        ]);

        try {
            $account->fill($data)->save();
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['account_number' => 'Rekening ini sudah terdaftar pada bank tersebut.'])
                ->errorBag('bankAccount');
        }
    }

    private function backToAccounts(string $message): RedirectResponse
    {
        return redirect(route('settings.index').'#rekening-pembayaran')->with('success', $message);
    }
}
