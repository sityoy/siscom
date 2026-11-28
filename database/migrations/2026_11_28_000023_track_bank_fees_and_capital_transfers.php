<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_bank_accounts', function (Blueprint $table) {
            $table->string('product_label', 120)->nullable();
            $table->unsignedBigInteger('monthly_admin_fee')->nullable();
            $table->unsignedBigInteger('minimum_balance')->nullable();
        });

        Schema::create('bank_administration_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_bank_account_id')->constrained()->cascadeOnDelete();
            $table->char('period', 7);
            $table->date('debited_on')->index();
            $table->unsignedBigInteger('amount');
            $table->timestamp('voided_at')->nullable();
            $table->timestamps();
            $table->unique(['payment_bank_account_id', 'period'], 'bank_admin_fee_account_period_unique');
        });

        Schema::table('capital_contributions', function (Blueprint $table) {
            $table->string('recipient', 160)->nullable();
            $table->date('transferred_on')->nullable();
            $table->string('transfer_reference', 120)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('capital_contributions', function (Blueprint $table) {
            $table->dropColumn(['recipient', 'transferred_on', 'transfer_reference']);
        });

        Schema::dropIfExists('bank_administration_fees');

        Schema::table('payment_bank_accounts', function (Blueprint $table) {
            $table->dropColumn(['product_label', 'monthly_admin_fee', 'minimum_balance']);
        });
    }
};
