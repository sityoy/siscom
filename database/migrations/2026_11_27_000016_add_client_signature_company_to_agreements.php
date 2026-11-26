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
            $table->string('client_signature_company')->nullable();
        });

        $finalized = DB::table('agreements')
            ->leftJoin('clients', 'clients.id', '=', 'agreements.client_id')
            ->whereNotNull('agreements.finalized_at')
            ->select('agreements.id', 'agreements.client_name', 'clients.company');

        foreach ($finalized->lazyById(200, 'agreements.id', 'id') as $agreement) {
            DB::table('agreements')->where('id', $agreement->id)->update([
                'client_signature_company' => filled($agreement->company) ? $agreement->company : $agreement->client_name,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->dropColumn('client_signature_company');
        });
    }
};
