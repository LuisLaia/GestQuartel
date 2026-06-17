<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class CartrackService
{
    public function getTrips($startDate, $endDate): array
    {
        if (! $this->isConfigured()) {
            return [
                'data' => $this->mockTrips(),
                'error' => null,
                'demo' => true,
            ];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->acceptJson()
                ->timeout(20)
                ->get(rtrim((string) config('services.cartrack.base_url'), '/').'/trips', [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                ]);

            if ($response->failed()) {
                return [
                    'data' => $this->mockTrips(),
                    'error' => "A API Cartrack respondeu com erro HTTP {$response->status()}.",
                    'demo' => true,
                ];
            }

            return [
                'data' => collect($this->normalizeItems($response->json())),
                'error' => null,
                'demo' => false,
            ];
        } catch (Exception) {
            return [
                'data' => $this->mockTrips(),
                'error' => 'Nao foi possivel ligar a API Cartrack. Foram usados dados de demonstracao.',
                'demo' => true,
            ];
        }
    }

    public function getFuelUsed($vehicleExternalId, $startDate, $endDate): array
    {
        if (! $this->isConfigured()) {
            return [
                'data' => [],
                'error' => null,
                'demo' => true,
            ];
        }

        try {
            $response = Http::baseUrl(rtrim((string) config('services.cartrack.base_url'), '/'))
                ->acceptJson()
                ->timeout(20)
                ->withHeaders($this->headers())
                ->get('/fuel', [
                    'vehicle_id' => $vehicleExternalId,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                ]);
        } catch (Exception) {
            return [
                'data' => [],
                'error' => 'Nao foi possivel ligar a API Cartrack. Tente novamente mais tarde.',
                'demo' => true,
            ];
        }

        if ($response->failed()) {
            return [
                'data' => [],
                'error' => "A API Cartrack respondeu com erro HTTP {$response->status()}.",
                'demo' => true,
            ];
        }

        return [
            'data' => $this->normalizeItems($response->json()),
            'error' => null,
            'demo' => false,
        ];
    }

    public function isConfigured(): bool
    {
        return filled(config('services.cartrack.base_url'))
            && filled(config('services.cartrack.api_key'))
            && filled(config('services.cartrack.token'));
    }

    private function headers(): array
    {
        $token = config('services.cartrack.token');
        $apiKey = config('services.cartrack.api_key');

        return array_filter([
            'Authorization' => $token ? "Bearer {$token}" : null,
            'X-API-Key' => $apiKey,
        ]);
    }

    private function normalizeItems(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        foreach (['data', 'items', 'results', 'trips', 'fuel'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return $payload[$key];
            }
        }

        return array_is_list($payload) ? $payload : [];
    }

    private function mockTrips(): Collection
    {
        return collect([
            [
                'external_id' => 'mock-cartrack-001',
                'vehicle' => 'ABSC 01',
                'driver' => 'João Silva',
                'distance' => 120,
                'fuel' => 8,
                'started_at' => now()->subDays(1)->setTime(8, 15),
                'ended_at' => now()->subDays(1)->setTime(9, 50),
            ],
            [
                'external_id' => 'mock-cartrack-002',
                'vehicle' => 'VCIF 12',
                'driver' => 'Afonso Martins',
                'distance' => 200,
                'fuel' => 15,
                'started_at' => now()->subDays(2)->setTime(7, 30),
                'ended_at' => now()->subDays(2)->setTime(10, 10),
            ],
            [
                'external_id' => 'mock-cartrack-003',
                'vehicle' => 'VUCI 03',
                'driver' => 'Carlos Mendes',
                'distance' => 80,
                'fuel' => 6,
                'started_at' => now()->subDays(3)->setTime(14, 0),
                'ended_at' => now()->subDays(3)->setTime(15, 20),
            ],
        ]);
    }
}
