<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {

            $table->decimal(
                'subtotal',
                15,
                2
            )->default(0);

            $table->decimal(
                'vat',
                15,
                2
            )->default(0);

            $table->decimal(
                'service_fee',
                15,
                2
            )->default(0);

            $table->decimal(
                'grand_total',
                15,
                2
            )->default(0);

        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {

            $table->dropColumn([

                'subtotal',

                'vat',

                'service_fee',

                'grand_total',

            ]);

        });
    }
};
