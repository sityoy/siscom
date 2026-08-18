<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {

            $table->id();

            $table->string('company_name');

            $table->string('company_email')
                ->nullable();

            $table->string('company_phone')
                ->nullable();

            $table->text('company_address')
                ->nullable();

            $table->string('bank_jakarta')
                ->nullable();

            $table->string('bank_jakarta_name')
                ->nullable();

            $table->string('bank_mandiri')
                ->nullable();

            $table->string('bank_mandiri_name')
                ->nullable();

            $table->string('bank_bca')
                ->nullable();

            $table->string('bank_bca_name')
                ->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
