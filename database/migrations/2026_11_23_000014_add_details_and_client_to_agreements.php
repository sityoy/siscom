<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->json('details')->nullable();
        });

        DB::table('agreements')->select('id', 'invoice_id', 'client_name')->whereNotNull('invoice_id')
            ->chunkById(200, function ($agreements): void {
                $clients = DB::table('invoices')->join('clients', 'clients.id', '=', 'invoices.client_id')
                    ->whereIn('invoices.id', $agreements->pluck('invoice_id'))
                    ->get(['invoices.id', 'invoices.client_id', 'clients.name'])->keyBy('id');

                foreach ($agreements as $agreement) {
                    $source = $clients->get($agreement->invoice_id);
                    if ($source && $source->name === $agreement->client_name) {
                        DB::table('agreements')->where('id', $agreement->id)->update(['client_id' => $source->client_id]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
            $table->dropColumn('details');
        });
    }
};
