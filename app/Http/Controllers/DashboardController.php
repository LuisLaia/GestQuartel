<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Trip;
use App\Models\UniformItem;
use App\Models\Vehicle;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $peopleCount = Person::count();
        $activePeopleCount = Person::where('status', 'ativo')->count();
        $vehiclesOperational = Vehicle::where('status', 'operacional')->count();
        $vehiclesMaintenance = Vehicle::where('status', 'manutencao')->count();
        $vehiclesInactive = Vehicle::where('status', 'inativo')->count();
        $uniformStock = UniformItem::select('name', 'available_quantity')
            ->orderBy('available_quantity')
            ->get();
        $ecoDrivers = Trip::selectRaw('driver_name, SUM(distance_km) as total_km, SUM(fuel_used_liters) as total_fuel')
            ->whereNotNull('driver_name')
            ->where('driver_name', '!=', '')
            ->where('distance_km', '>', 0)
            ->where('fuel_used_liters', '>', 0)
            ->groupBy('driver_name')
            ->get()
            ->map(function ($driver) {
                $driver->consumption = $driver->total_km > 0
                    ? round(($driver->total_fuel / $driver->total_km) * 100, 2)
                    : 0;

                return $driver;
            })
            ->sortBy('consumption')
            ->take(5)
            ->values();
        $bestDriver = $ecoDrivers->first();

        return view('dashboard.index', [
            'peopleCount' => $peopleCount,
            'activePeopleCount' => $activePeopleCount,

            'uniformItemCount' => UniformItem::count(),

            'lowStockCount' => UniformItem::whereColumn('available_quantity', '<=', 'minimum_quantity')->count(),

            'vehiclesCount' => Vehicle::count(),
            'vehiclesOperational' => $vehiclesOperational,
            'vehiclesMaintenance' => $vehiclesMaintenance,
            'vehiclesInactive' => $vehiclesInactive,
            'uniformStock' => $uniformStock,
            'ecoDrivers' => $ecoDrivers,
            'bestDriver' => $bestDriver,

            'recentPersonnel' => Person::latest()->limit(5)->get(),
            'recentVehicles' => Vehicle::latest()->limit(5)->get(),
            'recentStockEntries' => UniformItem::latest()->limit(5)->get(),

            'lowStockUniformItems' => UniformItem::whereColumn('available_quantity', '<=', 'minimum_quantity')
                ->orderBy('available_quantity')
                ->limit(6)
                ->get(),
        ]);
    }
}
