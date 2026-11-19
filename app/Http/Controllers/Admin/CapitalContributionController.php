<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CapitalContribution;
use App\Models\Invoice;
use App\Models\Payment;
use App\Support\Pagination;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CapitalContributionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'invoice_id' => ['nullable', 'integer', 'exists:invoices,id'],
        ]);
        $month = $filters['month'] ?? null;
        $invoiceId = $filters['invoice_id'] ?? null;
        $today = today()->toDateString();
        $start = $month ? Carbon::createFromFormat('!Y-m', $month) : null;

        $withinPeriod = static function (Builder $query, string $column) use ($today, $start): Builder {
            $query->where($column, '<=', $today);

            if ($start) {
                $query->whereBetween($column, [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()]);
            }

            return $query;
        };
        $capitalInPeriod = fn (Builder $query) => $withinPeriod($query, 'contribution_date')->whereNull('voided_at');
        $paymentsInPeriod = fn (Builder $query) => $withinPeriod($query, 'payment_date');

        $contributions = $withinPeriod(CapitalContribution::query(), 'contribution_date');
        $payments = $paymentsInPeriod(Payment::query());

        if ($invoiceId) {
            $contributions->where('invoice_id', $invoiceId);
            $payments->where('invoice_id', $invoiceId);
        }

        $capitalTotal = $this->money((clone $contributions)->whereNull('voided_at')->sum('amount'));
        $paymentsTotal = $this->money($payments->sum('amount'));
        $cashInTotal = $capitalTotal->plus($paymentsTotal);

        $invoiceBalances = $this->invoiceQuery()
            ->when($invoiceId, fn (Builder $query) => $query->whereKey($invoiceId))
            ->withSum(['capitalContributions as recorded_capital' => $capitalInPeriod], 'amount')
            ->withSum(['payments as recorded_payments' => $paymentsInPeriod], 'amount')
            ->paginate(10, pageName: 'invoice_page');

        $invoiceBalances->through(function (Invoice $invoice): array {
            $capital = $this->money($invoice->recorded_capital);
            $payments = $this->money($invoice->recorded_payments);

            return [
                'invoice' => $invoice,
                'capital' => (string) $capital,
                'payments' => (string) $payments,
                'difference' => (string) $payments->minus($capital),
            ];
        });

        return view('admin.capital.index', [
            'month' => $month,
            'invoiceId' => $invoiceId,
            'invoices' => $this->invoiceQuery()->get(),
            'invoiceBalances' => $invoiceBalances,
            'capitalTotal' => (string) $capitalTotal,
            'paymentsTotal' => (string) $paymentsTotal,
            'cashInTotal' => (string) $cashInTotal,
            'contributions' => $contributions->with('invoice:id,invoice_number')->latest('contribution_date')->orderByDesc('id')
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

        return view('admin.capital.edit', [
            'contribution' => $contribution,
            'invoices' => $this->invoiceQuery()->get(),
        ]);
    }

    public function update(Request $request, CapitalContribution $contribution): RedirectResponse
    {
        $data = $this->validatedContribution($request, $contribution);
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

    private function invoiceQuery(): Builder
    {
        return Invoice::query()
            ->select(['id', 'client_id', 'invoice_number', 'grand_total'])
            ->with('client:id,name')
            ->orderByDesc('id');
    }

    private function money(mixed $amount): BigDecimal
    {
        return BigDecimal::of((string) ($amount ?? 0))->toScale(2, RoundingMode::HalfUp);
    }

    private function validatedContribution(Request $request, ?CapitalContribution $contribution = null): array
    {
        $amount = $request->input('amount');

        if (is_string($amount)) {
            $amount = trim($amount);

            if (preg_match('/\A[0-9]{1,3}(?:\.[0-9]{3})+(?:,[0-9]{1,2})?\z/', $amount)) {
                $amount = str_replace('.', '', $amount);
            }

            $request->merge(['amount' => str_replace(',', '.', $amount)]);
        }

        $data = $request->validate([
            'contribution_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.today()->toDateString()],
            'source' => ['required', 'string', 'max:120'],
            'amount' => ['bail', 'required', 'regex:/\A[0-9]{1,13}(?:\.[0-9]{1,2})?\z/', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'invoice_id' => ['nullable', 'integer', 'exists:invoices,id'],
        ], [
            'amount.regex' => 'Isi nominal dengan angka, misalnya 1.500.000,50 atau 1500000.50.',
            'amount.min' => 'Nominal setoran minimal Rp 0,01.',
            'contribution_date.before_or_equal' => 'Tanggal setoran maksimal hari ini.',
        ], [
            'contribution_date' => 'tanggal setoran',
            'source' => 'sumber setoran',
            'amount' => 'nominal setoran',
            'notes' => 'catatan',
            'invoice_id' => 'invoice',
        ]);

        if (! array_key_exists('invoice_id', $data)) {
            return $data;
        }

        // Preserve the original reference when the linked invoice has been deleted.
        $data['invoice_reference'] = $data['invoice_id']
            ? Invoice::query()->findOrFail($data['invoice_id'], ['invoice_number'])->invoice_number
            : ($contribution?->invoice_id === null ? $contribution?->invoice_reference : null);

        return $data;
    }
}
