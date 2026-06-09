<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->string('external_id')->nullable()->unique()->after('id');
            $table->string('mechanographic_number')->nullable()->unique()->after('name');
        });

        DB::table('people')
            ->whereNotNull('number')
            ->whereNull('mechanographic_number')
            ->update(['mechanographic_number' => DB::raw('number')]);
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropUnique(['external_id']);
            $table->dropUnique(['mechanographic_number']);
            $table->dropColumn(['external_id', 'mechanographic_number']);
        });
    }
};
