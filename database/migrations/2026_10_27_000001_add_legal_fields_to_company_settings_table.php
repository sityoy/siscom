<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('legal_name')->nullable()->after('company_name');
            $table->string('nib', 50)->nullable()->after('company_address');
            $table->string('npwp', 50)->nullable()->after('nib');
            $table->string('director_name')->nullable()->after('npwp');
            $table->string('director_title')->nullable()->after('director_name');
            $table->string('city')->nullable()->after('director_title');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'legal_name',
                'nib',
                'npwp',
                'director_name',
                'director_title',
                'city',
            ]);
        });
    }
};
