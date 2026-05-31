<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;

    public function __construct($invoice)
    {
        $this->invoice = $invoice;
    }

    public function build()
    {
        $pdf = Pdf::loadView(

            'admin.invoices.pdf',

            [
                'invoice' => $this->invoice
            ]

        );

        return $this->subject(
                'Invoice Baru - SIS.COM'
            )
            ->view('emails.invoice')

            ->attachData(

                $pdf->output(),

                'invoice-' .
                $this->invoice->invoice_number .
                '.pdf'

            );
    }
}
