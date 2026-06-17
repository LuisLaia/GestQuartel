<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->unique();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('driver_name')->nullable();
            $table->decimal('distance_km', 10, 2)->default(0);
            $table->decimal('fuel_used_liters', 10, 2)->default(0);
            $table->decimal('consumption', 8, 2)->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
