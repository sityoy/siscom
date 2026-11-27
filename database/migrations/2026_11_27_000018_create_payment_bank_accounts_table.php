<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_bank_accounts')) {
            Schema::create('payment_bank_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_setting_id')->constrained()->cascadeOnDelete();
                $table->string('bank_name', 100);
                $table->string('account_number');
                $table->string('account_holder')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['company_setting_id', 'bank_name', 'account_number'], 'payment_bank_account_unique');
            });
        }

        $columns = ['bank_jakarta' => 'Bank Jakarta', 'bank_mandiri' => 'Bank Mandiri', 'bank_bca' => 'Bank BCA'];
        $now = now();

        foreach (DB::table('company_settings')->lazyById() as $setting) {
            $accounts = [];
            foreach ($columns as $column => $bankName) {
                $number = trim((string) $setting->{$column});
                if ($number !== '') {
                    $accounts[] = [
                        'company_setting_id' => $setting->id,
                        'bank_name' => $bankName,
                        'account_number' => $number,
                        'account_holder' => trim((string) $setting->{$column.'_name'}) ?: null,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if ($accounts !== []) {
                DB::table('payment_bank_accounts')->insertOrIgnore($accounts);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_bank_accounts');
    }
};
