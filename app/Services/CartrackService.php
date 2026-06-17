<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CartrackService
{
    private const TOKEN_CACHE_KEY = 'cartrack.access_token';

    public function getTrips($startDate, $endDate): array
    {
        if (! $this->isConfigured()) {
            return $this->errorResult('Configuração Cartrack em falta');
        }

        try {
            $response = $this->get('/fleet/trips', [
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]);

            if ($response->failed()) {
                Log::error('Cartrack API error', [
                    'endpoint' => '/fleet/trips',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->errorResult('Nao foi possivel sincronizar viagens da Cartrack.');
            }

            return [
                'data' => array_values(array_filter(array_map(
                    fn (array $item) => $this->normalizeTrip($item),
                    array_filter($this->normalizeItems($response->json()), 'is_array')
                ))),
                'error' => null,
            ];
        } catch (Exception $exception) {
            Log::error('Cartrack API error', [
                'endpoint' => '/fleet/trips',
                'message' => $exception->getMessage(),
            ]);

            return $this->errorResult('Nao foi possivel ligar a API Cartrack.');
        }
    }

    public function getFuelEstimate($vehicleId, $startDate, $endDate): array
    {
        if (! $this->isConfigured()) {
            return $this->errorResult('Configuração Cartrack em falta');
        }

        try {
            $response = $this->get('/fleet/fuel', [
                'vehicle_id' => $vehicleId,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]);

            if ($response->failed()) {
                Log::error('Cartrack API error', [
                    'endpoint' => '/fleet/fuel',
                    'vehicle_id' => $vehicleId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->errorResult('Nao foi possivel obter estimativa de combustivel da Cartrack.');
            }

            return [
                'data' => array_values(array_filter(array_map(
                    fn (array $item) => $this->normalizeFuel($item),
                    array_filter($this->normalizeItems($response->json()), 'is_array')
                ))),
                'error' => null,
            ];
        } catch (Exception $exception) {
            Log::error('Cartrack API error', [
                'endpoint' => '/fleet/fuel',
                'vehicle_id' => $vehicleId,
                'message' => $exception->getMessage(),
            ]);

            return $this->errorResult('Nao foi possivel ligar a API Cartrack.');
        }
    }

    public function isConfigured(): bool
    {
        return filled(config('services.cartrack.base_url'))
            && (
                filled(config('services.cartrack.token'))
                || (
                    filled(config('services.cartrack.client_id'))
                    && filled(config('services.cartrack.client_secret'))
                    && filled(config('services.cartrack.username'))
                    && filled(config('services.cartrack.password'))
                )
            );
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.cartrack.base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->timeout(10)
            ->retry(2, 1000)
            ->withToken($this->token());
    }

    private function get(string $endpoint, array $query): Response
    {
        $response = $this->client()->get($endpoint, $query);

        if ($response->status() === 401 && blank(config('services.cartrack.token'))) {
            Cache::forget(self::TOKEN_CACHE_KEY);

            return $this->client()->get($endpoint, $query);
        }

        return $response;
    }

    private function token(): string
    {
        $configuredToken = config('services.cartrack.token');

        if (filled($configuredToken)) {
            return (string) $configuredToken;
        }

        if ($cachedToken = Cache::get(self::TOKEN_CACHE_KEY)) {
            return (string) $cachedToken;
        }

        $response = Http::baseUrl(rtrim((string) config('services.cartrack.base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->timeout(10)
            ->retry(2, 1000)
            ->post('/oauth/token', [
                'grant_type' => 'password',
                'client_id' => config('services.cartrack.client_id'),
                'client_secret' => config('services.cartrack.client_secret'),
                'username' => config('services.cartrack.username'),
                'password' => config('services.cartrack.password'),
            ]);

        if ($response->failed()) {
            Log::error('Cartrack API error', [
                'endpoint' => '/oauth/token',
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new Exception('Cartrack authentication failed.');
        }

        $payload = $response->json();
        $token = data_get($payload, 'access_token') ?? data_get($payload, 'token');

        if (! $token) {
            Log::error('Cartrack API error', [
                'endpoint' => '/oauth/token',
                'message' => 'Authentication response without token.',
            ]);

            throw new Exception('Cartrack authentication response without token.');
        }

        $expiresIn = max((int) data_get($payload, 'expires_in', 3600) - 60, 60);
        Cache::put(self::TOKEN_CACHE_KEY, (string) $token, now()->addSeconds($expiresIn));

        return (string) $token;
    }

    private function normalizeItems(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        foreach (['data', 'items', 'results', 'trips', 'fuel'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return array_is_list($payload[$key]) ? $payload[$key] : $this->wrapSingleItem($payload[$key]);
            }
        }

        return array_is_list($payload) ? $payload : $this->wrapSingleItem($payload);
    }

    private function wrapSingleItem(array $payload): array
    {
        foreach (['fuel_used_liters', 'fuelUsedLiters', 'fuel_used', 'fuel', 'distance_km', 'distanceKm', 'distance'] as $key) {
            if (array_key_exists($key, $payload)) {
                return [$payload];
            }
        }

        return [];
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
            'started_at' => data_get($item, 'started_at') ?? data_get($item, 'startTime') ?? data_get($item, 'start_time') ?? data_get($item, 'startDate') ?? data_get($item, 'start_date'),
            'ended_at' => data_get($item, 'ended_at') ?? data_get($item, 'endTime') ?? data_get($item, 'end_time') ?? data_get($item, 'endDate') ?? data_get($item, 'end_date'),
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

    private function errorResult(string $message): array
    {
        return [
            'data' => [],
            'error' => $message,
        ];
    }
}
