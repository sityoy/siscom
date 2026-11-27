<?php

namespace App\Http\Controllers\Admin;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exports\PaymentsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with([
            'invoice' => fn ($query) => $query->with('client')->withSum('payments as amount_paid', 'amount'),
        ])

            ->latest()
            ->paginate(\App\Support\Pagination::perPage());

        return view(
            'admin.payments.index',
            compact('payments')
        );
    }

    public function create(Request $request)
    {
        $invoices = $this->availableInvoices();
        $selectedInvoice = $request->integer('invoice_id');

        if ($selectedInvoice && ! $invoices->contains('id', $selectedInvoice)) {
            $invoice = Invoice::withSum('payments as amount_paid', 'amount')->find($selectedInvoice);
            $reason = $invoice?->paymentBlockReason($invoice->recordedPaymentsTotal()) ?? 'Invoice tidak tersedia.';

            return to_route('payments.create')->with('warning', $reason);
        }

        return view('admin.payments.create', compact('invoices', 'selectedInvoice'));
    }

    public function edit(Payment $payment)
    {
        return view('admin.payments.create', [
            'invoices' => $this->availableInvoices($payment),
            'payment' => $payment,
        ]);
    }

    private function availableInvoices(?Payment $payment = null): Collection
    {
        return Invoice::with(['client', 'project'])
            ->withSum('payments as amount_paid', 'amount')
            ->withExists('payments')
            ->where('status', '!=', 'cancelled')
            ->where(function (Builder $query) use ($payment) {
                $query->whereIn('status', Invoice::PAYABLE_STATUSES);
                if ($payment) {
                    $query->orWhere('id', $payment->invoice_id);
                } else {
                    $query->orWhere(fn (Builder $paid) => $paid->where('status', 'paid')
                        ->whereNull('project_id')
                        ->whereIn('invoice_type', Invoice::MANUAL_PAYMENT_TYPES)
                        ->doesntHave('payments'));
                }
            })
            ->when($payment?->recorded_settlement, fn (Builder $query) => $query->whereKey($payment->invoice_id))
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (Invoice $invoice) => $invoice->id == $payment?->invoice_id
                || $invoice->canReceivePayment((float) $invoice->amount_paid)
                || (! $payment && $invoice->canRecordUnloggedSettlement()));
    }

    public function store(Request $request)
    {
        return $this->savePayment($request);
    }

    public function update(Request $request, Payment $payment)
    {
        return $this->savePayment($request, $payment);
    }

    private function savePayment(Request $request, ?Payment $payment = null)
    {
        $request->merge(['amount' => $this->normalizeCurrencyInput($request->input('amount'))]);
        $data = $request->validate([
            'invoice_id' => 'required|integer|exists:invoices,id',
            'amount' => 'required|numeric|decimal:0,2|min:0.01',
            'payment_date' => 'required|date',
            'proof' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
            'confirm_received' => 'sometimes|accepted',
        ]);

        $confirmed = $request->boolean('confirm_received');
        unset($data['proof'], $data['confirm_received']);
        $newProof = null;
        $oldProof = null;
        try {
            DB::transaction(function () use ($request, $data, $payment, $confirmed, &$newProof, &$oldProof) {
                if ($payment) {
                    $payment = Payment::lockForUpdate()->findOrFail($payment->id);
                }

                $invoices = Invoice::whereKey(array_filter([$data['invoice_id'], $payment?->invoice_id]))
                    ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                $invoice = $invoices->get($data['invoice_id']);
                abort_unless($invoice, 404);
                if ($invoice->status === 'cancelled') {
                    throw ValidationException::withMessages(['invoice_id' => 'Invoice ini sudah dibatalkan.']);
                }

                $sameInvoice = $payment && $payment->invoice_id == $invoice->id;
                if ($payment?->recorded_settlement && (! $sameInvoice || $invoice->status !== 'paid')) {
                    throw ValidationException::withMessages(['invoice_id' => 'Pelunasan yang tercatat harus tetap pada invoice lunas asalnya.']);
                }

                $paidByOthers = (float) $invoice->payments()
                    ->when($sameInvoice, fn ($query) => $query->where('id', '!=', $payment->id))
                    ->sum('amount');

                $recordSettlement = ! $payment && $invoice->canRecordUnloggedSettlement();
                if (! $sameInvoice && ! $recordSettlement && ! $invoice->canReceivePayment($paidByOthers)) {
                    throw ValidationException::withMessages(['invoice_id' => 'Invoice ini sudah lunas atau tidak memiliki sisa tagihan.']);
                }

                if ($recordSettlement && ! $confirmed) {
                    throw ValidationException::withMessages(['confirm_received' => 'Konfirmasi bahwa pembayaran invoice sudah diterima.']);
                }

                if ($confirmed && ! $recordSettlement) {
                    throw ValidationException::withMessages(['invoice_id' => 'Status pencatatan berubah. Pilih ulang invoice sebelum menyimpan.']);
                }

                $isSettlement = $recordSettlement || $payment?->recorded_settlement;
                $balanceDate = $isSettlement ? ($invoice->paid_at ?? $data['payment_date']) : $data['payment_date'];
                $remaining = $invoice->outstandingBalance($paidByOthers, $balanceDate);
                $amount = (float) $data['amount'];
                if ($isSettlement) {
                    // The issued invoice shows whole rupiah; the receipt records the exact amount received.
                    if ($amount !== $remaining && $amount !== round($remaining)) {
                        throw ValidationException::withMessages(['amount' => 'Nominal harus sesuai tagihan bersih pada invoice.']);
                    }
                } elseif ($amount > $remaining) {
                    throw ValidationException::withMessages(['amount' => 'Nominal melebihi sisa tagihan.']);
                }

                if ($request->hasFile('proof')) {
                    $oldProof = $payment?->proof;
                    $newProof = $request->file('proof')->store('payments', 'public');
                    if (! $newProof) {
                        throw ValidationException::withMessages(['proof' => 'Bukti pembayaran gagal disimpan.']);
                    }
                    $data['proof'] = $newProof;
                }

                if ($payment) {
                    $previousInvoice = $invoices->get($payment->invoice_id);
                    $payment->update($data);
                    if (! $sameInvoice) {
                        $this->refreshPaymentStatus($previousInvoice, today(), false);
                    }
                } else {
                    Payment::create($data + ['recorded_settlement' => $recordSettlement]);
                    Notification::create([
                        'client_id' => $invoice->client_id,
                        'title' => $recordSettlement ? 'Pelunasan Dicatat' : 'Pembayaran Baru',
                        'message' => 'Pembayaran invoice '.$invoice->invoice_number.' sebesar Rp '
                            .number_format($data['amount'], 0, ',', '.'),
                    ]);
                }

                if (! $isSettlement) {
                    $this->refreshPaymentStatus($invoice, $data['payment_date']);
                } elseif ($invoice->paid_at === null) {
                    $invoice->update(['paid_at' => $data['payment_date']]);
                }
            });
        } catch (\Throwable $exception) {
            if ($newProof) {
                Storage::disk('public')->delete($newProof);
            }
            throw $exception;
        }

        if ($oldProof) {
            Storage::disk('public')->delete($oldProof);
        }

        return redirect()->route('payments.index')->with('success',
            $payment ? 'Pembayaran berhasil diupdate' : 'Pembayaran berhasil dibuat'
        );
    }

    public function destroy(Payment $payment)
    {
        $proof = DB::transaction(function () use ($payment) {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);
            $invoice = Invoice::lockForUpdate()->findOrFail($payment->invoice_id);
            $payment->delete();
            if (! $payment->recorded_settlement) {
                $this->refreshPaymentStatus($invoice, today(), false);
            }

            return $payment->proof;
        });

        if ($proof) {
            Storage::disk('public')->delete($proof);
        }

        return back()->with('success', 'Pembayaran berhasil dihapus');
    }

    private function refreshPaymentStatus(Invoice $invoice, $date, bool $applyRenewal = true): void
    {
        if ($invoice->status === 'cancelled') {
            return;
        }

        $paid = (float) $invoice->payments()->sum('amount');
        $settled = $invoice->outstandingBalance($paid, $date) === 0.0;
        $wasPaid = $invoice->status === 'paid';
        $invoice->update([
            'status' => $settled ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
            'paid_at' => $settled ? $date : null,
        ]);

        if ($applyRenewal && $settled && ! $wasPaid) {
            $this->processRenewalInvoice($invoice);
        }
    }

    private function normalizeCurrencyInput(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = preg_replace('/[^0-9,.-]/', '', trim($value));

        if ($value === '' || $value === null) {
            return $value;
        }

        $lastComma = strrpos($value, ',');
        $lastDot = strrpos($value, '.');

        if ($lastComma !== false && $lastDot !== false) {
            return $lastComma > $lastDot
                ? str_replace(',', '.', str_replace('.', '', $value))
                : str_replace(',', '', $value);
        }

        if ($lastComma !== false) {
            return str_replace(',', '.', str_replace('.', '', $value));
        }

        if ($lastDot !== false) {
            $dotCount = substr_count($value, '.');
            $decimalDigits = strlen($value) - $lastDot - 1;

            if ($dotCount > 1 || $decimalDigits === 3) {
                return str_replace('.', '', $value);
            }
        }

        return $value;
    }

    private function processRenewalInvoice($invoice)
    {
        if ($invoice->invoice_type != 'renewal') {
            return;
        }

        $client = $invoice->client;

        if (!$client) {
            return;
        }

        $item = $invoice->items()->first();

        if (!$item) {
            return;
        }

        $duration = $item->duration ?? 12;

        $durationType = $item->duration_type ?? 'month';

        $currentEnd = $client->subscription_end
            ? Carbon::parse($client->subscription_end)
            : now();

        if ($currentEnd->lt(now())) {
            $currentEnd = now();
        }

        switch ($durationType) {

            case 'Hari':

                $newEnd = $currentEnd
                    ->copy()
                    ->addDays($duration);

                break;

            case 'Tahun':

                $newEnd = $currentEnd
                    ->copy()
                    ->addYears($duration);

                break;

            default:

                $newEnd = $currentEnd
                    ->copy()
                    ->addMonths($duration);

                break;
        }

        $client->update([

            'subscription_start' => now(),

            'subscription_end' => $newEnd,

        ]);
    }

    public function export()
    {
        return Excel::download(

            new PaymentsExport,

            'payments.xlsx'

        );
    }
}
