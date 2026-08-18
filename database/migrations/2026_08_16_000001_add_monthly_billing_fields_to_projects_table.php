<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('monthly_billing_active')
                ->default(false)
                ->after('budget');
            $table->decimal('monthly_fee', 15, 2)
                ->nullable()
                ->after('monthly_billing_active');
            $table->date('monthly_billing_start')
                ->nullable()
                ->after('monthly_fee');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'monthly_billing_active',
                'monthly_fee',
                'monthly_billing_start',
            ]);
        });
    }
};
