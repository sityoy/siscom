<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('tax_status', 30)->default('non_pkp')->after('npwp');
            $table->string('income_tax_scheme', 30)->default('umkm_final')->after('tax_status');
            $table->decimal('income_tax_rate', 5, 2)->default(0.50)->after('income_tax_scheme');
            $table->boolean('pp55_active')->default(true)->after('income_tax_rate');
            $table->string('pp55_bpe_number', 100)->nullable()->after('pp55_active');
            $table->string('pp55_case_number', 100)->nullable()->after('pp55_bpe_number');
            $table->date('pp55_effective_date')->nullable()->after('pp55_case_number');
            $table->string('pp55_document')->nullable()->after('pp55_effective_date');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('provider_tax_status', 30)->default('non_pkp')->after('vat');
            $table->string('income_tax_scheme', 30)->default('umkm_final')->after('provider_tax_status');
            $table->decimal('income_tax_rate', 5, 2)->default(0.50)->after('income_tax_scheme');
            $table->decimal('income_tax_base', 15, 2)->default(0)->after('income_tax_rate');
            $table->boolean('income_tax_withheld')->default(false)->after('income_tax_base');
            $table->decimal('income_tax_amount', 15, 2)->default(0)->after('income_tax_withheld');
        });

        DB::table('company_settings')->update([
            'tax_status' => 'non_pkp',
            'income_tax_scheme' => 'umkm_final',
            'income_tax_rate' => 0.50,
            'pp55_active' => true,
        ]);

        DB::table('invoices')->update([
            'provider_tax_status' => 'non_pkp',
            'income_tax_scheme' => 'umkm_final',
            'income_tax_rate' => 0.50,
            'income_tax_base' => DB::raw('subtotal + service_fee'),
            'income_tax_withheld' => false,
            'income_tax_amount' => 0,
        ]);
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'provider_tax_status',
                'income_tax_scheme',
                'income_tax_rate',
                'income_tax_base',
                'income_tax_withheld',
                'income_tax_amount',
            ]);
        });

        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'tax_status',
                'income_tax_scheme',
                'income_tax_rate',
                'pp55_active',
                'pp55_bpe_number',
                'pp55_case_number',
                'pp55_effective_date',
                'pp55_document',
            ]);
        });
    }
};
