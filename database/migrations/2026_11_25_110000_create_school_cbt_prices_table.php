<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_cbt_prices', function (Blueprint $table) {
            $table->unsignedBigInteger('portal_school_id')->primary();
            $table->string('basis', 16);
            $table->unsignedBigInteger('amount');
            $table->unsignedInteger('units')->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamp('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_cbt_prices');
    }
};
