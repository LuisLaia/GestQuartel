<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CartrackService
{
    public function getTrips($start, $end): array
    {
        if ($this->isSandbox()) {
            return $this->success($this->mockTrips());
        }

        try {
            $response = $this->client()->get('/fleet/trips', [
                'startDate' => $start,
                'endDate' => $end,
            ]);

            if ($response->failed()) {
                throw new Exception("Cartrack trips request failed with status {$response->status()}.");
            }

            return $this->success(array_values(array_filter(array_map(
                fn (array $item) => $this->normalizeTrip($item),
                array_filter($this->normalizeItems($response->json()), 'is_array')
            ))));
        } catch (Exception $exception) {
            Log::warning('Cartrack API unavailable, using sandbox trips.', [
                'endpoint' => '/fleet/trips',
                'message' => $exception->getMessage(),
            ]);

            return $this->success($this->mockTrips());
        }
    }

    public function getFuelEstimate($registration, $start, $end): array
    {
        if ($this->isSandbox()) {
            return $this->success([$this->mockFuel($registration)]);
        }

        try {
            $response = $this->client()->get('/fleet/fuel', [
                'registration' => $registration,
                'startDate' => $start,
                'endDate' => $end,
            ]);

            if ($response->failed()) {
                throw new Exception("Cartrack fuel request failed with status {$response->status()}.");
            }

            return $this->success(array_values(array_filter(array_map(
                fn (array $item) => $this->normalizeFuel($item),
                array_filter($this->normalizeItems($response->json()), 'is_array')
            ))));
        } catch (Exception $exception) {
            Log::warning('Cartrack API unavailable, using sandbox fuel estimate.', [
                'endpoint' => '/fleet/fuel',
                'registration' => $registration,
                'message' => $exception->getMessage(),
            ]);

            return $this->success([$this->mockFuel($registration)]);
        }
    }

    public function sandboxModeActive(): bool
    {
        return $this->isSandbox();
    }

    public function getSandboxTrips(): array
    {
        return $this->mockTrips();
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.cartrack.base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->timeout(10)
            ->retry(2, 1000)
            ->withBasicAuth(
                (string) config('services.cartrack.username'),
                (string) config('services.cartrack.password')
            );
    }

    private function isSandbox(): bool
    {
        return config('services.cartrack.mode', 'sandbox') === 'sandbox';
    }

    private function mockTrips(): array
    {
        return [
            [
                'external_id' => 'sandbox-00-aa-00-'.now()->subDays(2)->toDateString(),
                'vehicle_name' => 'Viatura 00-AA-00',
                'registration' => '00-AA-00',
                'driver_name' => 'Afonso Martins',
                'start_time' => now()->subDays(2),
                'end_time' => now()->subDays(2)->addHour(),
                'started_at' => now()->subDays(2),
                'ended_at' => now()->subDays(2)->addHour(),
                'distance_km' => 25,
                'fuel_used_liters' => 2.0,
            ],
            [
                'external_id' => 'sandbox-11-bb-11-'.now()->subDays(1)->toDateString(),
                'vehicle_name' => 'Viatura 11-BB-11',
                'registration' => '11-BB-11',
                'driver_name' => 'Joao Silva',
                'start_time' => now()->subDays(1),
                'end_time' => now()->subDays(1)->addHour(),
                'started_at' => now()->subDays(1),
                'ended_at' => now()->subDays(1)->addHour(),
                'distance_km' => 40,
                'fuel_used_liters' => 4.2,
            ],
            [
                'external_id' => 'sandbox-22-cc-22-'.now()->toDateString(),
                'vehicle_name' => 'Viatura 22-CC-22',
                'registration' => '22-CC-22',
                'driver_name' => 'Pedro Costa',
                'start_time' => now(),
                'end_time' => now()->addHour(),
                'started_at' => now(),
                'ended_at' => now()->addHour(),
                'distance_km' => 60,
                'fuel_used_liters' => 5.5,
            ],
        ];
    }

    private function mockFuel($registration): array
    {
        return [
            'registration' => $registration,
            'fuel_used_liters' => rand(2, 8),
        ];
    }

    private function normalizeItems(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        foreach (['data', 'items', 'results', 'trips', 'fuel'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return array_is_list($payload[$key]) ? $payload[$key] : [$payload[$key]];
            }
        }

        return array_is_list($payload) ? $payload : [$payload];
    }

    private function normalizeTrip(array $item): ?array
    {
        $distance = $this->floatValue(
            data_get($item, 'distance_km')
                ?? data_get($item, 'distanceKm')
                ?? data_get($item, 'distance')
                ?? data_get($item, 'km')
        );

        if ($distance <= 0) {
            return null;
        }

        $registration = $this->stringValue(
            data_get($item, 'registration')
                ?? data_get($item, 'plate')
                ?? data_get($item, 'vehicle.registration')
                ?? data_get($item, 'vehicle.plate')
                ?? data_get($item, 'licensePlate')
                ?? data_get($item, 'license_plate')
        );

        return [
            'external_id' => $this->stringValue(data_get($item, 'external_id') ?? data_get($item, 'tripId') ?? data_get($item, 'uuid') ?? data_get($item, 'id')),
            'vehicle_id' => data_get($item, 'vehicle_id'),
            'vehicle_external_id' => $this->stringValue(data_get($item, 'vehicle_external_id') ?? data_get($item, 'vehicle.id') ?? data_get($item, 'vehicleId') ?? data_get($item, 'assetId')),
            'vehicle_name' => $this->stringValue(data_get($item, 'vehicle_name') ?? data_get($item, 'vehicleName') ?? data_get($item, 'vehicle.name') ?? data_get($item, 'vehicle')),
            'registration' => $registration,
            'plate' => $registration,
            'driver_name' => $this->stringValue(data_get($item, 'driver_name') ?? data_get($item, 'driverName') ?? data_get($item, 'driver.name') ?? data_get($item, 'driver') ?? data_get($item, 'motorista')),
            'distance_km' => $distance,
            'fuel_used_liters' => $this->floatValue(data_get($item, 'fuel_used_liters') ?? data_get($item, 'fuelUsedLiters') ?? data_get($item, 'fuel_used') ?? data_get($item, 'fuelEstimate') ?? data_get($item, 'fuel')),
            'started_at' => $this->dateValue(data_get($item, 'started_at') ?? data_get($item, 'startTime') ?? data_get($item, 'start_time') ?? data_get($item, 'startDate') ?? data_get($item, 'start_date')),
            'ended_at' => $this->dateValue(data_get($item, 'ended_at') ?? data_get($item, 'endTime') ?? data_get($item, 'end_time') ?? data_get($item, 'endDate') ?? data_get($item, 'end_date')),
        ];
    }

    private function normalizeFuel(array $item): ?array
    {
        $fuel = $this->floatValue(
            data_get($item, 'fuel_used_liters')
                ?? data_get($item, 'fuelUsedLiters')
                ?? data_get($item, 'fuel_used')
                ?? data_get($item, 'fuelEstimate')
                ?? data_get($item, 'fuel')
        );

        if ($fuel <= 0) {
            return null;
        }

        return ['fuel_used_liters' => $fuel];
    }

    private function dateValue(mixed $value): mixed
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (! $value) {
            return null;
        }

        return Carbon::parse($value);
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

    private function success(array $data): array
    {
        return [
            'data' => $data,
            'error' => null,
        ];
    }
}
