<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Vehicle;
use App\Services\CartrackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TripController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $trips = Trip::with('vehicle')
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

        return view('trips.index', compact('trips', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'external_id' => ['nullable', 'string', 'max:255', 'unique:trips,external_id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'distance_km' => ['required', 'numeric', 'min:0.01'],
            'fuel_used_liters' => ['required', 'numeric', 'min:0.01'],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
        ]);

        $trip = Trip::create($data);
        $trip->vehicle?->updateMetrics();

        return redirect()->route('trips.index')->with('success', 'Viagem registada com sucesso.');
    }

    public function sync(Request $request, CartrackService $cartrackService): RedirectResponse
    {
        $data = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = $data['start_date'] ?? now()->subDays(7)->toDateString();
        $endDate = $data['end_date'] ?? now()->toDateString();
        $result = $cartrackService->getTrips($startDate, $endDate);

        if ($result['error']) {
            return back()->with('error', $result['error']);
        }

        $synced = 0;

        foreach ($result['data'] as $item) {
            $vehicle = $this->resolveOrCreateVehicle($item);

            if (! $vehicle) {
                continue;
            }

            $externalId = $this->tripExternalId($item);
            $distance = $this->floatValue(data_get($item, 'distance_km') ?? data_get($item, 'distance') ?? data_get($item, 'km'));
            $fuel = $this->floatValue(data_get($item, 'fuel_used_liters') ?? data_get($item, 'fuel_used') ?? data_get($item, 'fuel'));

            if ($fuel <= 0) {
                $fuel = $this->fuelUsedFor($cartrackService, $item, $startDate, $endDate);
            }

            if ($distance <= 0 || $fuel <= 0) {
                continue;
            }

            $values = [
                'vehicle_id' => $vehicle->id,
                'driver_name' => $this->stringValue(data_get($item, 'driver_name') ?? data_get($item, 'driver') ?? data_get($item, 'motorista')),
                'distance_km' => $distance,
                'fuel_used_liters' => $fuel,
                'started_at' => data_get($item, 'started_at') ?? data_get($item, 'start_time') ?? data_get($item, 'start_date'),
                'ended_at' => data_get($item, 'ended_at') ?? data_get($item, 'end_time') ?? data_get($item, 'end_date'),
            ];

            $trip = Trip::updateOrCreate(['external_id' => $externalId], $values);

            $trip->vehicle?->updateMetrics();
            $synced++;
        }

        $message = "{$synced} viagem(ns) sincronizada(s) com sucesso.";

        if ($result['demo']) {
            $message .= ' Modo demonstração ativo (sem API).';
        }

        if ($result['error']) {
            $message .= ' '.$result['error'];
        }

        return back()->with('success', $message);
    }

    private function resolveOrCreateVehicle(array $item): ?Vehicle
    {
        $vehicleId = data_get($item, 'vehicle_id');

        if (is_numeric($vehicleId) && $vehicle = Vehicle::find((int) $vehicleId)) {
            return $vehicle;
        }

        $externalVehicleId = $this->stringValue(
            data_get($item, 'vehicle_external_id')
                ?? data_get($item, 'vehicle.id')
                ?? data_get($item, 'vehicleId')
        );
        $plate = $this->stringValue(data_get($item, 'plate') ?? data_get($item, 'vehicle.plate') ?? data_get($item, 'license_plate'));
        $name = $this->stringValue(data_get($item, 'vehicle_name') ?? data_get($item, 'vehicle.name') ?? data_get($item, 'vehicle'));

        if (! $externalVehicleId && ! $plate && ! $name) {
            return null;
        }

        $vehicle = Vehicle::query()
            ->when($externalVehicleId, fn ($query) => $query->orWhere('id', $externalVehicleId))
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

    private function fuelUsedFor(CartrackService $cartrackService, array $item, string $startDate, string $endDate): float
    {
        $vehicleExternalId = $this->stringValue(
            data_get($item, 'vehicle_external_id')
                ?? data_get($item, 'vehicle.id')
                ?? data_get($item, 'vehicleId')
                ?? data_get($item, 'vehicle_id')
        );

        if (! $vehicleExternalId) {
            return 0;
        }

        $result = $cartrackService->getFuelUsed($vehicleExternalId, $startDate, $endDate);

        if ($result['error']) {
            return 0;
        }

        return $this->floatValue(data_get($result['data'], '0.fuel_used_liters') ?? data_get($result['data'], '0.fuel_used') ?? data_get($result['data'], '0.fuel'));
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
        $externalId = $this->stringValue(data_get($item, 'external_id') ?? data_get($item, 'id'));

        if ($externalId) {
            return $externalId;
        }

            return 'cartrack-'.md5(json_encode([
            data_get($item, 'vehicle_name') ?? data_get($item, 'vehicle.name') ?? data_get($item, 'vehicle'),
            data_get($item, 'driver_name') ?? data_get($item, 'driver') ?? data_get($item, 'motorista'),
            data_get($item, 'distance_km') ?? data_get($item, 'distance') ?? data_get($item, 'km'),
            data_get($item, 'fuel_used_liters') ?? data_get($item, 'fuel_used') ?? data_get($item, 'fuel'),
            data_get($item, 'started_at') ?? data_get($item, 'start_time') ?? data_get($item, 'start_date'),
        ]));
    }

    private function randomPlate(): string
    {
        return strtoupper(Str::random(2)).'-'.random_int(10, 99).'-'.strtoupper(Str::random(2));
    }
}
