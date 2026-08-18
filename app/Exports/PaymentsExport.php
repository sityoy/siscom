<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaymentsExport implements
    FromCollection,
    WithHeadings,
    WithMapping
{
    public function collection()
    {
        return Payment::with('invoice')->get();
    }

    public function headings(): array
    {
        return [

            'Invoice',

            'Nominal',

            'Tanggal Pembayaran',

            'Metode Pembayaran',

            'Catatan',

        ];
    }

    public function map($payment): array
    {
        return [

            $payment->invoice?->invoice_number,

            'Rp ' . number_format(
                $payment->amount,
                0,
                ',',
                '.'
            ),

            $payment->payment_date,

            $payment->payment_method,

            $payment->notes,

        ];
    }
}
