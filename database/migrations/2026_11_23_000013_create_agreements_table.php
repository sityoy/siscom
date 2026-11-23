<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('invoice_reference');
            $table->date('invoice_date')->nullable();
            $table->decimal('invoice_total', 15, 2);
            $table->string('provider_name');
            $table->json('letterhead');
            $table->string('client_name');
            $table->string('title');
            $table->date('document_date');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('city', 100);
            $table->text('scope');
            $table->text('payment_terms');
            $table->text('additional_terms')->nullable();
            $table->string('provider_signer', 150);
            $table->string('provider_title', 150);
            $table->string('client_signer', 150);
            $table->string('client_title', 150);
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreements');
    }
};
