<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uniform_assignments', function (Blueprint $table): void {
            $table->string('size')->nullable()->after('quantity');
        });

        DB::table('uniform_assignments')
            ->whereNull('size')
            ->orderBy('id')
            ->get()
            ->each(function ($assignment): void {
                $legacySize = DB::table('uniform_items')
                    ->where('id', $assignment->uniform_item_id)
                    ->value('size');

                DB::table('uniform_assignments')
                    ->where('id', $assignment->id)
                    ->update([
                        'size' => $assignment->chosen_size ?: $assignment->suggested_size ?: $legacySize,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('uniform_assignments', function (Blueprint $table): void {
            $table->dropColumn('size');
        });
    }
};
