<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\Invoice;
use Carbon\Carbon;

class InvoiceReminderMessageService
{
    public function build(
        Invoice $invoice,
        string $pdfUrl
    ): string {
        $invoice->loadMissing([
            'client',
            'project',
        ]);

        $setting = CompanySetting::first();

        $companyName = $setting?->company_name ?: 'SIS.COM';
        $senderName = 'Lena Septiana';
        $clientName = $invoice->client->company
            ?: $invoice->client->name;
        $projectName = $invoice->project?->title
            ?: 'Layanan SIS.COM';

        $timezone = config(
            'services.fonnte.reminder_timezone',
            'Asia/Jakarta'
        );
        $dueDate = Carbon::createFromFormat(
            'Y-m-d',
            $invoice->due_date->toDateString(),
            $timezone
        )
            ->startOfDay();
        $today = now($timezone)
            ->startOfDay();

        if ($invoice->status === 'paid') {
            $timeStatus = 'LUNAS';
        } elseif ($today->gt($dueDate)) {
            $daysLate = (int) $dueDate->diffInDays($today);
            $timeStatus = "TERLAMBAT {$daysLate} HARI";
        } elseif ($today->equalTo($dueDate)) {
            $timeStatus = 'JATUH TEMPO HARI INI';
        } else {
            $daysRemaining = (int) $today->diffInDays($dueDate);
            $timeStatus = "TERSISA {$daysRemaining} HARI";
        }

        $dueDateLabel = $dueDate
            ->locale('id')
            ->translatedFormat('d F Y');
        $statusLabel = strtoupper($invoice->status);
        $totalDue = number_format(
            $invoice->total_due,
            0,
            ',',
            '.'
        );

        $lateFeeLine = '';

        if ($invoice->late_fee_amount > 0) {
            $lateFee = number_format(
                $invoice->late_fee_amount,
                0,
                ',',
                '.'
            );

            $lateFeeLine = "\n💸 *Denda ({$invoice->late_months} bulan): Rp {$lateFee}*\n";
        }

        $bankAccounts = $setting?->activeBankAccounts
            ->map(function ($account, int $index) {
                $holder = filled($account->account_holder) ? " (a/n {$account->account_holder})" : '';

                return ($index + 1).". {$account->bank_name}: {$account->account_number}".$holder;
            })->implode("\n");

        if (blank($bankAccounts)) {
            $bankAccounts = 'Silakan hubungi admin untuk informasi rekening.';
        }

        return "⚠️ *PENGINGAT PEMBAYARAN " . strtoupper($projectName) . "* ⚠️

Halo Bapak/Ibu Pimpinan dan Admin *{$clientName}*,

Perkenalkan, saya *{$senderName}* dari *{$companyName}*. Kami mohon izin untuk menyampaikan tagihan layanan *{$projectName}* yang saat ini berstatus *{$statusLabel}*.

📝 *No. Invoice:* {$invoice->invoice_number}
⏳ *Batas Pembayaran:* {$dueDateLabel}
🚨 *Status Waktu:* *{$timeStatus}*
{$lateFeeLine}
💰 *TOTAL TAGIHAN: Rp {$totalDue}*

📄 *Lihat Rincian Invoice:* {$pdfUrl}

💳 *Metode Pembayaran:*
{$bankAccounts}

Mohon kesediaannya untuk segera menyelesaikan administrasi pembayaran dan mengirimkan bukti transfer kepada kami. Silakan abaikan pesan ini apabila Bapak/Ibu telah melakukan pembayaran.

Terima kasih atas kerja sama yang terjalin dengan baik,
*{$companyName} - Software House & IT Solutions*";
    }
}
