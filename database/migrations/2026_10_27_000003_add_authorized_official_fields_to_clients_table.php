<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('authorized_official_name')->nullable()->after('address');
            $table->string('authorized_official_title')->nullable()->after('authorized_official_name');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'authorized_official_name',
                'authorized_official_title',
            ]);
        });
    }
};
