<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table): void {
            $table->index('driver_name', 'trips_driver_name_index');
            $table->index('vehicle_id', 'trips_vehicle_id_index');
            $table->index('created_at', 'trips_created_at_index');
        });

        Schema::table('vehicles', function (Blueprint $table): void {
            $table->index('plate', 'vehicles_plate_index');
        });

        Schema::table('uniform_assignments', function (Blueprint $table): void {
            $table->index('person_id', 'uniform_assignments_person_id_index');
            $table->index('uniform_item_id', 'uniform_assignments_uniform_item_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('uniform_assignments', function (Blueprint $table): void {
            $table->dropIndex('uniform_assignments_uniform_item_id_index');
            $table->dropIndex('uniform_assignments_person_id_index');
        });

        Schema::table('vehicles', function (Blueprint $table): void {
            $table->dropIndex('vehicles_plate_index');
        });

        Schema::table('trips', function (Blueprint $table): void {
            $table->dropIndex('trips_created_at_index');
            $table->dropIndex('trips_vehicle_id_index');
            $table->dropIndex('trips_driver_name_index');
        });
    }
};
