<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('procurement_documents')
            ->where('status', 'draft')
            ->update(['status' => 'ready']);
    }

    public function down(): void
    {
        DB::table('procurement_documents')
            ->where('status', 'ready')
            ->update(['status' => 'draft']);
    }
};
