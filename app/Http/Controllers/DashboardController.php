<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\UniformAssignment;
use App\Models\UniformItem;
use App\Models\Vehicle;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $peopleCount = Person::count();
        $activePeopleCount = Person::where('status', 'ativo')->count();

        return view('dashboard.index', [
            'peopleCount' => $peopleCount,
            'activePeopleCount' => $activePeopleCount,

            'uniformItemCount' => UniformItem::count(),

            'lowStockCount' => UniformItem::whereColumn('available_quantity', '<=', 'minimum_quantity')->count(),

            'vehiclesCount' => Vehicle::count(),
            'activeVehicles' => Vehicle::where('status', 'operacional')->count(),
            'maintenanceVehicles' => Vehicle::where('status', 'manutencao')->count(),
            'inactiveVehicles' => Vehicle::where('status', 'inativo')->count(),

            'recentAssignments' => UniformAssignment::with(['person', 'uniformItem'])
                ->latest('assigned_at')
                ->limit(6)
                ->get(),

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
