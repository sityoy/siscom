<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('phone_2', 20)
                ->nullable()
                ->after('phone');
            $table->string('phone_3', 20)
                ->nullable()
                ->after('phone_2');
        });

        Schema::create('invoice_whatsapp_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('phone', 20);
            $table->date('reminder_date');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('response')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->unique(
                ['invoice_id', 'phone', 'reminder_date'],
                'invoice_whatsapp_daily_unique'
            );
            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_whatsapp_reminders');

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'phone_2',
                'phone_3',
            ]);
        });
    }
};
