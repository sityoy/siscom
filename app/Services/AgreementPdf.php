<?php

namespace App\Services;

use App\Models\Agreement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class AgreementPdf
{
    public function download(Agreement $agreement): Response
    {
        $filename = $agreement->number
            ? 'MoU-'.str_replace('/', '-', $agreement->number).'.pdf'
            : 'Draf-MoU-'.$agreement->id.'.pdf';

        return Pdf::loadView('admin.agreements.pdf', [
            'agreement' => $agreement,
            'company' => (object) $agreement->letterhead,
            'logoPath' => public_path('logo/siscombg2.png'),
        ])->setPaper('a4')->download($filename);
    }
}
