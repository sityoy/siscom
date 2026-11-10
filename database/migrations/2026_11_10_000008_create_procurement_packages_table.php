<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('package_type', 30)->default('direct')->index();
            $table->string('procurement_method', 30)->default('non_siplah');
            $table->string('status', 30)->default('preparation')->index();

            $table->string('vendor_name')->nullable();
            $table->string('vendor_tax_id', 50)->nullable();
            $table->string('vendor_tax_status', 30)->nullable();
            $table->string('vendor_contact', 100)->nullable();
            $table->text('vendor_address')->nullable();

            $table->decimal('contract_value', 15, 2)->default(0);
            $table->decimal('vendor_cost', 15, 2)->default(0);
            $table->decimal('vendor_tax_amount', 15, 2)->default(0);
            $table->decimal('operational_cost', 15, 2)->default(0);
            $table->string('partner_name')->nullable();
            $table->decimal('partner_fee', 15, 2)->default(0);
            $table->decimal('target_fee_percent', 5, 2)->default(10);
            $table->decimal('management_fee_amount', 15, 2)->default(0);

            $table->decimal('income_tax_rate', 5, 2)->default(0.50);
            $table->decimal('income_tax_amount', 15, 2)->default(0);
            $table->string('tax_collection_method', 30)->default('withheld');
            $table->decimal('net_profit', 15, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_packages');
    }
};
