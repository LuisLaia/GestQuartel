<?php

namespace App\Services;

use App\Models\Person;
use App\Models\Vehicle;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PreventechService
{
    public function getFirefighters(): array
    {
        return $this->get('/firefighters/firefightersList');
    }

    public function getVehicles(): array
    {
        return $this->get('/vehicles/vehiclesList');
    }

    public function syncFirefighters(): int
    {
        $result = $this->getFirefighters();

        if ($result['error']) {
            throw new RuntimeException($result['error']);
        }

        $synced = 0;

        foreach ($result['data'] as $firefighter) {
            $externalId = $this->stringValue(data_get($firefighter, 'external_id') ?? data_get($firefighter, 'id'));
            $mechanographicNumber = $this->stringValue(
                data_get($firefighter, 'mechanographic_number')
                    ?? data_get($firefighter, 'numero_mecanografico')
                    ?? data_get($firefighter, 'number')
                    ?? data_get($firefighter, 'numero')
            );
            $email = $this->stringValue(data_get($firefighter, 'email'));
            $name = $this->stringValue(data_get($firefighter, 'name') ?? data_get($firefighter, 'nome'));

            if ($name === null) {
                continue;
            }

            $identity = $this->personIdentity($externalId, $mechanographicNumber, $email);

            if ($identity === null) {
                continue;
            }

            Person::updateOrCreate($identity, array_filter([
                'external_id' => $externalId,
                'name' => $name,
                'mechanographic_number' => $mechanographicNumber,
                'email' => $email,
                'phone' => $this->stringValue(
                    data_get($firefighter, 'phone')
                        ?? data_get($firefighter, 'telefone')
                        ?? data_get($firefighter, 'telemovel')
                ),
                'rank' => $this->stringValue(data_get($firefighter, 'rank') ?? data_get($firefighter, 'posto')),
                'status' => $this->normalizePersonStatus(data_get($firefighter, 'status') ?? data_get($firefighter, 'estado')),
            ], fn ($value) => $value !== null));

            $synced++;
        }

        return $synced;
    }

    public function syncVehicles(): int
    {
        $result = $this->getVehicles();

        if ($result['error']) {
            throw new RuntimeException($result['error']);
        }

        $synced = 0;

        foreach ($result['data'] as $vehicle) {
            $name = $this->stringValue(data_get($vehicle, 'name') ?? data_get($vehicle, 'nome'));
            $plate = $this->stringValue(data_get($vehicle, 'plate') ?? data_get($vehicle, 'matricula') ?? data_get($vehicle, 'license_plate'));

            if ($name === null && $plate === null) {
                continue;
            }

            Vehicle::updateOrCreate(
                $plate !== null ? ['plate' => $plate] : ['name' => $name],
                array_filter([
                    'name' => $name ?? $plate,
                    'plate' => $plate,
                    'type' => $this->stringValue(data_get($vehicle, 'type') ?? data_get($vehicle, 'tipo')),
                    'status' => $this->normalizeVehicleStatus(data_get($vehicle, 'status') ?? data_get($vehicle, 'estado')),
                    'year' => $this->integerValue(data_get($vehicle, 'year') ?? data_get($vehicle, 'ano')),
                    'notes' => $this->stringValue(data_get($vehicle, 'notes') ?? data_get($vehicle, 'observacoes')),
                ], fn ($value) => $value !== null)
            );

            $synced++;
        }

        return $synced;
    }

    private function get(string $endpoint): array
    {
        $baseUrl = rtrim((string) config('services.preventech.base_url'), '/');

        if ($baseUrl === '') {
            return [
                'data' => [],
                'error' => 'Configuracao Preventech incompleta: PREVENTECH_BASE_URL nao esta definida.',
            ];
        }

        try {
            $response = Http::baseUrl($baseUrl)
                ->acceptJson()
                ->timeout(15)
                ->withHeaders($this->authHeaders())
                ->get($endpoint, $this->authPayload());
        } catch (ConnectionException $exception) {
            return [
                'data' => [],
                'error' => 'Nao foi possivel ligar a API Preventech. Tente novamente mais tarde.',
            ];
        }

        if ($response->failed()) {
            return [
                'data' => [],
                'error' => "A API Preventech respondeu com erro HTTP {$response->status()}.",
            ];
        }

        return [
            'data' => $this->normalizeItems($response->json()),
            'error' => null,
        ];
    }

    private function authHeaders(): array
    {
        $apiKey = config('services.preventech.api_key');
        $token = config('services.preventech.token');

        return array_filter([
            // Cliente: colocar PREVENTECH_API_KEY e PREVENTECH_TOKEN no ficheiro .env.
            'X-API-Key' => $apiKey,
            'Authorization' => $token ? "Bearer {$token}" : null,
        ]);
    }

    private function authPayload(): array
    {
        return array_filter([
            // Cliente: colocar PREVENTECH_EMAIL, PREVENTECH_NIF, PREVENTECH_API_KEY e PREVENTECH_TOKEN no .env.
            'email' => config('services.preventech.email'),
            'nif' => config('services.preventech.nif'),
            'api_key' => config('services.preventech.api_key'),
            'token' => config('services.preventech.token'),
        ]);
    }

    private function normalizeItems(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        foreach (['data', 'items', 'results', 'firefighters', 'vehicles'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return $payload[$key];
            }
        }

        return array_is_list($payload) ? $payload : [];
    }

    private function personIdentity(?string $externalId, ?string $mechanographicNumber, ?string $email): ?array
    {
        return match (true) {
            $externalId !== null => ['external_id' => $externalId],
            $mechanographicNumber !== null => ['mechanographic_number' => $mechanographicNumber],
            $email !== null => ['email' => $email],
            default => null,
        };
    }

    private function normalizePersonStatus(mixed $status): string
    {
        $status = strtolower((string) $status);

        return match ($status) {
            'inativo', 'inactive', '0' => 'inativo',
            'suspenso', 'suspended' => 'suspenso',
            default => 'ativo',
        };
    }

    private function normalizeVehicleStatus(mixed $status): string
    {
        $status = strtolower((string) $status);

        return match ($status) {
            'manutencao', 'manutenção', 'maintenance' => 'manutencao',
            'inativo', 'inactive', '0' => 'inativo',
            default => 'operacional',
        };
    }

    private function stringValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function integerValue(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }
}
