<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table): void {
            $table->integer('waist_cm')->nullable()->after('joined_at');
            $table->integer('chest_cm')->nullable()->after('waist_cm');
            $table->integer('height_cm')->nullable()->after('chest_cm');
            $table->integer('leg_cm')->nullable()->after('height_cm');
            $table->integer('arm_cm')->nullable()->after('leg_cm');
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table): void {
            $table->dropColumn([
                'waist_cm',
                'chest_cm',
                'height_cm',
                'leg_cm',
                'arm_cm',
            ]);
        });
    }
};
