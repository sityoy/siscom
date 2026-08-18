<?php

namespace App\Services;

use App\Jobs\SendInvoiceWhatsAppReminder;
use App\Models\Invoice;
use App\Models\InvoiceWhatsAppReminder;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class InvoiceReminderScheduler
{
    public function scheduleDaily(
        ?CarbonInterface $reminderDate = null
    ): int {
        $timezone = config(
            'services.fonnte.reminder_timezone',
            'Asia/Jakarta'
        );
        $reminderDate ??= Carbon::today($timezone);
        $scheduledCount = 0;

        Invoice::query()
            ->with([
                'client',
                'project',
            ])
            ->whereIn('status', [
                'unpaid',
                'partial',
            ])
            ->whereDate(
                'due_date',
                '<=',
                $reminderDate->toDateString()
            )
            ->chunkById(100, function ($invoices) use (
                &$scheduledCount,
                $reminderDate
            ) {
                foreach ($invoices as $invoice) {
                    $scheduledCount += $this->scheduleInvoice(
                        $invoice,
                        $reminderDate
                    );
                }
            });

        return $scheduledCount;
    }

    public function scheduleInvoice(
        Invoice $invoice,
        ?CarbonInterface $reminderDate = null
    ): int {
        $timezone = config(
            'services.fonnte.reminder_timezone',
            'Asia/Jakarta'
        );
        $reminderDate ??= Carbon::today($timezone);
        $invoice->loadMissing('client');

        if (!$invoice->client) {
            return 0;
        }

        $phones = $invoice->client->whatsapp_numbers;
        $baseTime = Carbon::now($timezone);
        $scheduledCount = 0;
        $firstReminderId = null;

        foreach ($phones as $index => $phone) {
            $reminder = InvoiceWhatsAppReminder::firstOrCreate(
                [
                    'invoice_id' => $invoice->id,
                    'phone' => $phone,
                    'reminder_date' => $reminderDate->toDateString(),
                ],
                [
                    'scheduled_at' => $index === 0
                        ? $baseTime
                        : null,
                    'status' => 'pending',
                ]
            );

            if (!$reminder->wasRecentlyCreated) {
                continue;
            }

            $scheduledCount++;

            if ($index === 0) {
                $firstReminderId = $reminder->id;
            }
        }

        if ($firstReminderId) {
            SendInvoiceWhatsAppReminder::dispatch($firstReminderId)
                ->delay($baseTime);
        }

        return $scheduledCount;
    }
}
