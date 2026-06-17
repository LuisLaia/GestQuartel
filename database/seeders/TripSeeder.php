<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TripSeeder extends Seeder
{
    public function run(): void
    {
        $drivers = [
            'João Silva',
            'Afonso Martins',
            'Carlos Mendes',
            'Rui Ferreira',
            'Pedro Costa',
        ];

        $vehicles = [
            ['name' => 'ABSC 01', 'type' => 'Ambulância'],
            ['name' => 'VCIF 12', 'type' => 'Incêndio Florestal'],
            ['name' => 'VLCI 02', 'type' => 'Combate Incêndios'],
            ['name' => 'VUCI 03', 'type' => 'Comando'],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::firstOrCreate(
                ['name' => $vehicle['name']],
                [
                    'plate' => $this->randomPlate(),
                    'type' => $vehicle['type'],
                    'year' => random_int(2005, 2022),
                    'status' => 'operacional',
                    'total_km' => 0,
                ]
            );
        }

        foreach ($drivers as $driver) {
            $this->ensureDriverExists($driver);
        }

        $fallbackVehicle = Vehicle::inRandomOrder()->first();
        $vehicleIds = Vehicle::pluck('id');

        if ($fallbackVehicle && $vehicleIds->isNotEmpty()) {
            Trip::whereNotIn('vehicle_id', $vehicleIds)->update([
                'vehicle_id' => $fallbackVehicle->id,
            ]);
        }

        Trip::query()
            ->whereNotNull('driver_name')
            ->where('driver_name', '!=', '')
            ->pluck('driver_name')
            ->unique()
            ->each(fn (string $driverName) => $this->ensureDriverExists($driverName));

        $trips = [
            ['João Silva', 'ABSC 01', 120, 8.0, 1, '08:15', 95],
            ['Afonso Martins', 'VCIF 12', 46, 2.8, 1, '11:30', 45],
            ['Carlos Mendes', 'VLCI 02', 210, 16.2, 2, '07:45', 150],
            ['Rui Ferreira', 'VUCI 03', 82, 6.7, 2, '14:10', 70],
            ['Pedro Costa', 'ABSC 01', 64, 4.0, 3, '09:20', 55],
            ['Afonso Martins', 'VCIF 12', 178, 11.0, 3, '16:00', 130],
            ['Carlos Mendes', 'VLCI 02', 35, 2.9, 4, '10:15', 40],
            ['Rui Ferreira', 'VUCI 03', 260, 24.1, 4, '06:40', 190],
            ['João Silva', 'ABSC 01', 95, 6.1, 5, '13:05', 80],
            ['Afonso Martins', 'VCIF 12', 28, 1.6, 5, '18:25', 35],
            ['Carlos Mendes', 'VLCI 02', 140, 10.6, 6, '08:50', 115],
            ['Pedro Costa', 'VUCI 03', 72, 5.9, 6, '15:35', 65],
            ['João Silva', 'ABSC 01', 188, 12.4, 7, '07:10', 145],
            ['Afonso Martins', 'VCIF 12', 54, 3.1, 7, '12:45', 50],
            ['Carlos Mendes', 'VLCI 02', 298, 27.8, 8, '06:20', 215],
            ['Rui Ferreira', 'VUCI 03', 118, 9.8, 8, '17:15', 95],
            ['Pedro Costa', 'ABSC 01', 42, 2.5, 9, '10:40', 40],
            ['Afonso Martins', 'VCIF 12', 226, 14.5, 9, '05:55', 165],
            ['Carlos Mendes', 'VLCI 02', 76, 6.2, 10, '14:50', 65],
            ['Rui Ferreira', 'VUCI 03', 154, 13.7, 10, '09:05', 120],
        ];

        foreach ($trips as $index => [$driverName, $vehicleName, $distanceKm, $fuelUsedLiters, $daysAgo, $startTime, $durationMinutes]) {
            $this->ensureDriverExists($driverName);

            $vehicle = Vehicle::where('name', $vehicleName)->first()
                ?? Vehicle::inRandomOrder()->first();

            if (! $vehicle) {
                $vehicle = Vehicle::create([
                    'name' => 'Viatura '.($index + 1),
                    'plate' => $this->randomPlate(),
                    'type' => 'Operacional',
                    'year' => random_int(2005, 2022),
                    'status' => 'operacional',
                    'total_km' => 0,
                ]);
            }

            $startedAt = now()->subDays($daysAgo)->setTimeFromTimeString($startTime);
            $endedAt = (clone $startedAt)->addMinutes($durationMinutes);
            $consumption = round(($fuelUsedLiters / $distanceKm) * 100, 2);

            Trip::updateOrCreate(
                ['external_id' => 'demo-trip-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)],
                [
                    'vehicle_id' => $vehicle->id,
                    'driver_name' => $driverName,
                    'distance_km' => $distanceKm,
                    'fuel_used_liters' => $fuelUsedLiters,
                    'consumption' => $consumption,
                    'started_at' => $startedAt,
                    'ended_at' => $endedAt,
                ]
            );
        }

        Vehicle::query()->each(function (Vehicle $vehicle): void {
            $vehicle->total_km = Trip::where('vehicle_id', $vehicle->id)
                ->where('distance_km', '>', 0)
                ->sum('distance_km');
            $vehicle->average_consumption = Trip::where('vehicle_id', $vehicle->id)
                ->where('distance_km', '>', 0)
                ->whereNotNull('consumption')
                ->avg('consumption');
            $vehicle->save();
        });
    }

    private function randomPlate(): string
    {
        return strtoupper(Str::random(2)).'-'.random_int(10, 99).'-'.strtoupper(Str::random(2));
    }

    private function ensureDriverExists(string $driverName): Person
    {
        return Person::firstOrCreate(
            ['name' => $driverName],
            [
                'number' => $this->uniquePersonnelNumber(),
                'rank' => 'Bombeiro',
                'email' => Str::slug(Str::ascii($driverName), '.').'@example.test',
                'phone' => '91'.random_int(1000000, 9999999),
                'status' => 'ativo',
                'joined_at' => now()->subMonths(random_int(6, 48)),
            ]
        );
    }

    private function uniquePersonnelNumber(): string
    {
        do {
            $number = 'B'.random_int(100, 999);
        } while (Person::where('number', $number)->exists());

        return $number;
    }
}
