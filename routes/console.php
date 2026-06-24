<?php

use App\Models\Person;
use App\Models\Vehicle;
use App\Services\PreventechService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('enrich:data', function () {
    $preventechService = app(PreventechService::class);
    $vehiclesUpdated = 0;
    $peopleUpdated = 0;

    Vehicle::query()
        ->where(function ($query): void {
            $query->whereNull('type')
                ->orWhereNull('year')
                ->orWhereNull('total_km');
        })
        ->chunkById(100, function ($vehicles) use ($preventechService, &$vehiclesUpdated): void {
            foreach ($vehicles as $vehicle) {
                $mapped = $preventechService->mapVehicleData([
                    'name' => $vehicle->name,
                    'registration' => $vehicle->plate,
                    'type' => $vehicle->type,
                    'year' => $vehicle->year,
                    'status' => $vehicle->status,
                    'total_km' => $vehicle->total_km,
                    'avg_consumption' => $vehicle->average_consumption,
                ]);

                $vehicle->forceFill([
                    'name' => $vehicle->name ?: $mapped['name'],
                    'plate' => $vehicle->plate ?: $mapped['plate'],
                    'type' => $vehicle->type ?: $mapped['type'],
                    'year' => $vehicle->year ?: $mapped['year'],
                    'status' => $vehicle->status ?: $mapped['status'],
                    'total_km' => $vehicle->total_km ?? 0,
                    'average_consumption' => $vehicle->average_consumption,
                ])->save();

                $vehiclesUpdated++;
            }
        });

    Person::query()
        ->where(function ($query): void {
            $query->whereNull('number')
                ->orWhereNull('mechanographic_number')
                ->orWhereNull('email')
                ->orWhereNull('phone')
                ->orWhereNull('rank')
                ->orWhereNull('waist_cm')
                ->orWhereNull('chest_cm')
                ->orWhereNull('height_cm');
        })
        ->chunkById(100, function ($people) use ($preventechService, &$peopleUpdated): void {
            foreach ($people as $person) {
                $mapped = $preventechService->mapPersonData([
                    'name' => $person->name,
                    'number' => $person->mechanographic_number ?: $person->number,
                    'email' => $person->email,
                    'phone' => $person->phone,
                    'rank' => $person->rank,
                    'status' => $person->status,
                    'waist_cm' => $person->waist_cm,
                    'chest_cm' => $person->chest_cm,
                    'height_cm' => $person->height_cm,
                ]);

                $person->forceFill([
                    'number' => $person->number ?: $mapped['number'],
                    'mechanographic_number' => $person->mechanographic_number ?: $mapped['mechanographic_number'],
                    'email' => $person->email ?: $mapped['email'],
                    'phone' => $person->phone ?: $mapped['phone'],
                    'rank' => $person->rank ?: $mapped['rank'],
                    'status' => $person->status ?: $mapped['status'],
                    'waist_cm' => $person->waist_cm ?: $mapped['waist_cm'],
                    'chest_cm' => $person->chest_cm ?: $mapped['chest_cm'],
                    'height_cm' => $person->height_cm ?: $mapped['height_cm'],
                ])->save();

                $peopleUpdated++;
            }
        });

    $this->info("Dados enriquecidos: {$vehiclesUpdated} veiculo(s), {$peopleUpdated} pessoa(s).");
})->purpose('Enrich imported vehicles and people with missing operational data');
