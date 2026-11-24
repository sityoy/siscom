<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->string('official_number', 48)->nullable()->unique();
        });

        Schema::create('agreement_number_sequences', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->unsignedBigInteger('last_number');
        });

        DB::table('agreements')->select('id', 'created_at', 'finalized_at')->whereNotNull('finalized_at')
            ->chunkById(200, function ($agreements): void {
                foreach ($agreements as $agreement) {
                    $year = Carbon::parse($agreement->created_at ?? $agreement->finalized_at)->year;
                    DB::table('agreements')->where('id', $agreement->id)->update([
                        'official_number' => sprintf('%04d/MOU/SIS/%04d', $agreement->id, $year),
                    ]);
                }
            });

        DB::table('agreement_number_sequences')->insert(['id' => 1, 'last_number' => $this->lastLegacyNumber()]);
    }

    private function lastLegacyNumber(): int
    {
        $table = DB::getTablePrefix().'agreements';
        $highestCurrentId = (int) DB::table('agreements')->max('id');
        $lastId = match (DB::getDriverName()) {
            'mysql', 'mariadb' => (int) (DB::selectOne('SHOW TABLE STATUS WHERE Name = ?', [$table])->Auto_increment ?? 1) - 1,
            'sqlite' => (int) DB::table('sqlite_sequence')->where('name', $table)->value('seq'),
            default => $highestCurrentId,
        };
        $lastNumber = max($lastId, $highestCurrentId);

        // Release only the trailing IDs we can prove still belong to drafts.
        // A missing legacy ID may have belonged to a final document since deleted.
        foreach (DB::table('agreements')->select('id', 'finalized_at')->lazyByIdDesc(200) as $agreement) {
            if ((int) $agreement->id !== $lastNumber || $agreement->finalized_at !== null) {
                break;
            }

            $lastNumber--;
        }

        return $lastNumber;
    }

    public function down(): void
    {
        Schema::dropIfExists('agreement_number_sequences');

        Schema::table('agreements', function (Blueprint $table) {
            $table->dropUnique(['official_number']);
            $table->dropColumn('official_number');
        });
    }
};
