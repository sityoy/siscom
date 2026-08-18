<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppService
{
    /**
     * KIRIM PESAN TEXT
     */
    public static function send(
        $phone,
        $message
    ) {
        self::ensureConfigured();

        return Http::withoutVerifying()

            ->timeout(30)

            ->withHeaders([

                'Authorization' => config('services.fonnte.token')

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
        self::ensureConfigured();

        return Http::withoutVerifying()

            ->timeout(30)

            ->withHeaders([

                'Authorization' => config('services.fonnte.token')

            ])->post(

                'https://api.fonnte.com/send',

                [

                    'target' => $phone,

                    'message' => $message,

                    'file' => $fileUrl,

                ]

            );
    }

    private static function ensureConfigured(): void
    {
        if (blank(config('services.fonnte.token'))) {
            throw new RuntimeException(
                'FONNTE_TOKEN belum diatur pada file .env.'
            );
        }
    }
}
