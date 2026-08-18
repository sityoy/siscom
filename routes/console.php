<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\InvoiceReminderScheduler;
use App\Models\Invoice;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('invoices:send-reminders {--invoice=}', function () {
    $scheduler = app(InvoiceReminderScheduler::class);
    $invoiceId = $this->option('invoice');

    $count = $invoiceId
        ? $scheduler->scheduleInvoice(
            Invoice::findOrFail($invoiceId)
        )
        : $scheduler->scheduleDaily();

    $this->info(
        "{$count} penerima pengingat WhatsApp berhasil dijadwalkan."
    );
})->purpose('Jadwalkan pengingat WhatsApp untuk invoice jatuh tempo');

Schedule::command('invoices:send-reminders')
    ->dailyAt(config('services.fonnte.reminder_time', '09:00'))
    ->timezone(config(
        'services.fonnte.reminder_timezone',
        'Asia/Jakarta'
    ))
    ->withoutOverlapping(30);
