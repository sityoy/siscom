<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class WhatsAppService
{
    /**
     * KIRIM PESAN TEXT
     */
    public static function send(
        $phone,
        $message
    ) {

        return Http::withoutVerifying()

            ->withHeaders([

                'Authorization' => env('FONNTE_TOKEN')

            ])->post(

                'https://api.fonnte.com/send',

                [

                    'target' => $phone,

                    'message' => $message,

                ]

            );
    }

    /**
     * KIRIM DOCUMENT / PDF
     */
    public static function sendDocument(
        $phone,
        $message,
        $fileUrl
    ) {

        return Http::withoutVerifying()

            ->withHeaders([

                'Authorization' => env('FONNTE_TOKEN')

            ])->post(

                'https://api.fonnte.com/send',

                [

                    'target' => $phone,

                    'message' => $message,

                    'file' => $fileUrl,

                ]

            );
    }
}
