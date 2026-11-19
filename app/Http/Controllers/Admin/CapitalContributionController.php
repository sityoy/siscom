<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CapitalContribution;
use App\Models\Payment;
use App\Support\Pagination;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CapitalContributionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['month' => ['nullable', 'date_format:Y-m']]);
        $month = $filters['month'] ?? null;
        $today = today()->toDateString();
        $contributions = CapitalContribution::where('contribution_date', '<=', $today);
        $payments = Payment::where('payment_date', '<=', $today);

        if ($month) {
            $start = Carbon::createFromFormat('!Y-m', $month);
            $dates = [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()];
            $contributions->whereBetween('contribution_date', $dates);
            $payments->whereBetween('payment_date', $dates);
        }

        $capitalTotal = BigDecimal::of((string) (clone $contributions)->whereNull('voided_at')->sum('amount'))
            ->toScale(2, RoundingMode::HalfUp);
        $paymentsTotal = BigDecimal::of((string) $payments->sum('amount'))->toScale(2, RoundingMode::HalfUp);
        $cashInTotal = $capitalTotal->plus($paymentsTotal);

        return view('admin.capital.index', [
            'month' => $month,
            'capitalTotal' => (string) $capitalTotal,
            'paymentsTotal' => (string) $paymentsTotal,
            'cashInTotal' => (string) $cashInTotal,
            'contributions' => $contributions->latest('contribution_date')->orderByDesc('id')
                ->paginate(Pagination::perPage()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        CapitalContribution::create($this->validatedContribution($request) + [
            'created_by' => $request->user()->id,
        ]);

        return to_route('capital.index')->with('success', 'Setoran modal berhasil dicatat.');
    }

    public function edit(CapitalContribution $contribution): View
    {
        abort_if($contribution->voided_at, 404);

        return view('admin.capital.edit', compact('contribution'));
    }

    public function update(Request $request, CapitalContribution $contribution): RedirectResponse
    {
        $data = $this->validatedContribution($request);
        $activeContribution = CapitalContribution::whereKey($contribution->id)->whereNull('voided_at');

        if (! $activeContribution->update($data)) {
            abort_unless($activeContribution->exists(), 404);
        }

        return to_route('capital.index')->with('success', 'Setoran modal berhasil diperbarui.');
    }

    public function destroy(Request $request, CapitalContribution $contribution): RedirectResponse
    {
        CapitalContribution::whereKey($contribution->id)->whereNull('voided_at')->update([
            'voided_at' => now(),
            'voided_by' => $request->user()->id,
        ]);

        return to_route('capital.index')->with('success', 'Setoran modal dibatalkan dan tersimpan dalam riwayat.');
    }

    private function validatedContribution(Request $request): array
    {
        $amount = $request->input('amount');

        if (is_string($amount)) {
            $amount = trim($amount);

            if (preg_match('/\A[0-9]{1,3}(?:\.[0-9]{3})+(?:,[0-9]{1,2})?\z/', $amount)) {
                $amount = str_replace('.', '', $amount);
            }

            $request->merge(['amount' => str_replace(',', '.', $amount)]);
        }

        return $request->validate([
            'contribution_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.today()->toDateString()],
            'source' => ['required', 'string', 'max:120'],
            'amount' => ['bail', 'required', 'regex:/\A[0-9]{1,13}(?:\.[0-9]{1,2})?\z/', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'amount.regex' => 'Isi nominal dengan angka, misalnya 1.500.000,50 atau 1500000.50.',
            'amount.min' => 'Nominal setoran minimal Rp 0,01.',
            'contribution_date.before_or_equal' => 'Tanggal setoran maksimal hari ini.',
        ], [
            'contribution_date' => 'tanggal setoran',
            'source' => 'sumber setoran',
            'amount' => 'nominal setoran',
            'notes' => 'catatan',
        ]);
    }
}
