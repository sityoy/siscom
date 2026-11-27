<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('company_signature_mode', 16)->default('blank');
            $table->string('verification_code', 40)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['verification_code']);
            $table->dropColumn(['company_signature_mode', 'verification_code']);
        });
    }
};
