<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('pp55_certificate_number', 100)
                ->nullable()
                ->after('pp55_case_number');
            $table->date('pp55_certificate_date')
                ->nullable()
                ->after('pp55_certificate_number');
            $table->string('pp55_certificate_document')
                ->nullable()
                ->after('pp55_certificate_date');
            $table->text('pp55_notes')
                ->nullable()
                ->after('pp55_certificate_document');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'pp55_certificate_number',
                'pp55_certificate_date',
                'pp55_certificate_document',
                'pp55_notes',
            ]);
        });
    }
};
