<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uniform_assignments', function (Blueprint $table): void {
            $table->string('suggested_size')->nullable()->after('quantity');
            $table->string('chosen_size')->nullable()->after('suggested_size');
        });
    }

    public function down(): void
    {
        Schema::table('uniform_assignments', function (Blueprint $table): void {
            $table->dropColumn(['suggested_size', 'chosen_size']);
        });
    }
};
