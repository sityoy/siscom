<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('salary_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('recipient', 100);
            $table->string('mode', 10);
            $table->decimal('value', 15, 2);
            $table->unique(['invoice_id', 'recipient']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_allocations');
    }
};
