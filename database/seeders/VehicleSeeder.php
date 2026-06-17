<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->vehicles() as $vehicle) {
            Vehicle::updateOrCreate(
                ['plate' => $vehicle['plate']],
                $vehicle
            );
        }
    }

    private function vehicles(): array
    {
        return [
            [
                'plate' => '00-AA-00',
                'name' => 'ABSC 01',
                'type' => 'Ambulancia',
                'year' => 2018,
                'status' => 'operacional',
            ],
            [
                'plate' => '11-BB-11',
                'name' => 'VLCI 02',
                'type' => 'Combate Incendios',
                'year' => 2020,
                'status' => 'operacional',
            ],
            [
                'plate' => '22-CC-22',
                'name' => 'VUCI 03',
                'type' => 'Comando',
                'year' => 2016,
                'status' => 'operacional',
            ],
        ];
    }
}
