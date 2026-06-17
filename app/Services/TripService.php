<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class TripService
{
    public function __construct(
        private readonly CartrackService $cartrackService,
        private readonly VehicleService $vehicleService,
    ) {}

    public function search(?string $search = null): Collection
    {
        return Trip::with('vehicle')
            ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('driver_name', 'like', "%{$search}%")
                    ->orWhereHas('vehicle', function ($query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('plate', 'like', "%{$search}%");
                    });
            }))
            ->latest('started_at')
            ->latest()
            ->get();
    }

    public function create(array $data): Trip
    {
        $this->ensureValidMetrics((float) $data['distance_km'], (float) $data['fuel_used_liters']);

        $trip = Trip::create($data);
        $trip->vehicle?->updateMetrics();

        return $trip;
    }

    public function sync(string $startDate, string $endDate): array
    {
        $result = $this->cartrackService->getTrips($startDate, $endDate);

        if ($result['error']) {
            return ['synced' => 0, 'error' => $result['error']];
        }

        $synced = 0;
        $skipped = 0;

        foreach ($result['data'] as $item) {
            $vehicle = $this->resolveOrCreateVehicle($item);

            if (! $vehicle) {
                $skipped++;
                continue;
            }

            $distance = $this->floatValue(data_get($item, 'distance_km'));
            $fuel = $this->floatValue(data_get($item, 'fuel_used_liters'));

            if ($fuel <= 0) {
                $fuel = $this->fuelUsedFor($item, $startDate, $endDate);
            }

            if ($distance <= 0 || $fuel <= 0) {
                Log::warning('Trip skipped due to invalid metrics', [
                    'external_id' => data_get($item, 'external_id'),
                    'distance_km' => $distance,
                    'fuel_used_liters' => $fuel,
                ]);
                $skipped++;
                continue;
            }

            $trip = Trip::updateOrCreate(
                ['external_id' => $this->tripExternalId($item)],
                [
                    'vehicle_id' => $vehicle->id,
                    'driver_name' => $this->stringValue(data_get($item, 'driver_name')),
                    'distance_km' => $distance,
                    'fuel_used_liters' => $fuel,
                    'started_at' => data_get($item, 'started_at'),
                    'ended_at' => data_get($item, 'ended_at'),
                ]
            );

            $this->vehicleService->refreshMetrics($trip->vehicle);
            $synced++;
        }

        Log::info('Trips synced', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'synced' => $synced,
            'skipped' => $skipped,
        ]);

        return ['synced' => $synced, 'error' => null];
    }

    public function calculateConsumption(float $fuel, float $distance): ?float
    {
        if ($distance <= 0 || $fuel <= 0) {
            Log::warning('Invalid trip consumption calculation', [
                'fuel' => $fuel,
                'distance' => $distance,
            ]);

            return null;
        }

        return round(($fuel / $distance) * 100, 2);
    }

    private function resolveOrCreateVehicle(array $item): ?Vehicle
    {
        $vehicleId = data_get($item, 'vehicle_id');

        if (is_numeric($vehicleId) && $vehicle = Vehicle::find((int) $vehicleId)) {
            return $vehicle;
        }

        $plate = $this->stringValue(data_get($item, 'registration') ?? data_get($item, 'plate'));
        $name = $this->stringValue(data_get($item, 'vehicle_name') ?? data_get($item, 'vehicle'));

        if (! $plate && ! $name) {
            return null;
        }

        $vehicle = Vehicle::query()
            ->when($plate, fn ($query) => $query->orWhere('plate', $plate))
            ->when($name, fn ($query) => $query->orWhere('name', $name))
            ->first();

        if ($vehicle) {
            return $vehicle;
        }

        if (! $name) {
            return null;
        }

        return Vehicle::firstOrCreate(
            ['name' => $name],
            [
                'plate' => $plate ?: $this->randomPlate(),
                'type' => 'Operacional',
                'status' => 'operacional',
                'total_km' => 0,
            ]
        );
    }

    private function fuelUsedFor(array $item, string $startDate, string $endDate): float
    {
        $vehicleExternalId = $this->stringValue(
            data_get($item, 'vehicle_external_id')
                ?? data_get($item, 'assetId')
                ?? data_get($item, 'vehicle_id')
        );

        if (! $vehicleExternalId) {
            return 0;
        }

        $result = $this->cartrackService->getFuelEstimate($vehicleExternalId, $startDate, $endDate);

        if ($result['error']) {
            return 0;
        }

        return $this->floatValue(data_get($result['data'], '0.fuel_used_liters'));
    }

    private function ensureValidMetrics(float $distance, float $fuel): void
    {
        if ($distance <= 0 || $fuel <= 0) {
            Log::warning('Invalid trip rejected', [
                'distance_km' => $distance,
                'fuel_used_liters' => $fuel,
            ]);

            throw new InvalidArgumentException('Invalid trip metrics.');
        }
    }

    private function stringValue(mixed $value): ?string
    {
        if ($value === null || is_array($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function floatValue(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0;
    }

    private function tripExternalId(array $item): string
    {
        $externalId = $this->stringValue(data_get($item, 'external_id'));

        if ($externalId) {
            return $externalId;
        }

        return 'cartrack-'.md5(json_encode([
            data_get($item, 'registration') ?? data_get($item, 'vehicle_name'),
            data_get($item, 'driver_name'),
            data_get($item, 'distance_km'),
            data_get($item, 'fuel_used_liters'),
            data_get($item, 'started_at'),
        ]));
    }

    private function randomPlate(): string
    {
        return strtoupper(Str::random(2)).'-'.random_int(10, 99).'-'.strtoupper(Str::random(2));
    }
}
