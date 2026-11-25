<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('salary_allocations', fn (Blueprint $table) => $table->date('allocated_on')->nullable()->index());
    }

    public function down(): void
    {
        Schema::table('salary_allocations', function (Blueprint $table) {
            $table->dropIndex(['allocated_on']);
            $table->dropColumn('allocated_on');
        });
    }
};
