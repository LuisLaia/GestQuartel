<?php

namespace App\Services;

use App\Models\Person;
use App\Models\Vehicle;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PreventechService
{
    public function syncPersonnel(): int
    {
        $result = $this->getPersonnel();

        if ($result['error']) {
            $this->logSync('personnel', false, 0, $result['error']);

            throw new RuntimeException($result['error']);
        }

        $synced = 0;

        foreach ($result['data'] as $item) {
            $person = $this->mapPersonData($item);

            if (! $person['name'] || ! $person['email']) {
                continue;
            }

            $existingPerson = Person::query()
                ->where('email', $person['email'])
                ->orWhere('number', $person['number'])
                ->orWhere('mechanographic_number', $person['mechanographic_number'])
                ->first();

            if ($existingPerson?->email) {
                $person['email'] = $existingPerson->email;
            }

            Person::updateOrCreate(
                ['email' => $person['email']],
                $person
            );

            $synced++;
        }

        $this->logSync('personnel', true, $synced);

        return $synced;
    }

    public function syncVehicles(): int
    {
        $result = $this->getVehicles();

        if ($result['error']) {
            $this->logSync('vehicles', false, 0, $result['error']);

            throw new RuntimeException($result['error']);
        }

        $synced = 0;

        foreach ($result['data'] as $item) {
            $vehicle = $this->mapVehicleData($item);

            if (! $vehicle['name'] && ! $vehicle['plate']) {
                continue;
            }

            Vehicle::updateOrCreate(
                $vehicle['plate'] ? ['plate' => $vehicle['plate']] : ['name' => $vehicle['name']],
                array_filter($vehicle, fn ($value) => $value !== null)
            );

            $synced++;
        }

        $this->logSync('vehicles', true, $synced);

        return $synced;
    }

    public function syncFirefighters(): int
    {
        return $this->syncPersonnel();
    }

    public function getFirefighters(): array
    {
        return $this->getPersonnel();
    }

    public function getPersonnel(): array
    {
        return $this->fetch('/personnel', 'personnel', fn () => $this->mockPersonnel());
    }

    public function getVehicles(): array
    {
        return $this->fetch('/vehicles', 'vehicles', fn () => $this->mockVehicles());
    }

    public function sandboxModeActive(): bool
    {
        return $this->isSandbox();
    }

    private function fetch(string $endpoint, string $type, callable $mockData): array
    {
        if ($this->isSandbox()) {
            return $this->success($mockData());
        }

        try {
            $response = Http::baseUrl(rtrim((string) config('services.preventech.base_url'), '/'))
                ->acceptJson()
                ->timeout(15)
                ->withHeaders([
                    'Authorization' => 'Bearer '.config('services.preventech.api_key'),
                ])
                ->get($endpoint);

            if ($response->failed()) {
                throw new Exception("Preventech {$type} request failed with status {$response->status()}.");
            }

            return $this->success($this->normalizeItems($response->json(), $type));
        } catch (Exception $exception) {
            Log::warning('Preventech API unavailable, using sandbox data.', [
                'type' => $type,
                'endpoint' => $endpoint,
                'message' => $exception->getMessage(),
            ]);

            return $this->success($mockData());
        }
    }

    private function mockPersonnel(): array
    {
        return [
            [
                'name' => 'Joaquim Silva',
                'number' => 'B010',
                'phone' => '943245667',
                'rank' => 'Bombeiro',
                'status' => 'ativo',
            ],
            [
                'name' => 'Rita Alves',
                'number' => 'B011',
                'phone' => '935467523',
                'rank' => 'Subchefe',
                'status' => 'ativo',
            ],
        ];
    }

    private function mockVehicles(): array
    {
        return [
            [
                'name' => 'VCIF 20',
                'registration' => '43-BR-20',
                'type' => 'Incendio Florestal',
                'status' => 'operacional',
            ],
        ];
    }

    public function mapVehicleData(array $data): array
    {
        $registration = $this->stringValue(
            data_get($data, 'registration')
                ?? data_get($data, 'plate')
                ?? data_get($data, 'matricula')
                ?? data_get($data, 'license_plate')
        );
        $name = $this->stringValue(data_get($data, 'name') ?? data_get($data, 'nome'));

        return [
            'name' => $name ?: 'Veiculo '.$registration,
            'plate' => $registration,
            'type' => $this->stringValue(data_get($data, 'type') ?? data_get($data, 'tipo'))
                ?: $this->inferVehicleType($name ?: $registration),
            'year' => $this->integerValue(data_get($data, 'year') ?? data_get($data, 'ano')) ?? rand(2005, 2022),
            'status' => $this->normalizeVehicleStatus(data_get($data, 'status') ?? data_get($data, 'estado')),
            'total_km' => $this->floatValue(data_get($data, 'total_km') ?? data_get($data, 'totalKm')) ?? 0,
            'average_consumption' => $this->floatValue(
                data_get($data, 'avg_consumption')
                    ?? data_get($data, 'average_consumption')
                    ?? data_get($data, 'averageConsumption')
            ),
        ];
    }

    public function inferVehicleType(?string $name): string
    {
        $name = strtoupper((string) $name);

        if (str_contains($name, 'ABSC')) {
            return 'Ambulancia';
        }

        if (str_contains($name, 'VCIF')) {
            return 'Incendio Florestal';
        }

        if (str_contains($name, 'VUCI')) {
            return 'Comando';
        }

        return 'Outros';
    }

    public function mapPersonData(array $data): array
    {
        $number = $this->stringValue(
            data_get($data, 'number')
                ?? data_get($data, 'mechanographic_number')
                ?? data_get($data, 'numero_mecanografico')
                ?? data_get($data, 'numero')
        );
        $number ??= $this->generateNumber();

        return [
            'external_id' => $this->stringValue(data_get($data, 'external_id') ?? data_get($data, 'id')),
            'name' => $this->stringValue(data_get($data, 'name') ?? data_get($data, 'nome')),
            'number' => $number,
            'mechanographic_number' => $number,
            'email' => $this->stringValue(data_get($data, 'email')) ?? fake()->unique()->safeEmail(),
            'phone' => $this->stringValue(data_get($data, 'phone') ?? data_get($data, 'telefone') ?? data_get($data, 'telemovel'))
                ?? '91'.rand(1000000, 9999999),
            'rank' => $this->stringValue(data_get($data, 'rank') ?? data_get($data, 'posto')) ?? 'Bombeiro',
            'status' => $this->normalizePersonStatus(data_get($data, 'status') ?? data_get($data, 'estado')),
            'waist_cm' => $this->integerValue(data_get($data, 'waist_cm')) ?? rand(80, 110),
            'chest_cm' => $this->integerValue(data_get($data, 'chest_cm')) ?? rand(90, 120),
            'height_cm' => $this->integerValue(data_get($data, 'height_cm')) ?? rand(165, 190),
        ];
    }

    public function generateNumber(): string
    {
        do {
            $number = 'B'.str_pad((string) rand(1, 999), 3, '0', STR_PAD_LEFT);
        } while (Person::where('number', $number)->orWhere('mechanographic_number', $number)->exists());

        return $number;
    }

    private function normalizeItems(mixed $payload, string $type): array
    {
        if (! is_array($payload)) {
            return [];
        }

        foreach (['data', 'items', 'results', $type, 'personnel', 'firefighters', 'vehicles'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return array_is_list($payload[$key]) ? $payload[$key] : [$payload[$key]];
            }
        }

        return array_is_list($payload) ? $payload : [$payload];
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
            'manutencao', 'maintenance' => 'manutencao',
            'inativo', 'inactive', '0' => 'inativo',
            default => 'operacional',
        };
    }

    private function isSandbox(): bool
    {
        return config('services.preventech.mode', 'sandbox') === 'sandbox';
    }

    private function stringValue(mixed $value): ?string
    {
        if ($value === null || is_array($value)) {
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

    private function floatValue(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function success(array $data): array
    {
        return [
            'data' => $data,
            'error' => null,
        ];
    }

    private function logSync(string $type, bool $success, int $count, ?string $message = null): void
    {
        Log::info('Preventech sync finished', [
            'type' => $type,
            'mode' => config('services.preventech.mode', 'sandbox'),
            'success' => $success,
            'imported' => $count,
            'message' => $message,
            'synced_at' => now()->toDateTimeString(),
        ]);
    }
}
