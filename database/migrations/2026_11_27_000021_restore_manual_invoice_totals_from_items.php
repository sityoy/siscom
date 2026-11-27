<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $itemTotal = DB::raw('(SELECT SUM(invoice_items.total) FROM invoice_items WHERE invoice_items.invoice_id = invoices.id)');

        DB::table('invoices')
            ->whereNull('project_id')
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('grand_total', 0)
            ->where('vat_percent', 0)
            ->where('vat', 0)
            ->where('service_fee', 0)
            ->where('income_tax_withheld', false)
            ->where($itemTotal, '>', 0)
            ->where(fn ($query) => $query->where('subtotal', 0)->orWhere('subtotal', '=', $itemTotal))
            ->where(fn ($query) => $query->where('amount', 0)->orWhere('amount', '=', $itemTotal))
            ->update(['subtotal' => $itemTotal, 'grand_total' => $itemTotal]);
    }

    public function down(): void
    {
        // Once restored, invoice totals can be edited and payments can be recorded against them.
    }
};
