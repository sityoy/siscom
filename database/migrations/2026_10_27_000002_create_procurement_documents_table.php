<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 50)->index();
            $table->string('document_number')->unique();
            $table->date('document_date');
            $table->string('status', 30)->default('draft')->index();
            $table->string('title')->nullable();
            $table->string('subject')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('work_start_date')->nullable();
            $table->date('work_end_date')->nullable();
            $table->string('school_signer_name')->nullable();
            $table->string('school_signer_title')->nullable();
            $table->string('provider_signer_name')->nullable();
            $table->string('provider_signer_title')->nullable();
            $table->string('funding_source', 50)->nullable();
            $table->string('transaction_method', 30)->nullable();
            $table->string('transaction_type', 30)->nullable();
            $table->string('provider_tax_status', 30)->nullable();
            $table->decimal('tax_base', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('income_tax_amount', 15, 2)->default(0);
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->string('signed_file_path')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_documents');
    }
};
