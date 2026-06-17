<?php

namespace App\Services;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

class VehicleService
{
    public function search(?string $search = null): Collection
    {
        return Vehicle::query()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('plate', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            }))
            ->latest()
            ->get();
    }

    public function stats(): array
    {
        return [
            'total' => Vehicle::count(),
            'operational' => Vehicle::where('status', 'operacional')->count(),
            'maintenance' => Vehicle::where('status', 'manutencao')->count(),
            'inactive' => Vehicle::where('status', 'inativo')->count(),
        ];
    }

    public function create(array $data): Vehicle
    {
        return Vehicle::create($data);
    }

    public function update(Vehicle $vehicle, array $data): Vehicle
    {
        $vehicle->update($data);

        return $vehicle;
    }

    public function refreshMetrics(Vehicle $vehicle): void
    {
        $vehicle->updateMetrics();
    }
}
