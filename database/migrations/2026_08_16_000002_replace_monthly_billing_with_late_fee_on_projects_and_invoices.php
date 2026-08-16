<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn(
                'monthly_billing_active',
                'late_fee_active'
            );
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn(
                'monthly_fee',
                'late_fee_per_month'
            );
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('monthly_billing_start');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('late_fee_active')
                ->default(false)
                ->after('service_fee');
            $table->decimal('late_fee_per_month', 15, 2)
                ->default(0)
                ->after('late_fee_active');
            $table->timestamp('paid_at')
                ->nullable()
                ->after('due_date');
        });

        DB::table('projects')
            ->whereNull('late_fee_per_month')
            ->update(['late_fee_per_month' => 100000]);

        DB::table('projects')
            ->update(['late_fee_active' => true]);

        DB::table('projects')
            ->select([
                'id',
                'late_fee_active',
                'late_fee_per_month',
            ])
            ->orderBy('id')
            ->each(function ($project) {
                DB::table('invoices')
                    ->where('project_id', $project->id)
                    ->update([
                        'late_fee_active' => $project->late_fee_active,
                        'late_fee_per_month' =>
                            $project->late_fee_per_month ?? 0,
                    ]);
            });

        DB::table('invoices')
            ->where('invoice_type', 'renewal')
            ->whereNotNull('project_id')
            ->update(['invoice_type' => 'project']);

        DB::table('invoices')
            ->where('status', 'paid')
            ->whereNull('paid_at')
            ->update(['paid_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'late_fee_active',
                'late_fee_per_month',
                'paid_at',
            ]);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->date('monthly_billing_start')
                ->nullable()
                ->after('late_fee_per_month');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn(
                'late_fee_per_month',
                'monthly_fee'
            );
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn(
                'late_fee_active',
                'monthly_billing_active'
            );
        });
    }
};
