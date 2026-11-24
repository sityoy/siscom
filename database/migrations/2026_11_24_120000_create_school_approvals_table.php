<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_approvals', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('portal_school_id')->unique();
            $table->string('school_code', 32);
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('client_label');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('until_date');
            $table->string('modules');
            $table->timestamp('approved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_approvals');
    }
};
