<?php

namespace App\Http\Controllers\Admin;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exports\PaymentsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Notification;

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
        $invoices = Invoice::all();

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
                $invoice->grand_total
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
        $grandTotal = $invoice->grand_total;

        // UPDATE STATUS OTOMATIS
        if ($totalPaid >= $grandTotal) {

            $invoice->update([

                'status' => 'paid'

            ]);

        } elseif ($totalPaid > 0) {

            $invoice->update([

                'status' => 'partial'

            ]);

        } else {

            $invoice->update([

                'status' => 'unpaid'

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
        $invoices = Invoice::all();

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
            $invoice->grand_total
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
        $grandTotal = $invoice->grand_total;

        // UPDATE STATUS
        if ($totalPaid >= $grandTotal) {

            $invoice->update([

                'status' => 'paid'

            ]);

        } elseif ($totalPaid > 0) {

            $invoice->update([

                'status' => 'partial'

            ]);

        } else {

            $invoice->update([

                'status' => 'unpaid'

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

        // HITUNG ULANG
        $totalPaid = $invoice->payments()
            ->sum('amount');

        // UPDATE STATUS
        if ($totalPaid >= $invoice->grand_total) {

            $invoice->update([
                'status' => 'paid'
            ]);

        } elseif ($totalPaid > 0) {

            $invoice->update([
                'status' => 'partial'
            ]);

        } else {

            $invoice->update([
                'status' => 'unpaid'
            ]);
        }

        return back()->with(
            'success',
            'Pembayaran berhasil dihapus'
        );
    }

    public function export()
    {
        return Excel::download(

            new PaymentsExport,

            'payments.xlsx'

        );
    }
}
