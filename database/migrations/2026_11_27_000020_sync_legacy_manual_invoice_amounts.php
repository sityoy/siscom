<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('invoices')
            ->whereNull('project_id')
            ->where('amount', '>', 0)
            ->where('subtotal', 0)
            ->where('grand_total', 0)
            ->where('vat', 0)
            ->where('service_fee', 0)
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('invoice_items')
                    ->whereColumn('invoice_items.invoice_id', 'invoices.id');
            })
            ->update([
                'subtotal' => DB::raw('amount'),
                'grand_total' => DB::raw('amount'),
            ]);
    }

    public function down(): void
    {
        // The original amount is retained and repaired invoices may have since been edited.
    }
};
