<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CompanySetting;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;

    public function __construct(
        Invoice $invoice
    )
    {
        $this->invoice = $invoice;
    }

    public function build()
    {
        $this->invoice->loadMissing(['client.user', 'project', 'items']);
        $company = CompanySetting::first();

        $pdf = Pdf::loadView(

            'admin.invoices.pdf',

            [
                'invoice' => $this->invoice,
                'company' => $company,
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
