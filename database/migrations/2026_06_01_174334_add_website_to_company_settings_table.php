<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    if (!Schema::hasColumn('company_settings', 'website')) {

        Schema::table('company_settings', function (Blueprint $table) {

            $table->string('website')
                  ->nullable()
                  ->after('company_address');

        });

    }
}

    /**
     * Reverse the migrations.
     */
public function down(): void
{
    if (Schema::hasColumn('company_settings', 'website')) {

        Schema::table('company_settings', function (Blueprint $table) {

            $table->dropColumn('website');

        });

    }
}
};
