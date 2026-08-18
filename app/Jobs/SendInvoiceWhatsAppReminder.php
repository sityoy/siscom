<?php

namespace App\Jobs;

use App\Models\InvoiceWhatsAppReminder;
use App\Services\InvoiceReminderMessageService;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Throwable;

class SendInvoiceWhatsAppReminder implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $reminderId
    ) {
    }

    public function backoff(): array
    {
        return [
            60,
            300,
        ];
    }

    public function handle(
        InvoiceReminderMessageService $messageService
    ): void {
        $reminder = InvoiceWhatsAppReminder::with([
            'invoice.client',
            'invoice.project',
        ])->find($this->reminderId);

        if (!$reminder) {
            return;
        }

        if ($reminder->status === 'sent') {
            $this->dispatchNextReminder($reminder);

            return;
        }

        if ($reminder->status === 'skipped') {
            return;
        }

        $invoice = $reminder->invoice;

        if (
            !$invoice ||
            !in_array($invoice->status, ['unpaid', 'partial'], true)
        ) {
            $reminder->update([
                'status' => 'skipped',
                'error' => 'Invoice sudah dibayar, dibatalkan, atau tidak tersedia.',
            ]);

            return;
        }

        try {
            $pdfUrl = URL::temporarySignedRoute(
                'invoice.whatsapp.pdf',
                now()->addDays(2),
                ['invoice' => $invoice->id]
            );

            $response = WhatsAppService::sendDocument(
                $reminder->phone,
                $messageService->build($invoice, $pdfUrl),
                $pdfUrl
            );
            $responseBody = $response->body();

            if (
                !$response->successful() ||
                $response->json('status') === false
            ) {
                throw new RuntimeException(
                    'Fonnte menolak pengiriman: ' . $responseBody
                );
            }

            $reminder->update([
                'status' => 'sent',
                'sent_at' => now(),
                'response' => $responseBody,
                'error' => null,
            ]);
        } catch (Throwable $exception) {
            $reminder->update([
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $this->dispatchNextReminder($reminder);
    }

    public function failed(Throwable $exception): void
    {
        InvoiceWhatsAppReminder::whereKey($this->reminderId)
            ->where('status', '!=', 'sent')
            ->update([
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ]);
    }

    private function dispatchNextReminder(
        InvoiceWhatsAppReminder $currentReminder
    ): void {
        $nextReminder = InvoiceWhatsAppReminder::query()
            ->where('invoice_id', $currentReminder->invoice_id)
            ->whereDate(
                'reminder_date',
                $currentReminder->reminder_date->toDateString()
            )
            ->where('id', '>', $currentReminder->id)
            ->where('status', 'pending')
            ->whereNull('scheduled_at')
            ->orderBy('id')
            ->first();

        if (!$nextReminder) {
            return;
        }

        $minimumDelay = max(
            1,
            (int) config('services.fonnte.minimum_delay_minutes', 5)
        );
        $maximumDelay = max(
            $minimumDelay,
            (int) config('services.fonnte.maximum_delay_minutes', 10)
        );
        $scheduledAt = now()->addMinutes(
            random_int($minimumDelay, $maximumDelay)
        );
        $updated = InvoiceWhatsAppReminder::query()
            ->whereKey($nextReminder->id)
            ->whereNull('scheduled_at')
            ->update([
                'scheduled_at' => $scheduledAt,
            ]);

        if ($updated === 1) {
            try {
                self::dispatch($nextReminder->id)
                    ->delay($scheduledAt);
            } catch (Throwable $exception) {
                InvoiceWhatsAppReminder::whereKey($nextReminder->id)
                    ->where('status', 'pending')
                    ->update([
                        'scheduled_at' => null,
                    ]);

                throw $exception;
            }
        }
    }
}
