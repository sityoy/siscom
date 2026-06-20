<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {

            $table->string('package_name')
                ->nullable()
                ->after('address');

            $table->decimal('package_price', 15, 2)
                ->nullable()
                ->after('package_name');

            $table->date('subscription_start')
                ->nullable()
                ->after('package_price');

            $table->date('subscription_end')
                ->nullable()
                ->after('subscription_start');

            $table->integer('grace_period_days')
                ->default(7)
                ->after('subscription_end');

        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {

            $table->dropColumn([
                'package_name',
                'package_price',
                'subscription_start',
                'subscription_end',
                'grace_period_days'
            ]);

        });
    }
};