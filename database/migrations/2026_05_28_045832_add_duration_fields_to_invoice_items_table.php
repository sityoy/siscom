<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {

            $table->integer('duration')
                ->nullable();

            $table->string('duration_type')
                ->nullable();

            $table->date('start_date')
                ->nullable();

            $table->date('end_date')
                ->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {

            $table->dropColumn([

                'duration',

                'duration_type',

                'start_date',

                'end_date',

            ]);

        });
    }
};
