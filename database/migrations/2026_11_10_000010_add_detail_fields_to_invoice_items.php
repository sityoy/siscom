<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('item_type', 30)->default('service')->after('description');
            $table->text('specification')->nullable()->after('item_type');
            $table->string('unit', 30)->default('Paket')->after('qty');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['item_type', 'specification', 'unit']);
        });
    }
};
