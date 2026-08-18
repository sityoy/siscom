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
use App\Models\Client;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with([
            'invoice.client',
            'invoice.payments'
        ])

            ->latest()
            ->paginate(10);

        return view(
            'admin.payments.index',
            compact('payments')
        );
    }

    public function create()
    {
        $invoices = Invoice::with([
            'client',
            'payments',
        ])->get();

        return view(
            'admin.payments.create',
            compact('invoices')
        );
    }

    public function store(Request $request)
    {
        $request->validate([

            'invoice_id' => 'required',

            'amount' => 'required|numeric',

            'payment_date' => 'required',

            'proof' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

        ]);


        $invoice = Invoice::findOrFail(
        $request->invoice_id
            );

            $totalPaid =
                $invoice->payments()
                    ->sum('amount');

            $remaining =
                $invoice->calculateTotalDue($request->payment_date)
                - $totalPaid;

            if($request->amount > $remaining){

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Nominal melebihi sisa tagihan'
                    );

            }

            $proof = null;

            if($request->hasFile('proof')){

                $proof = $request->file('proof')
                    ->store(
                        'payments',
                        'public'
                    );

            }

        Payment::create([

            'invoice_id' => $request->invoice_id,

            'amount' => $request->amount,

            'payment_date' => $request->payment_date,

            'payment_method' => $request->payment_method,

            'proof' => $proof,

            'notes' => $request->notes,

        ]);


        // AMBIL INVOICE
        $invoice = Invoice::findOrFail(
            $request->invoice_id
        );

        Notification::create([

            'client_id' => $invoice->client_id,

            'title' => 'Pembayaran Baru',

            'message' =>
                'Pembayaran invoice ' .
                $invoice->invoice_number .
                ' sebesar Rp ' .
                number_format(
                    $request->amount,
                    0,
                    ',',
                    '.'
                ),

        ]);

        // TOTAL SUDAH DIBAYAR
        $totalPaid = $invoice->payments()
            ->sum('amount');

        // GRAND TOTAL
        $grandTotal = $invoice->calculateTotalDue(
            $request->payment_date
        );

        // UPDATE STATUS OTOMATIS
        if ($totalPaid >= $grandTotal) {

            $invoice->update([
                'status' => 'paid',
                'paid_at' => $request->payment_date,
            ]);

            $this->processRenewalInvoice($invoice);

        } elseif ($totalPaid > 0) {

            $invoice->update([

                'status' => 'partial',
                'paid_at' => null,

            ]);

        } else {

            $invoice->update([

                'status' => 'unpaid',
                'paid_at' => null,

            ]);
        }

        return redirect()
            ->route('payments.index')
            ->with(
                'success',
                'Pembayaran berhasil dibuat'
            );
    }

    public function edit(Payment $payment)
    {
        $invoices = Invoice::with([
            'client',
            'payments',
        ])->get();

        return view(
            'admin.payments.edit',
            compact(
                'payment',
                'invoices'
            )
        );
    }

    public function update(
        Request $request,
        Payment $payment
    ) {

        $request->validate([

            'invoice_id' => 'required',

            'amount' => 'required|numeric',

            'payment_date' => 'required',

        ]);

        $invoice = Invoice::findOrFail(
        $request->invoice_id
        );

        $totalPaid =
            $invoice->payments()
                ->where(
                    'id',
                    '!=',
                    $payment->id
                )
                ->sum('amount');

        $remaining =
            $invoice->calculateTotalDue($request->payment_date)
            - $totalPaid;

        if($request->amount > $remaining){

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Nominal melebihi sisa tagihan'
                );

        }

        $proof = $payment->proof;

        if($request->hasFile('proof')){

            $proof = $request->file('proof')
                ->store(
                    'payments',
                    'public'
                );
        }

        $payment->update([

            'invoice_id' => $request->invoice_id,

            'amount' => $request->amount,

            'payment_date' => $request->payment_date,

            'payment_method' => $request->payment_method,

            'proof' => $proof,

            'notes' => $request->notes,

        ]);

        // AMBIL INVOICE
        $invoice = Invoice::findOrFail(
            $request->invoice_id
        );

        // TOTAL PEMBAYARAN
        $totalPaid = $invoice->payments()
            ->sum('amount');

        // GRAND TOTAL
        $grandTotal = $invoice->calculateTotalDue(
            $request->payment_date
        );

        // UPDATE STATUS
        if ($totalPaid >= $grandTotal) {

            $invoice->update([
                'status' => 'paid',
                'paid_at' => $request->payment_date,
            ]);

            $this->processRenewalInvoice($invoice);

        } elseif ($totalPaid > 0) {

            $invoice->update([

                'status' => 'partial',
                'paid_at' => null,

            ]);

        } else {

            $invoice->update([

                'status' => 'unpaid',
                'paid_at' => null,

            ]);
        }

        return redirect()
            ->route('payments.index')
            ->with(
                'success',
                'Pembayaran berhasil diupdate'
            );
    }

    public function destroy(Payment $payment)
    {
        $invoice = $payment->invoice;

        $payment->delete();

        $invoice->update([
            'paid_at' => null,
        ]);

        $invoice->refresh();

        // HITUNG ULANG
        $totalPaid = $invoice->payments()
            ->sum('amount');

        // UPDATE STATUS
        if ($totalPaid >= $invoice->total_due) {

            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

        } elseif ($totalPaid > 0) {

            $invoice->update([
                'status' => 'partial',
                'paid_at' => null,
            ]);

        } else {

            $invoice->update([
                'status' => 'unpaid',
                'paid_at' => null,
            ]);
        }

        return back()->with(
            'success',
            'Pembayaran berhasil dihapus'
        );
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
